// pages/patient/dashboard.tsx
import React, { useState, useEffect, useCallback } from 'react';
import { useRouter } from 'next/router';
import Link from 'next/link';
import { motion, AnimatePresence } from 'framer-motion';
import axios, { AxiosError } from 'axios';
import { auth } from '../../firebase/firebase'; // Adjust path as necessary
import { onAuthStateChanged, User as FirebaseUser } from 'firebase/auth';
import {
  MdPerson, MdEvent, MdBookOnline, MdAccessTime, MdHourglassEmpty,
  MdNotificationsActive, MdHistory, MdCancel, MdErrorOutline, MdRefresh,
  MdOutlineEditNote, MdInfoOutline // For "no data" messages
} from 'react-icons/md';

// --- Interfaces (Consider moving to a shared types/ file) ---
interface PatientDetails {
  id: number; // MySQL patient.id
  firebase_uid: string;
  name: string;
  email: string;
  // contact?: string; // Add if needed and fetched by /api/patients/me
}

interface Appointment {
  id: number; // appointments.id
  doctorName: string;
  specialty: string;
  appointmentDate: string; // YYYY-MM-DD
  appointmentTime: string; // HH:MM
  status: 'Scheduled' | 'Completed' | 'Cancelled' | string;
  timeSlotId: number; // Needed for cancellation logic to free up the slot
}

interface QueueStatus {
  id: string; // patient_id (as string)
  name: string; // Should match patientDetails.name
  status: 'waiting' | 'in-progress';
  waitTime: number;
  position: number;
  isEmergency: boolean;
}

interface ApiErrorResponse { message: string; error?: string; }

// --- Helper Components ---
const InfoCard: React.FC<{ title: string; value: string | number; icon: React.ElementType; cardClasses: string; iconClasses: string; textClasses: string }> =
  ({ title, value, icon: Icon, cardClasses, iconClasses, textClasses }) => (
  <div className={`p-3 rounded-lg shadow-sm ${cardClasses}`}>
    <div className="flex items-center mb-0.5">
      <Icon className={`text-lg mr-2 ${iconClasses}`} />
      <h3 className={`text-xs font-medium ${textClasses}`}>{title}</h3>
    </div>
    <p className={`text-xl font-bold ${textClasses}`}>{value}</p>
  </div>
);

