// pages/api/dashboard/priority-alerts.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { execute } from '../../../lib/db'; // Adjust path
import { NextApiResponseWithSocket, getIO } from '../socket'; // Adjust path

export interface Alert {
  id: number;
  name: string;
  urgency: string; // e.g., 'High', 'Medium', 'Low'
  reason: string;
  color: string; // This will be derived from urgency or stored as color_indicator
}

interface AlertFromDB {
  id: number;
  name: string;
  urgency: string;
  reason: string;
  color_indicator: string | null; // e.g., 'border-red-500 bg-red-100', 'red', 'yellow'
}

// Helper to map urgency to a color class or predefined color keyword
function mapUrgencyToColor(urgency: string, colorIndicator?: string | null): string {
  if (colorIndicator) {
    // If color_indicator stores Tailwind classes directly
    if (colorIndicator.startsWith('border-') || colorIndicator.startsWith('bg-')) {
        return colorIndicator;
    }
    // If color_indicator stores a keyword like 'red', 'yellow'
    // You might have a mapping here:
    // switch (colorIndicator.toLowerCase()) {
    //   case 'red': return 'border-l-4 border-red-500 bg-red-50'; // Example for alerts list
    //   case 'yellow': return 'border-l-4 border-yellow-500 bg-yellow-50';
    //   default: break;
    // }
  }

  // Fallback based on urgency string
  switch (urgency.toLowerCase()) {
    case 'high':
      return 'border-l-4 border-red-500 bg-red-100'; // Example classes for dashboard.tsx
    case 'medium':
      return 'border-l-4 border-yellow-500 bg-yellow-100';
    case 'low':
      return 'border-l-4 border-blue-500 bg-blue-100';
    default:
      return 'border-l-4 border-gray-500 bg-gray-100';
  }
}

export default async function handler(req: NextApiRequest, res: NextApiResponseWithSocket<Alert[] | { message: string }>) {
  if (req.method === 'GET') {
    try {
      const alertsFromDB = await execute<AlertFromDB[]>(
        `SELECT id, name, urgency, reason, color_indicator
         FROM alerts
         WHERE is_resolved = FALSE
         ORDER BY FIELD(urgency, 'High', 'Medium', 'Low'), created_at DESC` // FIELD ensures custom sort order
      );

      const alerts: Alert[] = alertsFromDB.map(dbAlert => ({
        id: dbAlert.id,
        name: dbAlert.name,
        urgency: dbAlert.urgency,
        reason: dbAlert.reason,
        color: mapUrgencyToColor(dbAlert.urgency, dbAlert.color_indicator),
      }));

      // Optionally, emit this update if you have a listener for 'alertUpdate'
      // const io = getIO(res);
      // if (io) {
      //   io.emit('alertUpdate', alerts);
      // }

      res.status(200).json(alerts);
    } catch (error: any) {
      console.error('Error fetching priority alerts:', error);
      res.status(500).json({ message: 'Failed to fetch priority alerts', error: error.message });
    }
  }
  // POST method could be used to create a new alert
  // PATCH /api/dashboard/priority-alerts/[id] to resolve an alert
  else {
    res.setHeader('Allow', ['GET']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}