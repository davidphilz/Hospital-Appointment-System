// pages/api/patients/list.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { execute } from '../../../lib/db'; // Adjust path as needed

export interface PatientListItem {
  _id: string; // Corresponds to patients.id
  name: string;
  // You could add other identifying info if needed, like contact or email,
  // but for a dropdown, name is usually sufficient.
}

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method === 'GET') {
    try {
      // Fetch only necessary fields for the dropdown
      const patients = await execute<PatientListItem[]>(
        "SELECT id as _id, name FROM patients ORDER BY name ASC"
      );
      res.status(200).json(patients);
    } catch (error: any) {
      console.error('Failed to fetch patient list:', error);
      res.status(500).json({ message: 'Failed to fetch patient list', error: error.message });
    }
  } else {
    res.setHeader('Allow', ['GET']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}