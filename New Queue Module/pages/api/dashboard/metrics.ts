// pages/api/dashboard/metrics.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { execute } from '../../../lib/db'; // Adjust path as needed
import { NextApiResponseWithSocket, getIO } from '../socket'; // Adjust path

interface Metrics {
  avgWaitTime: number;
  totalPatientsToday: number;
  resourceUtilization: number; // This will be a placeholder or simple calculation for now
}

interface AvgWaitTimeResult {
  average_wait: number | null;
}

interface TotalPatientsResult {
  total_patients: number;
}

// A simple placeholder for resource utilization calculation
async function calculateResourceUtilization(): Promise<number> {
  // Example: (booked appointment slots for today) / (total available slots for today) * 100
  // This requires querying 'appointments' and 'time_slots' tables.
  // For simplicity, we'll return a static value or a very basic calculation.
  try {
    const today = new Date().toISOString().split('T')[0];

    const [bookedSlotsResult]: any = await execute(
      `SELECT COUNT(*) as booked_slots FROM appointments WHERE appointment_date = ? AND status != 'Cancelled'`,
      [today]
    );
    const bookedSlots = bookedSlotsResult[0]?.booked_slots || 0;

    const [totalSlotsResult]: any = await execute(
      `SELECT COUNT(*) as total_slots FROM time_slots WHERE slot_date = ?`,
      [today]
    );
    const totalSlots = totalSlotsResult[0]?.total_slots || 0;

    if (totalSlots === 0) return 0;
    return Math.round((bookedSlots / totalSlots) * 100);

  } catch (error) {
    console.error("Error calculating resource utilization:", error);
    return 0; // Default if calculation fails
  }
}


export default async function handler(req: NextApiRequest, res: NextApiResponseWithSocket<Metrics | { message: string }>) {
  if (req.method === 'GET') {
    try {
      const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD format

      // Average Wait Time for completed patients today
      const [avgWaitResult]: any = await execute<AvgWaitTimeResult[]>(
        `SELECT AVG(wait_time_estimate_minutes) as average_wait
         FROM queue
         WHERE status = 'completed' AND DATE(updated_at) = ?`,
        [today]
      );
      const avgWaitTime = Math.round(avgWaitResult[0]?.average_wait || 0);

      // Total patients processed or in queue today
      const [totalPatientsResult]: any = await execute<TotalPatientsResult[]>(
        `SELECT COUNT(DISTINCT patient_id) as total_patients
         FROM queue
         WHERE DATE(entry_time) = ?`,
        [today]
      );
      const totalPatientsToday = totalPatientsResult[0]?.total_patients || 0;

      // Resource Utilization (placeholder logic)
      const resourceUtilization = await calculateResourceUtilization();

      const metrics: Metrics = {
        avgWaitTime,
        totalPatientsToday,
        resourceUtilization,
      };

      // Optionally, emit this update if you have a listener for 'metricsUpdate'
      // const io = getIO(res);
      // if (io) {
      //   io.emit('metricsUpdate', metrics);
      // }

      res.status(200).json(metrics);
    } catch (error: any) {
      console.error('Error fetching dashboard metrics:', error);
      res.status(500).json({ message: 'Failed to fetch dashboard metrics', error: error.message });
    }
  } else {
    res.setHeader('Allow', ['GET']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}