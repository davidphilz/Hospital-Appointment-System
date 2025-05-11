// pages/api/admin/register-patient.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../lib/db';         // Adjust path
import admin from '../../../lib/firebaseAdmin';     // Adjust path
import mysql from 'mysql2/promise';
import { IncomingForm, File as FormidableFile } from 'formidable'; // Assuming formidable v3+
import fs from 'fs/promises';
import path from 'path';

// Configure Formidable for file uploads if your admin form also handles them
export const config = {
  api: {
    bodyParser: false, // Required for formidable
  },
};

const UPLOAD_DIR = process.env.UPLOAD_DIR || './public/uploads'; // Same as patient self-registration
fs.mkdir(UPLOAD_DIR, { recursive: true }).catch(console.error);


interface AdminPatientRegistrationPayload {
  name: string;
  email: string;
  contact?: string;
  medicalHistory?: string;
  // Add other fields admin might fill
}

// Define a default password (consider security implications)
const DEFAULT_PATIENT_PASSWORD = process.env.DEFAULT_PATIENT_PASSWORD || '123456';

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', ['POST']);
    return res.status(405).json({ message: `Method ${req.method} Not Allowed` });
  }

  const authorization = req.headers.authorization;
  if (!authorization || !authorization.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Unauthorized: Missing or invalid admin token.' });
  }
  const idToken = authorization.split('Bearer ')[1];

  let connection: mysql.PoolConnection | null = null;

  try {
    // 1. Verify Admin's Token and Role
    let decodedAdminToken;
    try {
      decodedAdminToken = await admin.auth().verifyIdToken(idToken);
    } catch (authError: any) {
      return res.status(401).json({ message: 'Unauthorized: Invalid admin token.', details: authError.code });
    }

    if (decodedAdminToken.role !== 'admin' && decodedAdminToken.role !== 'staff') { // Check custom claim
      return res.status(403).json({ message: 'Forbidden: Insufficient privileges.' });
    }

    // 2. Parse Form Data (including potential file uploads)
    const form = new IncomingForm({
        uploadDir: UPLOAD_DIR,
        keepExtensions: true,
        maxFileSize: 10 * 1024 * 1024, // 10MB per file
        filename: (name, ext, part) => `${Date.now()}_${part.originalFilename || 'file'}${ext}`
    });

    const [fields, filesFromFormidable] = await form.parse(req);

    const getFieldValue = (fieldName: string): string | undefined => {
        const value = fields[fieldName];
        return Array.isArray(value) && value.length > 0 ? value[0] : undefined;
    };

    const name = (getFieldValue('name') || '').trim();
    const email = (getFieldValue('email') || '').trim();
    const contact = getFieldValue('contact');
    const medicalHistory = getFieldValue('medicalHistory');

    if (!name || !email) {
      return res.status(400).json({ message: 'Missing required fields: name and email.' });
    }

    // 3. Create Firebase User for the Patient
    let newFirebaseUser;
    try {
      newFirebaseUser = await admin.auth().createUser({
        email: email,
        emailVerified: false, // Or true, depending on your flow
        password: DEFAULT_PATIENT_PASSWORD,
        displayName: name,
        // photoURL: "some-default-avatar.png", // Optional
        disabled: false,
      });
      console.log(`Successfully created new Firebase user: ${newFirebaseUser.uid} for email: ${email}`);

      // Optionally set a 'patient' role custom claim immediately
      await admin.auth().setCustomUserClaims(newFirebaseUser.uid, { role: 'patient' });
      console.log(`Set 'patient' role for UID: ${newFirebaseUser.uid}`);

    } catch (firebaseError: any) {
      console.error('Firebase user creation failed (admin panel):', firebaseError);
      if (firebaseError.code === 'auth/email-already-exists') {
        return res.status(409).json({ message: 'A user with this email already exists in Firebase.' });
      }
      if (firebaseError.code === 'auth/invalid-password') {
        return res.status(400).json({ message: `The default password "${DEFAULT_PATIENT_PASSWORD}" is too weak. Please configure a stronger default password.`});
      }
      return res.status(500).json({ message: 'Failed to create Firebase user for patient.', error: firebaseError.message });
    }

    // 4. Insert Patient into MySQL Database
    const firebase_uid = newFirebaseUser.uid;
    const contactToInsert = (contact && contact.trim() !== '') ? contact.trim() : '00000000000'; // Or your preferred default/null
    const medicalHistoryToInsert = (medicalHistory && medicalHistory.trim() !== '') ? medicalHistory.trim() : null;

    connection = await getPool().getConnection();
    await connection.beginTransaction();

    // Check if patient with this firebase_uid already exists in MySQL (should not happen if Firebase creation was new)
    const [existingPatientRows] = await connection.execute<mysql.RowDataPacket[]>(
        'SELECT id FROM patients WHERE firebase_uid = ?', [firebase_uid]
    );
    if (existingPatientRows.length > 0) {
        await connection.rollback(); // Should not happen if Firebase uid is new
        console.warn(`MySQL conflict: Patient with firebase_uid ${firebase_uid} already exists. Firebase UID: ${newFirebaseUser.uid}`);
        return res.status(409).json({ message: 'Patient profile with this Firebase linkage already exists in DB.' });
    }


    const [patientInsertResult] = await connection.execute<mysql.OkPacket>(
      'INSERT INTO patients (firebase_uid, name, email, contact, medical_history, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
      [firebase_uid, name, email, contactToInsert, medicalHistoryToInsert]
    );
    const newPatientMySQLId = patientInsertResult.insertId;

    // 5. Handle Medical Record File Uploads (if any, similar to patient-registration)
    const medicalRecordFiles = filesFromFormidable.medicalRecords as FormidableFile[] | FormidableFile | undefined;
    if (medicalRecordFiles) {
        const filesArray = Array.isArray(medicalRecordFiles) ? medicalRecordFiles : [medicalRecordFiles];
        for (const file of filesArray) {
            if (file && file.newFilename) {
                await connection.execute(
                  'INSERT INTO medical_records (patient_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?)',
                  [newPatientMySQLId, file.originalFilename || file.newFilename, `/uploads/${file.newFilename}`, file.mimetype || null]
                );
            }
        }
    }

    await connection.commit();
    console.log(`Admin registered patient. MySQL ID: ${newPatientMySQLId}, Firebase UID: ${firebase_uid}`);
    res.status(201).json({
      message: `Patient "${name}" registered successfully.`,
      patientId: newPatientMySQLId,
      firebaseUid: firebase_uid,
    });

  } catch (error: any) {
    if (connection) await connection.rollback();
    console.error('Error in admin patient registration:', error);
    // If Firebase user was created but DB insert failed, this is an orphaned Firebase account.
    // Needs careful handling or cleanup strategy.
    res.status(500).json({ message: 'Failed to register patient.', error: error.message });
  } finally {
    if (connection) connection.release();
  }
}