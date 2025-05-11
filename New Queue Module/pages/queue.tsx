// pages/queue.tsx
import React, { useState, useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  MdEmergency,
  MdAccessTime,
  MdPerson,
  MdErrorOutline,
  MdRefresh,
} from 'react-icons/md';
import axios, { AxiosError } from 'axios';
import { io, Socket } from 'socket.io-client';

// This interface MUST match what QueueItemForClient from API sends
interface QueueItem {
  id: string; // patient's unique ID (from patient_id in DB)
  name: string;
  status: 'waiting' | 'in-progress' | 'completed';
  waitTime: number; // In minutes
  isEmergency: boolean;
  position: number; // Calculated for active items, might be 0/null for completed
  // updatedAt?: string; // Optional: if you want to display completion time
}

interface ApiErrorResponse {
  message: string;
  error?: string;
}

const statusColumns: { title: string; value: QueueItem['status']; itemBgColor: string; itemBorderColor: string; headerTextColor?: string }[] = [
  { title: 'Waiting', value: 'waiting', itemBgColor: 'bg-yellow-50', itemBorderColor: 'border-yellow-400', headerTextColor: 'text-yellow-800' },
  { title: 'In Progress', value: 'in-progress', itemBgColor: 'bg-blue-50', itemBorderColor: 'border-blue-400', headerTextColor: 'text-blue-800'},
  { title: 'Completed', value: 'completed', itemBgColor: 'bg-green-50', itemBorderColor: 'border-green-400', headerTextColor: 'text-green-800' },
];