const AppointmentCard: React.FC<{ appointment: Appointment; onCancel: (appointmentId: number, timeSlotId: number) => Promise<void>; isUpcoming: boolean; isCancellingId: number | null }> =
  ({ appointment, onCancel, isUpcoming, isCancellingId }) => (
  <motion.div
    layout
    initial={{ opacity: 0, y: 10 }}
    animate={{ opacity: 1, y: 0 }}
    exit={{ opacity: 0, y: -10, transition: {duration: 0.2} }}
    className="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200"
  >
    <div className="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
      <div className="flex-grow">
        <p className="text-xs text-gray-500">
          {new Date(appointment.appointmentDate + 'T' + appointment.appointmentTime).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}
        </p>
        <p className="text-lg font-semibold text-blue-600">
          {new Date(appointment.appointmentDate + 'T' + appointment.appointmentTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
        </p>
        <p className="text-sm text-gray-800">Dr. {appointment.doctorName}</p>
        <p className="text-xs text-gray-500">{appointment.specialty}</p>
      </div>
      <div className="mt-2 sm:mt-0 flex flex-col items-start sm:items-end gap-2">
        <span className={`px-2.5 py-1 text-xs font-semibold rounded-full leading-tight ${
          appointment.status === 'Scheduled' ? 'bg-blue-100 text-blue-700' :
          appointment.status === 'Completed' ? 'bg-green-100 text-green-700' :
          appointment.status === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'
        }`}>
          {appointment.status}
        </span>
        {isUpcoming && appointment.status === 'Scheduled' && (
          <button
            onClick={() => onCancel(appointment.id, appointment.timeSlotId)}
            disabled={isCancellingId === appointment.id}
            className="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded flex items-center gap-1 disabled:opacity-60 disabled:hover:bg-transparent"
          >
            {isCancellingId === appointment.id ? (
                <div className="w-3 h-3 border-2 border-red-500 border-t-transparent rounded-full animate-spin"></div>
            ) : (
                <MdCancel />
            )}
            Cancel
          </button>
        )}
      </div>
    </div>
  </motion.div>
);


// --- Main Patient Dashboard Component ---
export default function PatientDashboardPage() {
  const router = useRouter();
  const [firebaseUser, setFirebaseUser] = useState<FirebaseUser | null>(null);
  const [patientDetails, setPatientDetails] = useState<PatientDetails | null>(null);
  const [upcomingAppointments, setUpcomingAppointments] = useState<Appointment[]>([]);
  const [pastAppointments, setPastAppointments] = useState<Appointment[]>([]);
  const [currentQueueStatus, setCurrentQueueStatus] = useState<QueueStatus | null>(null);

  const [loading, setLoading] = useState(true); // General loading for initial data
  const [error, setError] = useState<string | null>(null);
  const [isCancellingId, setIsCancellingId] = useState<number | null>(null);

  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  const loadDashboardData = useCallback(async (user: FirebaseUser) => {
    setLoading(true); setError(null);
    try {
      const token = await user.getIdToken();
      const authHeader = { headers: { Authorization: `Bearer ${token}` } };

      const [patientDetailsRes, appointmentsRes, queueStatusRes] = await Promise.allSettled([
        axios.get<PatientDetails>(`${apiUrl}/api/patients/me`, authHeader),
        axios.get<Appointment[]>(`${apiUrl}/api/appointments/my-appointments`, authHeader),
        axios.get<QueueStatus | null>(`${apiUrl}/api/queue/my-status`, authHeader) // API might return null directly
      ]);

      if (patientDetailsRes.status === 'fulfilled') {
        setPatientDetails(patientDetailsRes.value.data);
      } else {
        throw new Error(patientDetailsRes.reason?.response?.data?.message || patientDetailsRes.reason?.message || "Failed to load patient details.");
      }

      if (appointmentsRes.status === 'fulfilled') {
        const allAppointments = appointmentsRes.value.data;
        const today = new Date();
        today.setHours(0,0,0,0); // Set to start of today for accurate date comparison

        const upcoming = allAppointments.filter(appt =>
            new Date(appt.appointmentDate) >= today && appt.status === 'Scheduled'
        );
        const past = allAppointments.filter(appt =>
            new Date(appt.appointmentDate) < today || (appt.status !== 'Scheduled')
        );
        setUpcomingAppointments(upcoming.sort((a,b) => new Date(a.appointmentDate + 'T' + a.appointmentTime).getTime() - new Date(b.appointmentDate + 'T' + b.appointmentTime).getTime()));
        setPastAppointments(past.sort((a,b) => new Date(b.appointmentDate + 'T' + b.appointmentTime).getTime() - new Date(a.appointmentDate + 'T' + a.appointmentTime).getTime()));
      } else {
        console.warn("Failed to load appointments:", appointmentsRes.reason?.response?.data?.message || appointmentsRes.reason?.message);
        // Decide if this is a critical error for the dashboard
      }

      if (queueStatusRes.status === 'fulfilled') {
        setCurrentQueueStatus(queueStatusRes.value.data); // API returns QueueStatus or null
      } else {
        console.info("Patient not in queue or error fetching queue status:", queueStatusRes.reason?.response?.data?.message || queueStatusRes.reason?.message);
        setCurrentQueueStatus(null);
      }

    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse> | Error;
      let errorMessage = "Could not load your dashboard data. Please try again.";
      if (axios.isAxiosError(error) && error.response?.data?.message) {
        errorMessage = error.response.data.message;
        if (error.response?.status === 401 || error.response?.status === 403) {
          router.push('/login?redirect=/patient/dashboard');
          return; // Stop further processing if redirecting
        }
      } else if (error instanceof Error) {
        errorMessage = error.message;
      }
      console.error("Failed to load patient dashboard:", error);
      setError(errorMessage);
    } finally {
      setLoading(false);
    }
  }, [apiUrl, router]);

  useEffect(() => {
    const unsubscribe = onAuthStateChanged(auth, (user) => {
      if (user) {
        setFirebaseUser(user);
        loadDashboardData(user);
      } else {
        setFirebaseUser(null);
        setPatientDetails(null);
        setLoading(false); // Stop loading if no user
        router.push('/login?redirect=/patient/dashboard');
      }
    });
    return () => unsubscribe();
  }, [router, loadDashboardData]);

  const handleCancelAppointment = async (appointmentId: number, timeSlotId: number) => {
    if (!firebaseUser || !confirm("Are you sure you want to cancel this appointment? This action cannot be undone.")) return;
    setIsCancellingId(appointmentId); setError(null);
    try {
      const token = await firebaseUser.getIdToken();
      await axios.patch(`${apiUrl}/api/appointments/${appointmentId}/cancel`,
        { timeSlotId }, // Send timeSlotId in the body for the backend to use
        { headers: { Authorization: `Bearer ${token}` } }
      );
      // Optimistically update UI or refresh data
      // For robustness, refresh all data:
      loadDashboardData(firebaseUser);
      // alert("Appointment cancelled successfully."); // Or use a more subtle notification
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>;
      console.error("Failed to cancel appointment:", error.response?.data || error.message);
      setError(error.response?.data?.message || "Could not cancel appointment. Please try again.");
    } finally {
      setIsCancellingId(null);
    }
  };

  // --- Render Logic ---
  if (loading || !firebaseUser) {
    return ( /* ... Main Loading Spinner ... */
      <div className="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4">
        <div className="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-600"></div>
        <p className="mt-3 text-gray-700">Loading Your Dashboard...</p>
      </div>
    );
  }

  // If there was an error fetching patientDetails specifically, it's a critical error.
  if (!patientDetails && error) {
     return ( /* ... Critical Error Display ... */
      <div className="min-h-screen bg-gray-100 p-6 flex items-center justify-center">
        <div className="bg-red-100 border border-red-400 text-red-700 px-6 py-8 rounded-lg max-w-md w-full text-center shadow-xl">
          <MdErrorOutline className="text-5xl text-red-500 mx-auto mb-4" />
          <h3 className="text-xl font-semibold text-red-800 mb-2">Access Denied or Error</h3>
          <p className="text-red-600 mt-2 mb-6 whitespace-pre-line">{error}</p>
          <button onClick={() => loadDashboardData(firebaseUser)} className="flex items-center justify-center gap-2 mt-4 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <MdRefresh className="text-lg"/> Retry
          </button>
        </div>
      </div>
    );
  }
  if (!patientDetails && !loading) { // Should not happen if auth logic is correct but as a fallback
      return <div className="min-h-screen bg-gray-100 flex items-center justify-center p-4"><p>Could not load patient data.</p></div>
  }


  return (
    <div className="min-h-screen bg-gray-100 py-8 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto space-y-8">
        <header className="text-left">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-800">Welcome, <span className="text-blue-600">{patientDetails?.name}!</span></h1>
          <p className="text-sm text-gray-500 mt-1">Here's an overview of your activities.</p>
        </header>

        {error && ( /* General error display if not critical */
            <motion.div initial={{opacity:0}} animate={{opacity:1}} className="p-3 mb-6 rounded-md text-sm bg-red-50 border border-red-200 text-red-700 flex items-center gap-2">
                 <MdErrorOutline className="text-lg flex-shrink-0" />
                <p className="flex-1">{error}</p>
                 <button onClick={() => firebaseUser && loadDashboardData(firebaseUser)} className="ml-auto p-1 text-red-500 hover:text-red-700">
                    <MdRefresh size={18}/>
                </button>
            </motion.div>
        )}

        {/* Current Queue Status Panel */}
        {currentQueueStatus && (
          <motion.section initial={{ opacity:0, y:10 }} animate={{opacity:1, y:0}} transition={{delay:0.1}}
            className="p-4 sm:p-5 bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 text-white rounded-xl shadow-lg"
          >
            <div className="flex items-center gap-2 mb-3">
                <MdNotificationsActive className="text-2xl sm:text-3xl"/>
                <h2 className="text-lg sm:text-xl font-semibold">You're in the Queue!</h2>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
              <InfoCard title="Position" value={`#${currentQueueStatus.position}`} icon={MdHourglassEmpty} cardClasses="bg-white/10 backdrop-blur-sm" iconClasses="text-yellow-300" textClasses="text-white" />
              <InfoCard title="Status" value={currentQueueStatus.status.charAt(0).toUpperCase() + currentQueueStatus.status.slice(1)} icon={MdPerson} cardClasses="bg-white/10 backdrop-blur-sm" iconClasses="text-green-300" textClasses="text-white" />
              <InfoCard title="Est. Wait" value={`${currentQueueStatus.waitTime} min`} icon={MdAccessTime} cardClasses="bg-white/10 backdrop-blur-sm" iconClasses="text-pink-300" textClasses="text-white" />
            </div>
          </motion.section>
        )}

        {/* Quick Actions */}
        <section>
          <Link href="/patient/appointment"
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 transition-all text-sm sm:text-base"
            >
              <MdBookOnline size={18} /> Book New Appointment
          </Link>
          {/* Add more quick actions like "View Profile" */}
           <Link href="/patient/profile"
              className="ml-3 inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-700 font-semibold rounded-lg shadow-md hover:bg-gray-300 focus:ring-2 focus:ring-gray-300 transition-all text-sm sm:text-base"
            >
              <MdOutlineEditNote size={18} /> My Profile
          </Link>
        </section>

        {/* Upcoming Appointments */}
        <section className="bg-white p-4 sm:p-6 rounded-lg shadow-md">
          <h2 className="text-lg sm:text-xl font-semibold text-gray-700 mb-4 flex items-center gap-2"><MdEvent className="text-blue-500"/> Upcoming Appointments</h2>
          {upcomingAppointments.length > 0 ? (
            <AnimatePresence>
                <div className="space-y-3">
                {upcomingAppointments.map(appt => <AppointmentCard key={`upcoming-${appt.id}`} appointment={appt} onCancel={handleCancelAppointment} isUpcoming={true} isCancellingId={isCancellingId} />)}
                </div>
            </AnimatePresence>
          ) : (
            <div className="text-center text-gray-500 py-6 px-4 border-2 border-dashed border-gray-200 rounded-md">
                <MdInfoOutline className="text-3xl text-gray-400 mx-auto mb-2"/>
                <p>You have no upcoming appointments.</p>
            </div>
          )}
        </section>

        {/* Past Appointments */}
        <section className="bg-white p-4 sm:p-6 rounded-lg shadow-md">
          <h2 className="text-lg sm:text-xl font-semibold text-gray-700 mb-4 flex items-center gap-2"><MdHistory className="text-blue-500"/> Appointment History</h2>
          {pastAppointments.length > 0 ? (
            <AnimatePresence>
                <div className="space-y-3">
                {pastAppointments.map(appt => <AppointmentCard key={`past-${appt.id}`} appointment={appt} onCancel={handleCancelAppointment} isUpcoming={false} isCancellingId={isCancellingId}/>)}
                </div>
            </AnimatePresence>
          ) : (
            <div className="text-center text-gray-500 py-6 px-4 border-2 border-dashed border-gray-200 rounded-md">
                 <MdInfoOutline className="text-3xl text-gray-400 mx-auto mb-2"/>
                <p>No past appointment records found.</p>
            </div>
          )}
        </section>
      </div>
    </div>
  );
}