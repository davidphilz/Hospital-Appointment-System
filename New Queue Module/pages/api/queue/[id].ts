// pages/api/queue/[id].ts
import type { NextApiRequest } from 'next'; // NextApiResponseWithSocket used below
import { getPool } from '../../../lib/db';   // Adjust path
import mysql from 'mysql2/promise';
import { getIO, NextApiResponseWithSocket } from '../socket'; // Adjust path for socket types/functions

// Helper function to fetch and format the entire queue for socket emission
// This MUST match the data structure and filtering logic of GET /api/queue
async function fetchFullQueueForSocket(connection: mysql.PoolConnection | mysql.Pool): Promise<QueueItemForClient[]> {
    const today = new Date().toISOString().split('T')[0];
    const [rows] = await connection.execute<mysql.RowDataPacket[]>(
      `
      SELECT
        q.patient_id as id,
        p.name,
        q.status,
        q.wait_time_estimate_minutes as waitTime,
        q.is_emergency as isEmergency,
        q.entry_time,
        q.updated_at,
        CASE
          WHEN q.status != 'completed' THEN
            (SELECT COUNT(*) + 1
             FROM queue q2
             WHERE q2.status != 'completed' AND
                   (q2.is_emergency > q.is_emergency OR
                    (q2.is_emergency = q.is_emergency AND q2.entry_time < q.entry_time)))
          ELSE 0
        END as position
      FROM queue q
      JOIN patients p ON q.patient_id = p.id
      WHERE
        q.status != 'completed'
        OR (q.status = 'completed' AND DATE(q.updated_at) = ?)
      ORDER BY
        CASE q.status WHEN 'waiting' THEN 1 WHEN 'in-progress' THEN 2 WHEN 'completed' THEN 3 ELSE 4 END ASC,
        q.is_emergency DESC,
        CASE WHEN q.status != 'completed' THEN q.entry_time ELSE NULL END ASC,
        CASE WHEN q.status = 'completed' THEN q.updated_at ELSE NULL END DESC
      `,
      [today]
    );
    return rows.map((item: any) => ({ // Ensure this mapping matches QueueItemForClient
      id: String(item.id),
      name: item.name,
      status: item.status,
      waitTime: item.waitTime,
      isEmergency: Boolean(item.isEmergency),
      position: item.status === 'completed' ? 0 : item.position,
      // updatedAt: item.updated_at ? new Date(item.updated_at).toISOString() : undefined,
    }));
}


// Interface for data expected by the client (matches frontend)
interface QueueItemForClient {
  id: string;
  name: string;
  status: 'waiting' | 'in-progress' | 'completed';
  waitTime: number;
  isEmergency: boolean;
  position: number;
  // updatedAt?: string;
}


export default async function queuePatchHandler(req: NextApiRequest, res: NextApiResponseWithSocket) { // Use NextApiResponseWithSocket
  const { id: itemId } = req.query; // This 'id' from URL is patient_id used in queue
  const { status: newStatus } = req.body;

  if (req.method !== 'PATCH') {
    res.setHeader('Allow', ['PATCH']);
    return res.status(405).json({ message: `Method ${req.method} Not Allowed` });
  }

  if (!itemId || typeof itemId !== 'string' || !newStatus || !['waiting', 'in-progress', 'completed'].includes(newStatus)) {
    return res.status(400).json({ message: 'Valid Item ID (patient_id) and new status (waiting, in-progress, completed) are required.' });
  }

  console.log(`API PATCH /api/queue/${itemId}: Attempting update to status: ${newStatus}`);

  let connection: mysql.PoolConnection | null = null;
  try {
    connection = await getPool().getConnection();
    await connection.beginTransaction();

    // Update the database: Set new status and current timestamp for updated_at
    const [updateResult] = await connection.execute<mysql.OkPacket>(
      'UPDATE queue SET status = ?, updated_at = NOW() WHERE patient_id = ? AND status != ?', // Avoid re-updating if status is same
      [newStatus, itemId, newStatus]
    );

    if (updateResult.affectedRows === 0) {
      // Could be that item doesn't exist or status is already set to newStatus
      // Check if item exists with the *old* status to differentiate
      const [checkRows] = await connection.execute<mysql.RowDataPacket[]>(
          'SELECT status FROM queue WHERE patient_id = ?', [itemId]
      );
      if (checkRows.length === 0) {
          await connection.rollback();
          console.log(`API PATCH /api/queue/${itemId}: Item not found.`);
          return res.status(404).json({ message: `Queue item with Patient ID ${itemId} not found.` });
      }
      // If it exists but affectedRows is 0, it means status was already newStatus
      console.log(`API PATCH /api/queue/${itemId}: Status was already ${newStatus} or item not found for update logic.`);
      // No DB change, but still commit transaction and emit current queue state for consistency
    } else {
        console.log(`API PATCH /api/queue/${itemId}: DB updated successfully. Rows affected: ${updateResult.affectedRows}`);
    }

    await connection.commit();
    console.log(`API PATCH /api/queue/${itemId}: Transaction committed.`);

    // After successful commit, fetch the full updated queue and emit via Socket.IO
    const io = getIO(res); // Pass res to initialize/get io instance for this request
    if (io) {
      try {
        // Use the pool for a fresh read after commit, not the transaction connection if it auto-commits on release
        const poolForRead = getPool();
        const updatedQueueForSocket = await fetchFullQueueForSocket(poolForRead);
        io.emit('queue-update', updatedQueueForSocket);
        console.log(`API PATCH /api/queue/${itemId}: Emitted 'queue-update' with ${updatedQueueForSocket.length} items.`);
      } catch (fetchError: any) {
        console.error(`API PATCH /api/queue/${itemId}: Error fetching/emitting queue for socket update:`, fetchError.message);
        // Don't fail the HTTP response for a socket emission error, but log it.
      }
    } else {
      console.warn(`API PATCH /api/queue/${itemId}: Socket.IO instance not found. Cannot emit 'queue-update'.`);
    }

    res.status(200).json({ message: 'Patient status updated successfully.' });

  } catch (error: any) {
    if (connection) {
      try { await connection.rollback(); } catch (rbError) { console.error("Error during rollback:", rbError); }
    }
    console.error(`API PATCH /api/queue/${itemId}: Error during database operation:`, error);
    res.status(500).json({ message: 'Failed to update patient status in database.', error: error.message });
  } finally {
    if (connection) {
      try { connection.release(); } catch (relError) { console.error("Error releasing connection:", relError); }
    }
  }
}