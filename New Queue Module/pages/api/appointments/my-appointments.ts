// pages/api/appointments/my-appointments.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../lib/db'; // Adjust path
import admin from '../../../lib/firebaseAdmin'; // Adjust path
import mysql from 'mysql2/promise';

export interface MyAppointment { // Renamed to avoid conflict with frontend interface
  id: number; // appointments.id
  doctorName: string;
  specialty: string;
  appointmentDate: string; // YYYY-MM-DD
  appointmentTime: string; // HH:MM
  status: string;
  timeSlotId: number;
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

    if (!firebaseUid) {
      return res.status(401).json({ message: 'Unauthorized: Invalid token UID' });
    }

    const pool = getPool();
    // First, get the patient's MySQL ID
    const [patientRows] = await pool.execute<mysql.RowDataPacket[] & { id: number }[]>(
      'SELECT id FROM patients WHERE firebase_uid = ?',
      [firebaseUid]
    );

    if (patientRows.length === 0) {
      return res.status(404).json({ message: 'Patient record not found for this user.' });
    }
    const patientMySQLId = patientRows[0].id;

    // Then, fetch their appointments
    const [appointmentRows] = await pool.execute<mysql.RowDataPacket[] & MyAppointment[]>(
      `SELECT
         a.id,
         d.name as doctorName,
         d.specialty,
         DATE_FORMAT(a.appointment_date, '%Y-%m-%d') as appointmentDate,
         TIME_FORMAT(a.appointment_time, '%H:%i') as appointmentTime,
         a.status,
         a.time_slot_id as timeSlotId
       FROM appointments a
       JOIN doctors d ON a.doctor_id = d.id
       WHERE a.patient_id = ?
       ORDER BY a.appointment_date DESC, a.appointment_time DESC`,
      [patientMySQLId]
    );

    res.status(200).json(appointmentRows);

  } catch (error: any) {
    console.error('Error fetching "my appointments":', error);
    if (error.code === 'auth/id-token-expired' || error.code === 'auth/argument-error') {
      return res.status(401).json({ message: 'Unauthorized: Token is invalid or expired.' });
    }
    res.status(500).json({ message: 'Internal server error', error: error.message });
  }
}