export default function QueueManagementPage() {
  const [queue, setQueue] = useState<QueueItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [updatingItemId, setUpdatingItemId] = useState<string | null>(null);
  const [socket, setSocket] = useState<Socket | null>(null);

  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';
  const wsUrl = process.env.NEXT_PUBLIC_WS_URL || 'http://localhost:3000';

  const fetchData = useCallback(async () => { // Wrapped in useCallback
    setLoading(true);
    setError(null);
    try {
      const response = await axios.get<QueueItem[]>(`${apiUrl}/api/queue`);
      setQueue(response.data);
      console.log("Fetched initial queue data:", response.data);
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>;
      console.error("Error fetching queue data:", error.response?.data || error.message);
      setError(error.response?.data?.message || 'Failed to load queue data. Please try again.');
    } finally {
      setLoading(false);
    }
  }, [apiUrl]); // Added apiUrl to dependency array of useCallback

  useEffect(() => {
    fetchData();

    const socketInstance = io(wsUrl, {
      path: '/api/socket_io',
      transports: ['websocket'],
      reconnection: true,
    });
    setSocket(socketInstance);

    socketInstance.on('connect', () => console.log('QueueMgmt Socket.IO connected'));
    socketInstance.on('disconnect', (reason) => console.log('QueueMgmt Socket.IO disconnected:', reason));
    socketInstance.on('connect_error', (err) => {
        console.error('QueueMgmt Socket.IO connection error:', err);
        setError(prev => prev ? `${prev}\nSocket connection issue. Live updates may be affected.` : 'Socket connection issue. Live updates may be affected.');
    });

    socketInstance.on('queue-update', (updatedQueue: QueueItem[]) => {
      console.log('QueueMgmt received queue-update from socket:', updatedQueue);
      setQueue(updatedQueue); // Directly set the queue from socket
      setError(null); // Clear previous errors on successful update
    });

    return () => {
      console.log("QueueMgmt: Disconnecting socket instance.");
      socketInstance.disconnect();
      setSocket(null);
    };
  }, [fetchData, wsUrl]); // Added fetchData and wsUrl to dependency array

  const updateStatus = async (itemId: string, newStatus: QueueItem['status']) => {
    setUpdatingItemId(itemId);
    setError(null);
    console.log(`Attempting to update item ${itemId} to status ${newStatus}`);
    try {
      const response = await axios.patch(`${apiUrl}/api/queue/${itemId}`, { status: newStatus });
      console.log(`Successfully sent PATCH for item ${itemId} to status ${newStatus}`, response.data);
      // UI update will come via 'queue-update' socket event from backend
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>;
      const errorMessage = error.response?.data?.message || `Failed to update patient status (Client Error: ${error.message})`;
      console.error(`Error updating patient status for item ${itemId}:`, error.response?.data || error.message, error.response?.status);
      setError(errorMessage);
    } finally {
      setUpdatingItemId(null);
    }
  };

  if (loading && queue.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div className="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-600"></div>
        <p className="ml-3 text-gray-700 text-lg">Loading Queue...</p>
      </div>
    );
  }

  if (error && queue.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 p-6 flex items-center justify-center">
        <div className="bg-red-100 border border-red-400 text-red-700 px-6 py-8 rounded-lg max-w-md w-full text-center shadow-xl">
          <MdErrorOutline className="text-5xl text-red-500 mx-auto mb-4" />
          <h3 className="text-xl font-semibold text-red-800 mb-2">Error Loading Queue</h3>
          <p className="text-red-600 mt-2 mb-6 whitespace-pre-line">{error}</p>
          <button onClick={fetchData} className="flex items-center justify-center gap-2 mt-4 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <MdRefresh className="text-lg"/> Retry
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">
      <header className="mb-6">
        <h1 className="text-2xl sm:text-3xl font-bold text-blue-700">Patient Queue</h1>
        {error && queue.length > 0 && (
             <div className="mt-3 p-3 rounded-md text-sm bg-yellow-100 border border-yellow-300 text-yellow-800 flex items-center gap-2">
                <MdErrorOutline className="text-lg flex-shrink-0" />
                <p className="whitespace-pre-line">{error}</p>
            </div>
        )}
      </header>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        {statusColumns.map((column) => {
          const itemsInColumn = queue
            .filter(item => item.status === column.value)
            .sort((a, b) => {
              if (column.value === 'completed') {
                // For completed, sort by name or ideally a completion timestamp (if available)
                return a.name.localeCompare(b.name); // Placeholder sort
              }
              // Sort active items by emergency then position/entry time
              return (a.isEmergency === b.isEmergency) ? a.position - b.position : (a.isEmergency ? -1 : 1);
            });

          return (
            <div key={column.value} className="bg-white rounded-lg shadow-md flex flex-col">
              <h2 className={`text-base sm:text-lg font-semibold p-3 border-b border-gray-200 text-gray-700`}>
                {column.title} <span className="text-gray-500">({itemsInColumn.length})</span>
              </h2>
              <div className="p-3 space-y-3 flex-grow overflow-y-auto min-h-[200px] max-h-[calc(100vh-220px)]"> {/* Adjusted max-h for better viewport fit */}
                <AnimatePresence>
                  {itemsInColumn.map((item, index) => (
                    <motion.div
                      key={item.id}
                      layout
                      initial={{ opacity: 0, scale: 0.95 }}
                      animate={{ opacity: 1, scale: 1 }}
                      exit={{ opacity: 0, scale: 0.9, transition: { duration: 0.15 } }}
                      transition={{ type: "spring", stiffness: 300, damping: 25, delay: index * 0.02 }}
                      className={`p-3 rounded-md border-l-4 ${column.itemBorderColor} ${column.itemBgColor} shadow-sm hover:shadow-md transition-shadow
                        ${item.isEmergency && item.status !== 'completed' ? '!border-red-500 ring-1 ring-red-400 !bg-red-50' : ''}
                      `}
                    >
                      <div className="flex justify-between items-center">
                        <div className="flex-grow min-w-0">
                          <div className="flex items-center gap-1.5 mb-0.5">
                            <MdPerson className="text-gray-500 text-sm flex-shrink-0" />
                            <span className="font-medium text-gray-800 text-sm truncate" title={item.name}>{item.name}</span>
                            {item.isEmergency && item.status !== 'completed' && <MdEmergency className="text-red-500 text-sm flex-shrink-0" title="Emergency" />}
                          </div>
                          <div className="flex items-center gap-1 text-xs text-gray-500">
                            <MdAccessTime className="flex-shrink-0"/>
                            <span>
                              {item.status === 'completed' ? 'Finished' :
                               item.status === 'in-progress' ? 'Now' :
                               item.waitTime > 0 ? `${item.waitTime} mins` : 'Next'}
                            </span>
                          </div>
                        </div>

                        <div className="flex-shrink-0 ml-2">
                          {item.status === 'waiting' && (
                            <button
                              onClick={() => updateStatus(item.id, 'in-progress')}
                              disabled={updatingItemId === item.id}
                              className="px-2.5 py-1 text-xs font-medium text-blue-700 bg-transparent border border-blue-500 rounded-md hover:bg-blue-50 disabled:opacity-50 transition-colors whitespace-nowrap"
                            >
                              {updatingItemId === item.id ? <div className="w-3 h-3 border-2 border-blue-700 border-t-transparent rounded-full animate-spin"></div> : 'Start'}
                            </button>
                          )}
                          {item.status === 'in-progress' && (
                            <button
                              onClick={() => updateStatus(item.id, 'completed')}
                              disabled={updatingItemId === item.id}
                              className="px-2.5 py-1 text-xs font-medium text-green-700 bg-transparent border border-green-500 rounded-md hover:bg-green-50 disabled:opacity-50 transition-colors whitespace-nowrap"
                            >
                              {updatingItemId === item.id ? <div className="w-3 h-3 border-2 border-green-700 border-t-transparent rounded-full animate-spin"></div> : 'Complete'}
                            </button>
                          )}
                        </div>
                      </div>
                    </motion.div>
                  ))}
                </AnimatePresence>
                {itemsInColumn.length === 0 && (
                  <p className="text-xs text-gray-400 text-center py-4">No patients here.</p>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}