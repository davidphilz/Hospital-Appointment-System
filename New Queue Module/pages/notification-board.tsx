// pages/notification-board.tsx
import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import {
  MdPerson,
  MdNotifications,
  MdEmergency,
  MdCheckCircle,
  MdHourglassEmpty,
  MdOutlineUpdate,
  MdMenu,
  MdClose,
  MdErrorOutline, // For error display
  MdRefresh,      // For retry button
} from 'react-icons/md';
import { useRouter } from 'next/router';
import axios, { AxiosError } from 'axios';
import { io, Socket } from 'socket.io-client';

// Matches the structure from /api/queue (GET)
interface QueueItem {
  id: string; // This should be patient's unique ID (e.g., patient_id from DB)
  name: string;
  status: 'waiting' | 'in-progress' | 'completed';
  waitTime: number; // In minutes
  position: number;
  isEmergency: boolean;
  appointmentTime?: string; // Optional, if your API provides it
}

interface ApiErrorResponse {
  message: string;
  error?: string;
}

// Configuration for status display
const statusConfig: Record<QueueItem['status'], { color: string; icon: JSX.Element; label: string }> = {
  waiting: {
    color: 'bg-yellow-100 text-yellow-700 border-yellow-400',
    icon: <MdHourglassEmpty className="text-yellow-500" />,
    label: 'Waiting',
  },
  'in-progress': {
    color: 'bg-blue-100 text-blue-700 border-blue-400',
    icon: <MdNotifications className="text-blue-500" />,
    label: 'In Progress',
  },
  completed: {
    color: 'bg-green-100 text-green-700 border-green-400',
    icon: <MdCheckCircle className="text-green-500" />,
    label: 'Called / Completed', // Or just 'Completed'
  },
};

