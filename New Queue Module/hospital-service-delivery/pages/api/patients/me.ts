// pages/api/patients/me.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../lib/db'; // Adjust path
import admin from '../../../lib/firebaseAdmin'; // Adjust path
import mysql from 'mysql2/promise';

interface PatientProfile {
  id: number; // MySQL patients.id
  firebase_uid: string;
  name: string;
  email: string;
  contact?: string;
  medicalHistory?: string;
  // Add other fields from your patients table
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
    const [rows] = await pool.execute<mysql.RowDataPacket[] & PatientProfile[]>(
      'SELECT id, firebase_uid, name, email, contact, medical_history as medicalHistory FROM patients WHERE firebase_uid = ?',
      [firebaseUid]
    );

    if (rows.length === 0) {
      return res.status(404).json({ message: 'Patient record not found for this user.' });
    }

    res.status(200).json(rows[0]);

  } catch (error: any) {
    console.error('Error fetching patient "me" data:', error);
    if (error.code === 'auth/id-token-expired' || error.code === 'auth/argument-error') {
      return res.status(401).json({ message: 'Unauthorized: Token is invalid or expired.' });
    }
    res.status(500).json({ message: 'Internal server error', error: error.message });
  }
}