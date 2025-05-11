// pages/api/patients/me/update.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../../lib/db'; // Adjust path
import admin from '../../../../lib/firebaseAdmin'; // Adjust path
import mysql from 'mysql2/promise';
import { IncomingForm, File as FormidableFile } from 'formidable';
import fs from 'fs/promises';
import path from 'path';

export const config = {
  api: {
    bodyParser: false, // Required for formidable to handle multipart/form-data
  },
};

const UPLOAD_DIR = process.env.UPLOAD_DIR || './public/uploads';
fs.mkdir(UPLOAD_DIR, { recursive: true }).catch(console.error);

interface UpdatePayload { // Fields that can be sent from client
  name?: string;
  contact?: string;
  medicalHistory?: string;
  // No email here, assuming it's not directly editable by user this way
}

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'PATCH') { // Using PATCH for partial updates
    res.setHeader('Allow', ['PATCH']);
    return res.status(405).end(`Method ${req.method} Not Allowed`);
  }

  const authorization = req.headers.authorization;
  if (!authorization || !authorization.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Unauthorized: Missing or invalid token.' });
  }
  const idToken = authorization.split('Bearer ')[1];

  let connection: mysql.PoolConnection | null = null;

  try {
    const decodedToken = await admin.auth().verifyIdToken(idToken);
    const firebaseUid = decodedToken.uid;

    // Get patient's MySQL ID first to ensure they exist and for FK in medical_records
    const pool = getPool();
    const [patientRows] = await pool.execute<mysql.RowDataPacket[] & { id: number }[]>(
        'SELECT id FROM patients WHERE firebase_uid = ?', [firebaseUid]
    );
    if (patientRows.length === 0) {
        return res.status(404).json({ message: 'Patient record not found for this user.' });
    }
    const patientMySQLId = patientRows[0].id;


    const form = new IncomingForm({
        uploadDir: UPLOAD_DIR,
        keepExtensions: true,
        maxFileSize: (parseInt(process.env.MAX_FILE_SIZE_MB_PROFILE || '5')) * 1024 * 1024, // Use constant from frontend
        filename: (name, ext, part) => `${Date.now()}_patient${patientMySQLId}_${part.originalFilename || 'record'}${ext}`
    });

    const [fields, filesFromFormidable] = await form.parse(req);

    const getFieldValue = (fieldName: string): string | undefined => {
        const value = fields[fieldName];
        return Array.isArray(value) && value.length > 0 ? value[0] : undefined;
    };

    const updateData: Partial<UpdatePayload> = {};
    const receivedName = getFieldValue('name');
    const receivedContact = getFieldValue('contact');
    const receivedMedicalHistory = getFieldValue('medicalHistory');

    if (receivedName !== undefined) updateData.name = receivedName.trim(); // Allow empty string if user clears it, or add validation
    if (receivedContact !== undefined) updateData.contact = receivedContact.trim() || null; // Store empty as null or specific default
    if (receivedMedicalHistory !== undefined) updateData.medicalHistory = receivedMedicalHistory.trim() || null;


    connection = await pool.getConnection();
    await connection.beginTransaction();

    // 1. Update patients table if there are changes
    if (Object.keys(updateData).length > 0) {
        const setClauses = Object.keys(updateData).map(key => `${key === 'medicalHistory' ? 'medical_history' : key} = ?`).join(', ');
        const values = Object.values(updateData);

        if (setClauses) { // Only update if there are fields to update
            const updateQuery = `UPDATE patients SET ${setClauses}, updated_at = NOW() WHERE id = ? AND firebase_uid = ?`;
            await connection.execute(updateQuery, [...values, patientMySQLId, firebaseUid]);
            console.log(`Updated patient text details for patient ID: ${patientMySQLId}`);
        }
    }

    // 2. Handle new medical record file uploads
    const medicalRecordFiles = filesFromFormidable.medicalRecords as FormidableFile[] | FormidableFile | undefined;
    if (medicalRecordFiles) {
        const filesArray = Array.isArray(medicalRecordFiles) ? medicalRecordFiles : [medicalRecordFiles];
        for (const file of filesArray) {
            if (file && file.newFilename) {
                await connection.execute(
                  'INSERT INTO medical_records (patient_id, file_name, file_path, file_type, uploaded_at) VALUES (?, ?, ?, ?, NOW())',
                  [patientMySQLId, file.originalFilename || file.newFilename, `/uploads/${file.newFilename}`, file.mimetype || null]
                );
            }
        }
        console.log(`Added ${filesArray.length} new medical records for patient ID: ${patientMySQLId}`);
    }

    await connection.commit();
    res.status(200).json({ message: 'Profile updated successfully.' });

  } catch (error: any) {
    if (connection) await connection.rollback();
    console.error('Error updating patient profile:', error);
    // Handle specific errors like unique constraint violations if email were editable
    res.status(500).json({ message: 'Failed to update profile.', error: error.message });
  } finally {
    if (connection) connection.release();
  }
}