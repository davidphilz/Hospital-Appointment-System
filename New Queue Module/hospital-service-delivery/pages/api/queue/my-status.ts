// pages/api/queue/my-status.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../lib/db'; // Adjust path
import admin from '../../../lib/firebaseAdmin'; // Adjust path
import mysql from 'mysql2/promise';

export interface MyQueueStatus {
  id: string; // patient_id as string
  name: string;
  status: 'waiting' | 'in-progress';
  waitTime: number;
  position: number;
  isEmergency: boolean;
}

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'GET') {
    res.setHeader('Allow', ['GET']);
    return res.status(405).end(`Method ${req.method} Not Allowed`);
  }

  const authorization = req.headers.authorization;
  if (!authorization || !authorization.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Unauthorized: Missing or invalid token' });
  }
  const idToken = authorization.split('Bearer ')[1];

  try {
    const decodedToken = await admin.auth().verifyIdToken(idToken);
    const firebaseUid = decodedToken.uid;

    const pool = getPool();
    // Get patient's MySQL ID
    const [patientRows] = await pool.execute<mysql.RowDataPacket[] & { id: number }[]>(
      'SELECT id FROM patients WHERE firebase_uid = ?', [firebaseUid]
    );
    if (patientRows.length === 0) {
      return res.status(404).json({ message: 'Patient record not found.' });
    }
    const patientMySQLId = patientRows[0].id;

    // Fetch active queue status for this patient
    const [queueRows] = await pool.execute<mysql.RowDataPacket[] & MyQueueStatus[]>(
      `SELECT
         q.patient_id as id,
         p.name,
         q.status,
         q.wait_time_estimate_minutes as waitTime,
         q.is_emergency as isEmergency,
         (SELECT COUNT(*) + 1
          FROM queue q2
          WHERE (q2.status = 'waiting' OR q2.status = 'in-progress')
            AND (q2.is_emergency > q.is_emergency OR (q2.is_emergency = q.is_emergency AND q2.entry_time < q.entry_time))
         ) as position
       FROM queue q
       JOIN patients p ON q.patient_id = p.id
       WHERE q.patient_id = ? AND (q.status = 'waiting' OR q.status = 'in-progress')
       LIMIT 1`, // A patient should only have one active queue entry
      [patientMySQLId]
    );

    if (queueRows.length > 0) {
      // Convert patient_id (which is q.patient_id) to string for the 'id' field
      const queueStatus = { ...queueRows[0], id: String(queueRows[0].id) };
      res.status(200).json(queueStatus);
    } else {
      res.status(200).json(null); // Or res.status(204).end(); if not in active queue
    }

  } catch (error: any) {
    console.error('Error fetching "my queue status":', error);
    if (error.code === 'auth/id-token-expired') {
      return res.status(401).json({ message: 'Unauthorized: Token expired.' });
    }
    res.status(500).json({ message: 'Internal server error', error: error.message });
  }
}