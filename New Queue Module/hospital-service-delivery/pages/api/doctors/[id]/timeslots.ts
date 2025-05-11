// pages/api/doctors/[id]/timeslots.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { execute } from '../../../../lib/db'; // Adjust path as needed
import mysql from 'mysql2/promise'; // For types

interface TimeSlotForClient { // What the client expects
    _id: string; // Mapped from time_slots.id
    time: string; // Formatted time, e.g., "09:00"
    available: boolean;
}

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'GET') {
    res.setHeader('Allow', ['GET']);
    return res.status(405).end(`Method ${req.method} Not Allowed`);
  }

  const { id: doctorId, date } = req.query; // Extract doctorId from path and date from query

  if (!doctorId || typeof doctorId !== 'string') {
    return res.status(400).json({ message: 'Doctor ID is required in the path.' });
  }

  if (!date || typeof date !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(date)) {
    // Basic validation for YYYY-MM-DD format
    return res.status(400).json({ message: 'A valid date (YYYY-MM-DD) query parameter is required.' });
  }

  try {
    const timeSlotsFromDB = await execute<any[]>( // Use more specific type if possible
      `SELECT
         id,
         TIME_FORMAT(slot_time, '%H:%i') as time, -- Format time as HH:MM
         is_available
       FROM time_slots
       WHERE doctor_id = ? AND slot_date = ? AND is_available = TRUE
       ORDER BY slot_time ASC`,
      [doctorId, date] // Use both doctorId and the specific date in the query
    );

    // Map to the client-expected format (_id, available)
    const formattedTimeSlots: TimeSlotForClient[] = timeSlotsFromDB.map(slot => ({
        _id: String(slot.id), // Ensure _id is a string if frontend expects that
        time: slot.time,
        available: Boolean(slot.is_available), // Ensure boolean
    }));

    res.status(200).json(formattedTimeSlots);

  } catch (error: any) {
    console.error(`Error fetching timeslots for doctor ${doctorId} on date ${date}:`, error);
    res.status(500).json({ message: 'Failed to fetch available time slots', error: error.message });
  }
}