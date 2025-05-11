// pages/api/appointments/[appointmentId]/cancel.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../../lib/db'; // Adjust path
import admin from '../../../../lib/firebaseAdmin'; // Adjust path
import mysql from 'mysql2/promise';
import { getIO, NextApiResponseWithSocket } from '../../socket'; // For socket emission if queue changes

// Helper to fetch full queue (adapt from your queue API)
async function fetchFullQueueForSocket(connection: mysql.PoolConnection | mysql.Pool): Promise<any[]> {
    const [rows] = await connection.execute<mysql.RowDataPacket[]>(
        `SELECT q.patient_id as id, p.name, q.status, q.wait_time_estimate_minutes as waitTime, q.is_emergency as isEmergency,
                (SELECT COUNT(*) + 1 FROM queue q2 WHERE q2.is_emergency > q.is_emergency OR (q2.is_emergency = q.is_emergency AND q2.entry_time < q.entry_time) AND q2.status != 'completed') as position
         FROM queue q JOIN patients p ON q.patient_id = p.id
         WHERE q.status != 'completed' OR DATE(q.updated_at) = CURDATE()
         ORDER BY q.status = 'completed' ASC, q.is_emergency DESC, q.entry_time ASC`
    );
    return rows.map((item: any, index: number) => ({
        id: String(item.id), name: item.name, status: item.status,
        waitTime: item.waitTime, isEmergency: Boolean(item.isEmergency),
        position: item.status === 'completed' ? 0 : (item.position || index + 1),
    }));
}


export default async function handler(req: NextApiRequest, res: NextApiResponseWithSocket) { // Use NextApiResponseWithSocket
  if (req.method !== 'PATCH') {
    res.setHeader('Allow', ['PATCH']);
    return res.status(405).end(`Method ${req.method} Not Allowed`);
  }

  const { appointmentId } = req.query;
  const { timeSlotId } = req.body; // Frontend sends this to make the slot available

  const authorization = req.headers.authorization;
  if (!authorization || !authorization.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Unauthorized: Missing or invalid token' });
  }
  const idToken = authorization.split('Bearer ')[1];

  if (!appointmentId || typeof appointmentId !== 'string' || !timeSlotId) {
    return res.status(400).json({ message: 'Missing appointmentId or timeSlotId.' });
  }

  let connection: mysql.PoolConnection | null = null;
  try {
    const decodedToken = await admin.auth().verifyIdToken(idToken);
    const firebaseUid = decodedToken.uid;

    connection = await getPool().getConnection();
    await connection.beginTransaction();

    // Get patient's MySQL ID
    const [patientRows] = await connection.execute<mysql.RowDataPacket[] & { id: number }[]>(
      'SELECT id FROM patients WHERE firebase_uid = ?', [firebaseUid]
    );
    if (patientRows.length === 0) {
      await connection.rollback();
      return res.status(404).json({ message: 'Patient record not found.' });
    }
    const patientMySQLId = patientRows[0].id;

    // Verify appointment belongs to this patient and is cancellable
    const [apptRows] = await connection.execute<mysql.RowDataPacket[] & { status: string, patient_id: number, time_slot_id: number }[]>(
      'SELECT status, patient_id, time_slot_id FROM appointments WHERE id = ?', [appointmentId]
    );
    if (apptRows.length === 0) {
      await connection.rollback();
      return res.status(404).json({ message: 'Appointment not found.' });
    }
    if (apptRows[0].patient_id !== patientMySQLId) {
      await connection.rollback();
      return res.status(403).json({ message: 'Forbidden: You can only cancel your own appointments.' });
    }
    if (apptRows[0].status !== 'Scheduled') {
      await connection.rollback();
      return res.status(400).json({ message: `Cannot cancel appointment with status: ${apptRows[0].status}.` });
    }
    const actualTimeSlotId = apptRows[0].time_slot_id; // Use time_slot_id from DB for safety

    // 1. Update appointment status
    await connection.execute(
      'UPDATE appointments SET status = "Cancelled" WHERE id = ?', [appointmentId]
    );

    // 2. Make time slot available again
    await connection.execute(
      'UPDATE time_slots SET is_available = TRUE WHERE id = ?', [actualTimeSlotId]
    );

    // 3. Remove patient from queue if they were added for this appointment
    // This logic assumes patient is added to queue with appointment_id or similar link,
    // or by matching patient_id and it's an active 'waiting' status.
    // For simplicity, let's assume we remove if they are 'waiting' or 'in-progress'.
    // A more robust solution would link appointments to queue entries if needed.
    const [deleteQueueResult] = await connection.execute<mysql.OkPacket>(
        'DELETE FROM queue WHERE patient_id = ? AND (status = "waiting" OR status = "in-progress")', // This is a broad removal
        [patientMySQLId]
    );
    console.log(`Removed ${deleteQueueResult.affectedRows} queue entries for patient ${patientMySQLId} upon cancellation.`);


    await connection.commit();

    // Emit queue update if queue was affected
    if (deleteQueueResult.affectedRows > 0) {
        const io = getIO(res);
        if (io) {
            try {
                const pool = getPool();
                const updatedQueue = await fetchFullQueueForSocket(pool);
                io.emit('queue-update', updatedQueue);
                console.log(`Emitted 'queue-update' after appointment cancellation affected queue.`);
            } catch (fetchError) {
                console.error(`Error fetching queue for socket emission after cancellation:`, fetchError);
            }
        }
    }

    res.status(200).json({ message: 'Appointment cancelled successfully.' });

  } catch (error: any) {
    if (connection) await connection.rollback();
    console.error(`Error cancelling appointment ${appointmentId}:`, error);
    if (error.code === 'auth/id-token-expired') {
      return res.status(401).json({ message: 'Unauthorized: Token expired.' });
    }
    res.status(500).json({ message: 'Failed to cancel appointment', error: error.message });
  } finally {
    if (connection) connection.release();
  }
}