// pages/api/doctors/index.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { execute } from '../../../lib/db'; // Adjust path

export interface Doctor { // Keep this interface consistent
  _id: string; // Aliased from id
  name: string;
  specialty: string;
}

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method === 'GET') {
    const { specialty } = req.query; // Get specialty from query params

    try {
      let query = "SELECT id as _id, name, specialty FROM doctors";
      const params: string[] = [];

      if (specialty && typeof specialty === 'string') {
        query += " WHERE specialty = ?";
        params.push(specialty);
      }
      query += " ORDER BY name ASC";

      const doctors = await execute<Doctor[]>(query, params);
      res.status(200).json(doctors);
    } catch (error: any) {
      console.error('Failed to fetch doctors:', error);
      res.status(500).json({ message: 'Failed to fetch doctors', error: error.message });
    }
  } else {
    res.setHeader('Allow', ['GET']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}