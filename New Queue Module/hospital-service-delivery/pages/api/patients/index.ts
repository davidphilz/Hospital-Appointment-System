// pages/api/patients/index.ts
// ... (other imports) ...
import type { NextApiRequest, NextApiResponse } from 'next';
import { IncomingForm, File } from 'formidable'; // Assuming 'File' is from formidable v3
// import { Fields, Files } from 'formidable'; // For formidable v2 if you are using it
import fs from 'fs/promises';
import path from 'path';
import { query, execute } from '../../../lib/db'; // Adjust path
import mysql from 'mysql2/promise'; // For OkPacket type

export const config = {
  api: {
    bodyParser: false,
  },
};

const UPLOAD_DIR = process.env.UPLOAD_DIR || './public/uploads';
fs.mkdir(UPLOAD_DIR, { recursive: true }).catch(console.error);

interface PatientFormDataFromClient { // What the client sends
  name: string;
  contact: string;
  email: string;
  medicalHistory?: string; // Optional from client
}

interface PatientDataForDB { // What we insert into DB
    name: string;
    contact: string;
    email: string;
    medical_history: string | null; // Explicitly allow null for DB
}


export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method === 'POST') {
    try {
      const form = new IncomingForm({
        uploadDir: UPLOAD_DIR,
        keepExtensions: true,
        maxFileSize: 10 * 1024 * 1024,
        filename: (name, ext, part) => {
            // Ensure part.originalFilename is not null before using it
            return `${Date.now()}_${part.originalFilename || 'unknownfile'}${ext}`;
        }
      });

      // Formidable v3 parse returns a promise with [fields, files]
      const [fields, filesFromFormidable] = await form.parse(req);

      // Helper to get single string value from Formidable's field arrays
      const getFieldValue = (fieldName: string): string | undefined => {
        const value = fields[fieldName];
        if (Array.isArray(value) && value.length > 0) {
          return value[0];
        }
        return undefined;
      };

      const name = (getFieldValue('name') || '').trim();
      const contact = (getFieldValue('contact') || '').trim();
      const email = (getFieldValue('email') || '').trim();
      const medicalHistoryInput = getFieldValue('medicalHistory'); // Might be undefined or empty string

      // Basic validation (can be more extensive)
      if (!name || !contact || !email) {
        return res.status(400).json({ message: 'Missing required fields: name, contact, email' });
      }
      // Add more specific validation if needed (e.g., email format, phone format)

      const patientDataForDB: PatientDataForDB = {
        name: name,
        contact: contact,
        email: email,
        medical_history: medicalHistoryInput && medicalHistoryInput.trim() !== '' ? medicalHistoryInput.trim() : null, // <--- MODIFIED HERE
      };

      const patientResult = await execute<mysql.OkPacket>(
        'INSERT INTO patients (name, contact, email, medical_history) VALUES (?, ?, ?, ?)',
        [patientDataForDB.name, patientDataForDB.contact, patientDataForDB.email, patientDataForDB.medical_history]
      );
      const patientId = patientResult.insertId;

      const medicalRecordFiles = filesFromFormidable.medicalRecords; // Key used in frontend FormData
      const uploadedFilesInfo: {fileName: string, filePath: string, fileType: string | null}[] = [];

      if (medicalRecordFiles) {
        const filesArray = Array.isArray(medicalRecordFiles) ? medicalRecordFiles : [medicalRecordFiles];
        for (const file of filesArray) {
          if (file && file.newFilename) { // formidable v3 uses newFilename
            const originalFilename = file.originalFilename || file.newFilename;
            const filePathForClient = `/uploads/${file.newFilename}`; // Path for client access
            const fileMimetype = file.mimetype || null; // Ensure mimetype is null if not present

            uploadedFilesInfo.push({
                fileName: originalFilename,
                filePath: filePathForClient,
                fileType: fileMimetype
            });

            await query(
              'INSERT INTO medical_records (patient_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?)',
              [patientId, originalFilename, filePathForClient, fileMimetype] // Pass fileMimetype (which can be null)
            );
          }
        }
      }

      res.status(201).json({
        message: 'Patient registered successfully!', // More positive message
        patientId,
        files: uploadedFilesInfo,
      });

    } catch (error: any) {
      console.error('Patient registration error:', error);
      // You might want to clean up uploaded files if DB transaction failed for medical_records
      // For now, a general error:
      res.status(500).json({
        message: 'Failed to register patient',
        error: error.message,
        details: error.code === 'ER_BIND_ERROR' ? 'A value submitted was undefined, expected null or a valid value.' : undefined
      });
    }
  } else {
    res.setHeader('Allow', ['POST']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}