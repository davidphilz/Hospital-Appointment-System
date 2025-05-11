// pages/api/queue/index.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../lib/db'; // Adjust path if your lib folder is elsewhere
import mysql from 'mysql2/promise';

// Interface for data sent to client (ensure it matches frontend's QueueItem)
export interface QueueItemForClient {
  id: string; // patient_id from DB, converted to string
  name: string;
  status: 'waiting' | 'in-progress' | 'completed';
  waitTime: number; // wait_time_estimate_minutes
  isEmergency: boolean;
  position: number; // Calculated for active items
  // Add 'updatedAt' if you want to display completion time or sort by it on client more precisely
  // updatedAt?: string; // ISO string format
}

export default async function queueGetHandler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'GET') {
    res.setHeader('Allow', ['GET']);
    return res.status(405).json({ message: `Method ${req.method} Not Allowed` });
  }

  try {
    const pool = getPool();
    const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD

    // Fetch active items (waiting, in-progress) OR items completed today
    // Calculate position primarily for active items.
    const [rows] = await pool.execute<mysql.RowDataPacket[]>(
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
          ELSE 0 -- Position is less relevant for 'completed' status in this view
        END as position
      FROM queue q
      JOIN patients p ON q.patient_id = p.id
      WHERE
        q.status != 'completed'  -- All active (waiting, in-progress)
        OR (q.status = 'completed' AND DATE(q.updated_at) = ?) -- Or completed today
      ORDER BY
        -- Ensure consistent ordering: active items first, then completed
        -- Within active: emergency first, then by entry time (which determines position)
        -- Within completed: most recently completed first
        CASE q.status
          WHEN 'waiting' THEN 1
          WHEN 'in-progress' THEN 2
          WHEN 'completed' THEN 3
          ELSE 4
        END ASC,
        q.is_emergency DESC,
        CASE WHEN q.status != 'completed' THEN q.entry_time ELSE NULL END ASC, -- Sort active by entry_time
        CASE WHEN q.status = 'completed' THEN q.updated_at ELSE NULL END DESC   -- Sort completed by updated_at desc
      `,
      [today] // Parameter for DATE(q.updated_at) = ?
    );

    const formattedQueue: QueueItemForClient[] = rows.map((item: any) => ({
      id: String(item.id),
      name: item.name,
      status: item.status,
      waitTime: item.waitTime,
      isEmergency: Boolean(item.isEmergency),
      position: item.status === 'completed' ? 0 : item.position, // Use calculated position for active
      // updatedAt: item.updated_at ? new Date(item.updated_at).toISOString() : undefined, // Optional
    }));

    res.status(200).json(formattedQueue);
  } catch (error: any) {
    console.error('API GET /api/queue: Failed to fetch queue:', error);
    res.status(500).json({ message: 'Failed to fetch queue data', error: error.message });
  }
}