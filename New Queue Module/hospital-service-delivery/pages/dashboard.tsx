// pages/dashboard.tsx
import { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { io, Socket } from 'socket.io-client';
import { MdNotifications, MdErrorOutline, MdRefresh, MdChevronRight } from 'react-icons/md'; // Added MdChevronRight for Assign/Resolve buttons
import axios, { AxiosError } from 'axios';
import { auth } from '../firebase/firebase'; // For logout
import { signOut } from 'firebase/auth';
import { useRouter } from 'next/router';

// Matches the structure from /api/dashboard/queue-stats.ts
interface QueueData {
  status: 'Waiting' | 'In Progress' | 'Completed'; // Matches your card titles
  count: number;
  color: string; // Hex color string e.g., '#FBBF24' - used for left border
  bgColor?: string; // Optional: Tailwind class for background if different from border logic
}

// Matches the structure from /api/dashboard/priority-alerts.ts
interface Alert {
  id: number;
  name: string; // Reason or title of the alert
  urgency: string; // e.g., "High Priority", "Low Priority"
  // 'reason' might be the same as 'name' or more detailed, adjust as needed
  // The 'color' field from your original Alert interface was for Tailwind classes.
  // Here, we'll derive border color based on urgency to match the image.
}

// Matches the structure from /api/dashboard/metrics.ts
interface Metrics {
  avgWaitTime: number; // Not directly shown in this specific UI, but good to have
  totalPatientsToday: number; // Not directly shown in this specific UI
  resourceUtilization: number; // Not directly shown in this specific UI
}

// --- Mappings to match your UI design ---
const queueCardStyles: Record<string, { bgColor: string; borderColor: string; textColor?: string }> = {
  'Waiting': { bgColor: 'bg-yellow-50', borderColor: 'border-yellow-400', textColor: 'text-yellow-700' },
  'In Progress': { bgColor: 'bg-blue-100', borderColor: 'border-blue-500', textColor: 'text-blue-700' },
  'Completed': { bgColor: 'bg-green-100', borderColor: 'border-green-500', textColor: 'text-green-700' },
};

const alertUrgencyStyles: Record<string, { borderColor: string; textColor: string }> = {
  'High Priority': { borderColor: 'border-red-500', textColor: 'text-red-600' },
  'Emergency': { borderColor: 'border-red-600', textColor: 'text-red-700' }, // Assuming 'Emergency' is higher
  'Medium Priority': { borderColor: 'border-yellow-500', textColor: 'text-yellow-600' },
  'Low Priority': { borderColor: 'border-gray-400', textColor: 'text-gray-500' },
};
// --- End Mappings ---


const Dashboard = () => {
  const router = useRouter();
  const [queues, setQueues] = useState<QueueData[]>([]);
  const [alerts, setAlerts] = useState<Alert[]>([]);
  // Metrics are fetched but not directly used in this specific UI version from the image
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [metrics, setMetrics] = useState<Metrics>({ avgWaitTime: 0, totalPatientsToday: 0, resourceUtilization: 0 });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [socket, setSocket] = useState<Socket | null>(null);

  const fetchData = async () => {
    try {
      setLoading(true);
      setError(null);
      const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

      const [metricsRes, queueRes, alertsRes] = await Promise.all([
        axios.get<Metrics>(`${apiUrl}/api/dashboard/metrics`),
        axios.get<QueueData[]>(`${apiUrl}/api/dashboard/queue-stats`),
        axios.get<Alert[]>(`${apiUrl}/api/dashboard/priority-alerts`)
      ]);

      setMetrics(metricsRes.data);
      setQueues(queueRes.data);
      setAlerts(alertsRes.data);
    } catch (err: any) {
      console.error('Error fetching dashboard data:', err);
      setError(err.response?.data?.message || err.message || 'Failed to load dashboard data.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  useEffect(() => {
    const wsUrl = process.env.NEXT_PUBLIC_WS_URL || 'http://localhost:3000';
    const socketInstance = io(wsUrl, { path: '/api/socket_io', transports: ['websocket'], reconnection: true });
    setSocket(socketInstance);
    socketInstance.on('connect', () => console.log('Dashboard Socket.IO connected'));
    socketInstance.on('queueUpdate', (data: QueueData[]) => setQueues(data));
    socketInstance.on('metricsUpdate', (data: Metrics) => setMetrics(data));
    socketInstance.on('alertUpdate', (data: Alert[]) => setAlerts(data));
    socketInstance.on('disconnect', () => console.log('Dashboard Socket.IO disconnected'));
    socketInstance.on('connect_error', (err) => console.error('Dashboard Socket.IO connection error:', err));
    return () => { socketInstance.disconnect(); setSocket(null); };
  }, []);

  const handleLogout = async () => {
    try {
      await signOut(auth);
      router.push('/login');
    } catch (error) {
      console.error("Error signing out: ", error);
      setError("Failed to logout. Please try again.");
    }
  };

  const urgentAlertCount = alerts.filter(
    alert => alert.urgency.toLowerCase().includes('high') || alert.urgency.toLowerCase().includes('emergency')
  ).length;


  if (loading) { /* ... Loading spinner ... */
    return (
      <div className="min-h-screen bg-gray-100 p-6 flex items-center justify-center">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-600"></div>
        <p className="ml-4 text-gray-700 text-lg">Loading Dashboard...</p>
      </div>
    );
  }

  if (error) { /* ... Error display ... */
    return (
      <div className="min-h-screen bg-gray-100 p-6 flex items-center justify-center">
        <div className="bg-red-100 border border-red-400 text-red-700 px-6 py-8 rounded-lg max-w-lg w-full text-center shadow-xl">
          <MdErrorOutline className="text-5xl text-red-500 mx-auto mb-4" />
          <h3 className="text-2xl font-semibold text-red-800 mb-2">Oops! Something went wrong.</h3>
          <p className="text-red-600 mt-2 mb-6 whitespace-pre-line">{error}</p>
          <button onClick={fetchData} className="flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-lg font-medium">
            <MdRefresh className="text-xl"/> Retry
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 p-4 sm:p-6 lg:p-8">
      <div className="max-w-6xl mx-auto"> {/* Changed from max-w-7xl for a slightly narrower feel like image */}
        {/* Header */}
        <header className="flex justify-between items-center mb-6 sm:mb-8 pb-4 border-b border-gray-200">
          <h1 className="text-xl sm:text-2xl font-bold text-blue-700">Hospital Dashboard</h1>
          <div className="flex items-center gap-3 sm:gap-4">
            <button className="relative p-1 text-gray-500 hover:text-blue-600 focus:outline-none">
              <MdNotifications className="text-xl sm:text-2xl" />
              {/* Optional: Add a badge for urgent notifications if needed */}
            </button>
            <button onClick={handleLogout} className="text-sm text-gray-600 hover:text-blue-600 font-medium">
              Logout
            </button>
          </div>
        </header>

        {/* Queue Status Cards - Matching the image */}
        <section className="mb-6 sm:mb-8">
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
            {queues.map((queueItem, index) => {
              const style = queueCardStyles[queueItem.status] || { bgColor: 'bg-gray-100', borderColor: 'border-gray-400' };
              return (
                <motion.div
                  key={queueItem.status}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.1 }}
                  className={`p-4 rounded-lg shadow-sm ${style.bgColor} border-l-4 ${style.borderColor}`}
                >
                  <h3 className="text-sm font-medium text-gray-700 mb-1">{queueItem.status}</h3>
                  <p className="text-3xl sm:text-4xl font-bold text-gray-800">{queueItem.count}</p>
                </motion.div>
              );
            })}
          </div>
        </section>

        {/* Priority Alert panel - Matching the image */}
        <section>
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }} // Stagger after queue cards
            className="bg-white p-4 sm:p-6 rounded-lg shadow-md"
          >
            <h2 className="text-lg sm:text-xl font-semibold text-gray-800 mb-4">
              Priority Alerts {urgentAlertCount > 0 && `(${urgentAlertCount} Urgent)`}
            </h2>

            {alerts.length === 0 ? (
              <p className="text-gray-500 text-sm text-center py-4">No active priority alerts.</p>
            ) : (
              <div className="space-y-3 max-h-[40vh] sm:max-h-[50vh] overflow-y-auto pr-1"> {/* Scrollable alerts */}
                {alerts.map((alert, index) => {
                  const style = alertUrgencyStyles[alert.urgency] || alertUrgencyStyles['Low Priority'];
                  return (
                    <motion.div
                      key={alert.id}
                      layout
                      initial={{ opacity: 0, x: -20 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: index * 0.05, type: "spring", stiffness: 200, damping: 25 }}
                      className={`p-3 rounded-md border-l-4 ${style.borderColor} bg-gray-50 shadow-xs`}
                    >
                      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-3">
                        {/* Alert Details */}
                        <div className="flex-1 min-w-0">
                          <h3 className={`text-xs font-semibold ${style.textColor} mb-0.5`}>
                            {alert.urgency}
                          </h3>
                          <p className="text-sm text-gray-700 truncate" title={alert.name}>
                            {alert.name} {/* 'name' from Alert interface used as the main description */}
                          </p>
                        </div>
                        {/* Action Buttons */}
                        <div className="flex flex-row gap-2 flex-shrink-0 mt-2 sm:mt-0 self-end sm:self-center">
                          <button className="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors flex items-center gap-1">
                            Assign <MdChevronRight className="hidden sm:inline"/>
                          </button>
                          <button className="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded hover:bg-gray-300 transition-colors flex items-center gap-1">
                            Resolve <MdChevronRight className="hidden sm:inline"/>
                          </button>
                        </div>
                      </div>
                    </motion.div>
                  );
                })}
              </div>
            )}
          </motion.div>
        </section>
      </div>
    </div>
  );
};

export default Dashboard;