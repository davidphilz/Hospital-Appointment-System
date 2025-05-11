// pages/api/specialties/index.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { execute } from '../../../lib/db'; // Adjust path

export interface Specialty {
  name: string; // The specialty name
}

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method === 'GET') {
    try {
      const specialties = await execute<{ specialty: string }[]>(
        "SELECT DISTINCT specialty FROM doctors WHERE specialty IS NOT NULL AND specialty != '' ORDER BY specialty ASC"
      );
      // Transform to match Specialty interface if needed, or just send as is
      const formattedSpecialties: Specialty[] = specialties.map(s => ({ name: s.specialty }));
      res.status(200).json(formattedSpecialties);
    } catch (error: any) {
      console.error('Failed to fetch specialties:', error);
      res.status(500).json({ message: 'Failed to fetch specialties', error: error.message });
    }
  } else {
    res.setHeader('Allow', ['GET']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}