const PatientQueueNotificationBoard = () => {
  const router = useRouter();
  // patientId could come from a query parameter, e.g., /notification-board?patientId=XYZ
  // Or if a patient logs in, you might get their ID from auth context.
  // For this example, let's try to get it from router query.
  const [currentPatientId, setCurrentPatientId] = useState<string | null>(null);

  const [queue, setQueue] = useState<QueueItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [lastUpdate, setLastUpdate] = useState<string>('');
  const [socketConnectionStatus, setSocketConnectionStatus] = useState<'connecting' | 'connected' | 'disconnected'>('connecting');
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false); // Your original state for mobile view
  const [socket, setSocket] = useState<Socket | null>(null);

  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';
  const wsUrl = process.env.NEXT_PUBLIC_WS_URL || 'http://localhost:3000';

  useEffect(() => {
    if (router.isReady && router.query.patientId) {
      setCurrentPatientId(router.query.patientId as string);
    }
  }, [router.isReady, router.query.patientId]);


  const fetchInitialData = async () => {
    setIsLoading(true);
    setError(null);
    try {
      const response = await axios.get<QueueItem[]>(`${apiUrl}/api/queue`);
      // Filter out 'completed' patients unless they are the 'currentPatientId'
      // Or decide if completed should show for a short while
      const activeQueue = response.data.filter(p => p.status !== 'completed' || p.id === currentPatientId);
      setQueue(activeQueue);
      setLastUpdate(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>;
      console.error('Failed to load queue data:', error.response?.data || error.message);
      setError(error.response?.data?.message || 'Failed to load queue data. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchInitialData(); // Fetch data on mount

    const socketInstance = io(wsUrl, {
      path: '/api/socket_io', // Important: matches server-side path
      transports: ['websocket'],
      reconnection: true,
      reconnectionAttempts: 5,
      reconnectionDelay: 1000,
      timeout: 10000, // Connection timeout
    });
    setSocket(socketInstance);

    socketInstance.on('connect', () => {
      console.log('NotificationBoard Socket.IO connected:', socketInstance.id);
      setSocketConnectionStatus('connected');
      // Optionally re-fetch data on connect if needed, though initial fetch might suffice
      // fetchInitialData();
    });

    socketInstance.on('queue-update', (updatedQueue: QueueItem[]) => {
      console.log('Received queue-update from socket:', updatedQueue);
      // Filter out 'completed' patients unless they are the 'currentPatientId'
      const activeQueue = updatedQueue.filter(p => p.status !== 'completed' || p.id === currentPatientId);
      setQueue(activeQueue);
      setLastUpdate(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
      setError(null); // Clear previous errors on successful update
    });

    socketInstance.on('connect_error', (err) => {
      console.error('NotificationBoard Socket.IO connection error:', err);
      setSocketConnectionStatus('disconnected');
      // Avoid flooding with errors if initial data is already loaded
      if (queue.length === 0) { // Only set error if no data is present
        setError(prev => prev ? `${prev}\nReal-time updates connection failed.` : 'Real-time updates connection failed. Displaying last known data.');
      }
    });

    socketInstance.on('disconnect', (reason) => {
      console.log('NotificationBoard Socket.IO disconnected:', reason);
      setSocketConnectionStatus('disconnected');
      // Optionally inform user about disconnection if it wasn't intentional
    });

    return () => {
      console.log('Disconnecting notification board socket');
      socketInstance.disconnect();
      setSocket(null);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [apiUrl, wsUrl, currentPatientId]); // Add currentPatientId to re-filter if it changes

  const getPatientStatusDisplay = (status: QueueItem['status']) => {
    const config = statusConfig[status];
    return (
      <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${config.color}`}>
        {config.icon}
        <span className="hidden sm:inline">{config.label}</span>
        <span className="sm:hidden capitalize">{status.replace('-', ' ')}</span>
      </span>
    );
  };

  const currentPatientDetails = currentPatientId ? queue.find(patient => patient.id === currentPatientId) : null;

  if (isLoading && queue.length === 0) { // Show main loader only if no data yet
    return (
      <div className="min-h-screen bg-gray-100 flex flex-col items-center justify-center gap-4 p-4">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-600"></div>
        <p className="text-gray-700 text-lg">
          {socketConnectionStatus === 'connecting' ? 'Connecting to live updates...' : 'Loading queue information...'}
        </p>
      </div>
    );
  }

  if (error && queue.length === 0) { // Show main error only if no data could be loaded at all
    return (
      <div className="min-h-screen bg-gray-100 flex flex-col items-center justify-center gap-4 p-4">
        <div className="bg-red-100 border border-red-400 p-6 rounded-lg max-w-md w-full text-center shadow-xl">
          <MdErrorOutline className="text-5xl text-red-500 mx-auto mb-4" />
          <h3 className="text-xl font-semibold text-red-800 mb-2">Error Loading Queue</h3>
          <p className="text-red-600 mt-2 mb-6 whitespace-pre-line">{error}</p>
          <button
            onClick={fetchInitialData}
            className="flex items-center justify-center gap-2 mt-4 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            <MdRefresh className="text-lg"/>
            Retry
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 p-4 sm:p-6 lg:p-8">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-center mb-6 pb-4 border-b border-gray-300">
          <div className="flex items-center gap-3">
            <MdOutlineUpdate className="text-3xl text-blue-600" />
            <h1 className="text-2xl sm:text-3xl font-bold text-gray-800">
              Live Patient Queue
            </h1>
          </div>
          <div className="text-sm text-gray-500 mt-2 sm:mt-0">
            {socketConnectionStatus === 'connected' ? `Last updated: ${lastUpdate}` :
             socketConnectionStatus === 'connecting' ? 'Connecting...' :
             <span className="text-red-500 font-medium">Updates Paused</span>}
          </div>
        </div>

        {/* Display general error if data is present but socket fails later */}
        {error && queue.length > 0 && (
             <div className="mb-4 p-3 rounded-md text-sm bg-yellow-100 border border-yellow-400 text-yellow-700 flex items-center gap-2">
                <MdErrorOutline className="text-lg flex-shrink-0" />
                <p>{error}</p>
            </div>
        )}


        {/* Current Patient Panel (if applicable) */}
        {currentPatientDetails && (
          <motion.div
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            className="mb-8 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl shadow-lg p-5 sm:p-6 border border-gray-200"
          >
            <h2 className="text-xl font-semibold mb-3 flex items-center gap-2">
              <MdPerson className="text-2xl" />
              Your Status: {currentPatientDetails.name}
            </h2>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center sm:text-left">
              <div className="space-y-1 p-3 bg-white/20 rounded-lg">
                <h3 className="text-sm font-medium text-blue-100">Position in Queue</h3>
                <p className="text-3xl font-bold">#{currentPatientDetails.position}</p>
              </div>
              <div className="space-y-1 p-3 bg-white/20 rounded-lg flex flex-col items-center sm:items-start">
                <h3 className="text-sm font-medium text-blue-100">Current Status</h3>
                <div className={`inline-flex items-center gap-1.5 mt-1 px-2.5 py-1 rounded-full text-xs font-semibold shadow ${statusConfig[currentPatientDetails.status].color.replace('bg-', 'bg-white text-').replace('-100', '-700')}`}>
                  {statusConfig[currentPatientDetails.status].icon}
                  {statusConfig[currentPatientDetails.status].label}
                </div>
              </div>
              <div className="space-y-1 p-3 bg-white/20 rounded-lg">
                <h3 className="text-sm font-medium text-blue-100">Estimated Wait Time</h3>
                <p className="text-2xl font-semibold">
                  {currentPatientDetails.status === 'in-progress' ? 'Being attended' :
                   currentPatientDetails.waitTime > 0 ? `${currentPatientDetails.waitTime} min` :
                   currentPatientDetails.status === 'waiting' ? 'Shortly' : 'Completed'}
                </p>
              </div>
            </div>
          </motion.div>
        )}

        {/* Queue Table - Desktop / List - Mobile */}
        {queue.length === 0 && !isLoading && (
            <div className="text-center py-10">
                <MdNotifications className="text-5xl text-gray-400 mx-auto mb-3" />
                <p className="text-gray-600 text-lg">The queue is currently empty.</p>
            </div>
        )}

        {queue.length > 0 && (
        <div className="bg-white rounded-xl shadow-lg overflow-hidden">
          {/* Desktop Table Header */}
          <div className="hidden sm:grid grid-cols-12 gap-4 p-4 bg-gray-200 font-semibold text-gray-700 text-sm border-b border-gray-300">
            <div className="col-span-1 text-center">#</div>
            <div className="col-span-4">Patient Name</div>
            <div className="col-span-3 text-center">Status</div>
            <div className="col-span-2 text-center">Wait Time</div>
            <div className="col-span-2 text-center">Priority</div>
          </div>

          {/* Queue Items */}
          <div className="divide-y divide-gray-200">
            {queue.map((patient, index) => (
              <motion.div
                key={patient.id} // Use patient.id which should be unique
                layout // Animate layout changes
                initial={{ opacity: 0, y:10 }}
                animate={{ opacity: 1, y:0 }}
                transition={{ duration: 0.3, delay: index * 0.05 }}
                className={`p-4 items-center hover:bg-gray-50 transition-colors
                  ${currentPatientId === patient.id ? 'bg-blue-50 border-l-4 border-blue-500' : ''}
                  ${patient.isEmergency && currentPatientId !== patient.id ? 'bg-red-50 border-l-4 border-red-500' : ''}
                `}
              >
                {/* Mobile View */}
                <div className="sm:hidden">
                  <div className="flex justify-between items-start mb-2">
                    <div className="font-semibold text-gray-800 text-lg">
                        #{patient.position} {patient.name}
                        {patient.isEmergency && <MdEmergency className="inline text-red-500 ml-1 text-xl" titleAccess='Emergency'/>}
                    </div>
                    {getPatientStatusDisplay(patient.status)}
                  </div>
                  <div className="text-sm text-gray-600">
                    Est. Wait: {patient.status === 'in-progress' ? 'Now' : patient.waitTime > 0 ? `${patient.waitTime} min` : 'Shortly'}
                  </div>
                </div>

                {/* Desktop View Grid */}
                <div className="hidden sm:grid grid-cols-12 gap-4 items-center">
                  <div className="col-span-1 text-center font-medium text-gray-700">{patient.position}</div>
                  <div className="col-span-4 flex items-center gap-2">
                    <MdPerson className="text-gray-400 text-xl" />
                    <span className="text-gray-800">{patient.name}</span>
                  </div>
                  <div className="col-span-3 text-center">
                    {getPatientStatusDisplay(patient.status)}
                  </div>
                  <div className="col-span-2 text-center text-gray-700">
                    {patient.status === 'in-progress' ? 'Now' : patient.waitTime > 0 ? `${patient.waitTime} min` : 'Shortly'}
                  </div>
                  <div className="col-span-2 text-center">
                    {patient.isEmergency ? (
                      <span className="inline-flex items-center gap-1 text-red-600 font-semibold">
                        <MdEmergency /> Emergency
                      </span>
                    ) : (
                      <span className="text-gray-500">Standard</span>
                    )}
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
        )}
      </div>
    </div>
  );
};

export default PatientQueueNotificationBoard;