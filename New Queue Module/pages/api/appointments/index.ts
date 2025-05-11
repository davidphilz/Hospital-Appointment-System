// pages/api/appointments/index.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { getPool } from '../../../lib/db'; // Ensure getPool is correctly exported
import mysql from 'mysql2/promise';
import { getIO } from '../socket'; // Import getIO to emit socket events

interface AppointmentPayload {
    patientId: string;
    doctorId: string;
    date: string; // YYYY-MM-DD
    timeSlotId: string;
}

interface TimeSlotFromDB {
    id: number;
    is_available: boolean;
    slot_time: string; // HH:MM format
}

interface PatientFromDB {
    id: number;
    name: string;
}

// Helper function to fetch and format the entire queue (needed for socket emission)
// This should match the logic in your GET /api/queue endpoint
async function fetchAndFormatQueueForSocket(connection: mysql.PoolConnection): Promise<any[]> {
    // Fetch non-completed items, order by emergency then entry time to derive position
    const [queueDataFromDB] = await connection.execute<mysql.RowDataPacket[]>(
      `SELECT q.id as queue_id, q.patient_id, p.name as patient_name, q.status, q.wait_time_estimate_minutes, q.is_emergency, q.entry_time
       FROM queue q
       JOIN patients p ON q.patient_id = p.id
       WHERE q.status != 'completed'
       ORDER BY q.is_emergency DESC, q.entry_time ASC`
    );

    return queueDataFromDB.map((item: any, index: number) => ({
      id: String(item.patient_id), // Use patient_id as the unique ID for client
      name: item.patient_name,
      status: item.status,
      waitTime: item.wait_time_estimate_minutes,
      isEmergency: Boolean(item.is_emergency),
      position: index + 1,
    }));
}


export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method === 'POST') {
    const { patientId, doctorId, date, timeSlotId }: AppointmentPayload = req.body;

    if (!patientId || !doctorId || !date || !timeSlotId) {
      return res.status(400).json({ message: 'Missing required appointment data (patientId, doctorId, date, timeSlotId)' });
    }

    let connection: mysql.PoolConnection | null = null;

    try {
      connection = await getPool().getConnection();
      await connection.beginTransaction();

      // 1. Verify patient exists and get their name
      const [patientRows] = await connection.execute<mysql.RowDataPacket[] & PatientFromDB[]>(
        'SELECT id, name FROM patients WHERE id = ?',
        [patientId]
      );
      if (patientRows.length === 0) {
        await connection.rollback();
        return res.status(404).json({ message: `Patient with ID ${patientId} not found.` });
      }
      const patientName = patientRows[0].name;

      // 2. Find the time_slot by its ID and check availability
      const [slotRows] = await connection.execute<mysql.RowDataPacket[] & TimeSlotFromDB[]>(
        'SELECT id, is_available, TIME_FORMAT(slot_time, "%H:%i") as slot_time FROM time_slots WHERE id = ? AND doctor_id = ? AND slot_date = ? AND is_available = TRUE FOR UPDATE',
        [timeSlotId, doctorId, date]
      );

      if (slotRows.length === 0) {
        await connection.rollback();
        return res.status(409).json({ message: 'Selected time slot is not available, does not exist, or does not match selected doctor/date.' });
      }
      const appointmentTime = slotRows[0].slot_time;

      // 3. Create appointment
      const [appointmentResult] = await connection.execute<mysql.OkPacket>(
        'INSERT INTO appointments (patient_id, doctor_id, time_slot_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?)',
        [patientId, doctorId, timeSlotId, date, `${appointmentTime}:00`, 'Scheduled'] // Added default status
      );
      const newAppointmentId = appointmentResult.insertId;

      // 4. Mark time slot as unavailable
      await connection.execute(
        'UPDATE time_slots SET is_available = FALSE WHERE id = ?',
        [timeSlotId]
      );

      // 5. Add patient to the queue
      // For wait_time_estimate_minutes, using a placeholder. This could be more dynamic.
      // For appointments on future dates, you might NOT want to add them immediately or set a very high wait time / different status.
      // This example assumes immediate addition to 'waiting' queue.
      const defaultWaitTimeEstimate = 15; // Example placeholder
      const isEmergencyBooking = false; // Assume bookings are not emergencies by default

      await connection.execute(
        'INSERT INTO queue (patient_id, patient_name, status, wait_time_estimate_minutes, is_emergency, entry_time) VALUES (?, ?, ?, ?, ?, NOW())',
        [patientId, patientName, 'waiting', defaultWaitTimeEstimate, isEmergencyBooking]
      );

      await connection.commit();

      // After successful commit, emit socket event for queue update
      const io = getIO(res); // Pass res to initialize if needed for this request-response cycle
      if (io && connection) { // Check connection as fetchAndFormatQueueForSocket needs it
        try {
            const updatedQueueForSocket = await fetchAndFormatQueueForSocket(connection);
            io.emit('queue-update', updatedQueueForSocket);
            console.log('Emitted queue-update after new appointment and queue entry.');
        } catch (socketEmitError) {
            console.error("Error fetching queue for socket emission after appointment:", socketEmitError);
        }
      } else if (!io) {
        console.warn('Socket.IO instance not found for appointment, cannot emit queue-update.');
      }


      res.status(201).json({ message: 'Appointment created and patient added to queue successfully!', appointmentId: newAppointmentId });

    } catch (error: any) {
      if (connection) await connection.rollback();
      console.error('Appointment creation or queue addition failed:', error); // Updated error log message
      // ... (existing specific error handling for ER_DUP_ENTRY, ER_NO_REFERENCED_ROW_2) ...
      if (error.code === 'ER_NO_REFERENCED_ROW_2' && error.sqlMessage?.includes('CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`)')) {
        return res.status(400).json({ message: `Invalid Patient ID: ${patientId}. The patient does not exist.`});
      }
      if (error.code === 'ER_DUP_ENTRY' && error.sqlMessage?.includes("appointments.time_slot_id_UNIQUE")) {
         return res.status(409).json({ message: 'This time slot has just been booked.' });
      }
      // Handle potential foreign key error for queue insert if patientId was somehow invalid despite earlier check
      if (error.code === 'ER_NO_REFERENCED_ROW_2' && error.sqlMessage?.includes('CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`patient_id`)')) {
        return res.status(400).json({ message: `Failed to add to queue: Patient ID ${patientId} became invalid.`});
      }
      res.status(500).json({ message: 'Failed to book appointment or add to queue.', error: error.message });
    } finally {
        if (connection) connection.release();
    }
  } else {
    res.setHeader('Allow', ['POST']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}