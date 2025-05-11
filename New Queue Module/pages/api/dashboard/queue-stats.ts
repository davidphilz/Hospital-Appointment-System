// pages/api/dashboard/queue-stats.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { execute } from '../../../lib/db'; // Adjust path
import { NextApiResponseWithSocket, getIO } from '../socket'; // Adjust path

export interface QueueData {
  status: 'Waiting' | 'In Progress' | 'Completed';
  count: number;
  color: string; // Hex color code for the chart/cards
}

interface QueueStatFromDB {
  status: 'waiting' | 'in-progress' | 'completed';
  count: number;
}

// Helper to map DB status to frontend status and assign colors
function mapDbStatusToQueueData(dbStatus: 'waiting' | 'in-progress' | 'completed', count: number): QueueData {
  switch (dbStatus) {
    case 'waiting':
      return { status: 'Waiting', count, color: '#FBBF24' }; // Amber 400
    case 'in-progress':
      return { status: 'In Progress', count, color: '#3B82F6' }; // Blue 500
    case 'completed':
      return { status: 'Completed', count, color: '#10B981' }; // Emerald 500
    default:
      // Should not happen if DB enum is correct
      return { status: 'Waiting', count, color: '#FBBF24' };
  }
}

export default async function handler(req: NextApiRequest, res: NextApiResponseWithSocket<QueueData[] | { message: string }>) {
  if (req.method === 'GET') {
    try {
      // Fetch counts for each status for patients today
      // Adjust DATE(entry_time) if you want stats for all-time or a different period
      const today = new Date().toISOString().split('T')[0];

      const statsFromDB = await execute<QueueStatFromDB[]>(
        `SELECT status, COUNT(*) as count
         FROM queue
         WHERE DATE(entry_time) = ? OR status != 'completed' -- Include active non-completed from previous days
         GROUP BY status`,
         [today]
      );

      // Ensure all statuses are present, even if count is 0
      const allStatuses: Array<'waiting' | 'in-progress' | 'completed'> = ['waiting', 'in-progress', 'completed'];
      const queueStatsMap = new Map<string, number>();
      statsFromDB.forEach(stat => queueStatsMap.set(stat.status, stat.count));

      const queueStats: QueueData[] = allStatuses.map(statusValue => {
        const count = queueStatsMap.get(statusValue) || 0;
        return mapDbStatusToQueueData(statusValue, count);
      });

      // Optionally, emit this update if you have a listener for general 'queueUpdate' or a specific 'queueStatsUpdate'
      // const io = getIO(res);
      // if (io) {
      //   io.emit('queueUpdate', queueStats); // dashboard.tsx listens to queueUpdate for these cards
      // }

      res.status(200).json(queueStats);
    } catch (error: any) {
      console.error('Error fetching queue stats:', error);
      res.status(500).json({ message: 'Failed to fetch queue stats', error: error.message });
    }
  } else {
    res.setHeader('Allow', ['GET']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}