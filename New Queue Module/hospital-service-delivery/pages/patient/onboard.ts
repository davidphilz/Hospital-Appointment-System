// pages/api/patients/onboard.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../lib/db'; // Adjust path
import admin from '../../../lib/firebaseAdmin'; // Adjust path for Firebase Admin SDK
import mysql from 'mysql2/promise';

interface OnboardPayload {
  firebaseUid: string;
  email: string;
  name: string;
  contact?: string;
}

// Your handler function (might be named 'handler' or something else)
async function onboardHandler(req: NextApiRequest, res: NextApiResponse) { // Renamed to avoid conflict if you had 'handler' elsewhere
  if (req.method !== 'POST') {
    res.setHeader('Allow', ['POST']);
    return res.status(405).end(`Method ${req.method} Not Allowed`);
  }

  const authorization = req.headers.authorization;
  if (!authorization || !authorization.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Unauthorized: Missing or invalid token for onboarding' });
  }
  const idToken = authorization.split('Bearer ')[1];

  try {
    const decodedToken = await admin.auth().verifyIdToken(idToken);
    const tokenFirebaseUid = decodedToken.uid;

    const { firebaseUid, email, name, contact }: OnboardPayload = req.body;

    if (tokenFirebaseUid !== firebaseUid) {
      console.warn(`UID mismatch during onboarding: Token UID (${tokenFirebaseUid}) vs Payload UID (${firebaseUid})`);
      return res.status(403).json({ message: 'Forbidden: UID mismatch.' });
    }

    if (!firebaseUid || !email || !name) {
      return res.status(400).json({ message: 'Missing required fields: firebaseUid, email, name' });
    }

    const pool = getPool();
    const [existingPatient] = await pool.execute<mysql.RowDataPacket[]>(
      'SELECT id FROM patients WHERE firebase_uid = ?',
      [firebaseUid]
    );

    if (existingPatient.length > 0) {
      console.log(`Patient with firebase_uid ${firebaseUid} already exists (ID: ${existingPatient[0].id}). Onboarding skipped or will update.`);
      return res.status(200).json({ message: 'Patient profile already exists or updated.', patientId: existingPatient[0].id });
    }

    const medicalHistoryPlaceholder = null;
    const [result] = await pool.execute<mysql.OkPacket>(
      'INSERT INTO patients (firebase_uid, email, name, contact, medical_history, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
      [firebaseUid, email, name, contact || null, medicalHistoryPlaceholder]
    );

    // Optional: Set a default custom claim for 'patient' role
    // await admin.auth().setCustomUserClaims(firebaseUid, { role: 'patient' });

    res.status(201).json({ message: 'Patient profile created successfully', patientId: result.insertId });

  } catch (error: any) {
    console.error('Error during patient onboarding:', error);
    if (error.code === 'auth/id-token-expired' || error.code === 'auth/argument-error' || error.code?.startsWith('auth/')) {
      return res.status(401).json({ message: 'Unauthorized: Token is invalid or expired for onboarding.' });
    }
    if (error.code === 'ER_DUP_ENTRY' && error.sqlMessage?.includes('patients.firebase_uid_UNIQUE')) {
        return res.status(409).json({ message: 'This user profile (based on Firebase UID) already exists.'});
    }
    // Add other specific SQL error checks if needed (e.g., unique email)
    res.status(500).json({ message: 'Internal server error during onboarding', error: error.message });
  }
}

// THIS IS THE CRUCIAL LINE:
export default onboardHandler; // Or export default async function handler(...) if you define it that way