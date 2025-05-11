// pages/api/patients/onboard.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../lib/db'; // Adjust path
import admin from '../../../lib/firebaseAdmin'; // Adjust path
import mysql from 'mysql2/promise';

interface OnboardPayload {
  firebaseUid: string;
  email: string;
  name: string;
  contact?: string; // Contact is optional from the client
}

interface ExistingPatientCheck {
  id: number;
  firebase_uid: string;
}

export default async function onboardPatientHandler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', ['POST']);
    return res.status(405).json({ message: `Method ${req.method} Not Allowed` });
  }

  const authorization = req.headers.authorization;
  if (!authorization || !authorization.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Unauthorized: Missing or invalid Firebase ID token.' });
  }
  const idToken = authorization.split('Bearer ')[1];

  try {
    let decodedToken;
    try {
        decodedToken = await admin.auth().verifyIdToken(idToken);
    } catch (authError: any) {
        console.error('Firebase ID Token verification failed during onboarding:', authError.message);
        return res.status(401).json({ message: 'Unauthorized: Invalid or expired Firebase ID token.', details: authError.code });
    }
    const tokenFirebaseUid = decodedToken.uid;
    const tokenEmail = decodedToken.email;

    const { firebaseUid, email: bodyEmail, name, contact }: OnboardPayload = req.body;

    if (tokenFirebaseUid !== firebaseUid) {
      console.warn(`UID mismatch: Token UID (${tokenFirebaseUid}) vs Payload UID (${firebaseUid})`);
      return res.status(403).json({ message: 'Forbidden: User ID mismatch.' });
    }
    if (tokenEmail && bodyEmail && tokenEmail.toLowerCase() !== bodyEmail.toLowerCase()) {
      console.warn(`Email mismatch: Token Email (${tokenEmail}) vs Payload Email (${bodyEmail})`);
      return res.status(403).json({ message: 'Forbidden: Email mismatch.' });
    }

    if (!firebaseUid || !bodyEmail || !name) {
      return res.status(400).json({ message: 'Missing required fields: firebaseUid, email, name.' });
    }

    const pool = getPool();
    let connection: mysql.PoolConnection | null = null;

    try {
      connection = await pool.getConnection();
      await connection.beginTransaction();

      const [existingPatientRows] = await connection.execute<mysql.RowDataPacket[] & ExistingPatientCheck[]>(
        'SELECT id, firebase_uid FROM patients WHERE firebase_uid = ?',
        [firebaseUid]
      );

      if (existingPatientRows.length > 0) {
        await connection.commit();
        return res.status(200).json({ message: 'Patient profile already linked.', patientId: existingPatientRows[0].id });
      }

      const medicalHistoryPlaceholder = null;
      const defaultContactValueFromCode = '00000000000'; // Your desired default

      // Determine the contact value to insert
      const contactToInsert = (contact && contact.trim() !== '')
                              ? contact.trim()
                              : defaultContactValueFromCode; // Apply default if client sent nothing valid

      console.log(`Inserting new patient: UID=${firebaseUid}, Email=${bodyEmail}, Name=${name}, Contact=${contactToInsert}`);

      const [insertResult] = await connection.execute<mysql.OkPacket>(
        'INSERT INTO patients (firebase_uid, email, name, contact, medical_history, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
        [firebaseUid, bodyEmail, name, contactToInsert, medicalHistoryPlaceholder]
      );

      // Optional: Set custom claim
      try {
        const currentUserClaims = (await admin.auth().getUser(firebaseUid)).customClaims;
        if (!currentUserClaims || !currentUserClaims.role) {
            await admin.auth().setCustomUserClaims(firebaseUid, { ...currentUserClaims, role: 'patient' });
            console.log(`Custom claim 'role: patient' set for UID: ${firebaseUid}.`);
        }
      } catch (claimError: any) {
        console.error(`Failed to set 'patient' custom claim for UID ${firebaseUid}:`, claimError.message);
      }

      await connection.commit();
      console.log(`Successfully onboarded patient. Firebase UID: ${firebaseUid}, MySQL ID: ${insertResult.insertId}`);
      res.status(201).json({ message: 'Patient profile created successfully!', patientId: insertResult.insertId });

    } catch (dbError: any) {
      if (connection) { try { await connection.rollback(); } catch (rbError) { console.error("Error during rollback:", rbError); }}
      console.error('Database error during patient onboarding:', dbError);
      if (dbError.code === 'ER_DUP_ENTRY') { /* ... duplicate entry handling ... */ }
      res.status(500).json({ message: 'Internal server error during database operation.', error: dbError.message });
    } finally {
      if (connection) { try { connection.release(); } catch (relError) { console.error("Error releasing connection:", relError); }}
    }

  } catch (error: any) {
    console.error('Overall error in patient onboarding API:', error.message, error.code);
    res.status(500).json({ message: 'An unexpected error occurred.' });
  }
}