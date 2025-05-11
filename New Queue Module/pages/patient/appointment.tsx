// pages/patient/appointment.tsx
import React, { useState, useEffect, FormEvent, useCallback } from 'react';
import { useRouter } from 'next/router';
import Link from 'next/link';
import { motion } from 'framer-motion';
import axios, { AxiosError } from 'axios';
import { auth } from '../../firebase/firebase'; // Adjust path as necessary
import { onAuthStateChanged, User as FirebaseUser } from 'firebase/auth';
import {
  MdAccessTime, MdCalendarToday, MdLocalHospital, MdError, MdCheckCircle,
  MdCategory, MdPerson, MdRefresh
} from 'react-icons/md';

// --- Interfaces ---
interface Doctor { _id: string; name: string; specialty: string; }
interface TimeSlot { _id: string; time: string; available: boolean; }
interface Specialty { name: string; }
interface LoggedInPatientInfo { id: string; name: string; } // MySQL patient.id (as string) and name
interface ApiErrorResponse { message: string; error?: string; }

export default function PatientSelfAppointmentPage() {
  const router = useRouter();
  const [firebaseUser, setFirebaseUser] = useState<FirebaseUser | null>(null);
  const [loggedInPatientInfo, setLoggedInPatientInfo] = useState<LoggedInPatientInfo | null>(null);

  const [specialties, setSpecialties] = useState<Specialty[]>([]);
  const [doctors, setDoctors] = useState<Doctor[]>([]);
  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);

  const [selectedSpecialty, setSelectedSpecialty] = useState('');
  const [selectedDoctor, setSelectedDoctor] = useState('');
  const [selectedDate, setSelectedDate] = useState('');
  const [selectedTimeSlotId, setSelectedTimeSlotId] = useState('');
  const [selectedTimeDisplay, setSelectedTimeDisplay] = useState('');

  const [loadingAuthAndPatient, setLoadingAuthAndPatient] = useState(true);
  const [loadingSpecialties, setLoadingSpecialties] = useState(false);
  const [loadingDoctors, setLoadingDoctors] = useState(false);
  const [loadingSlots, setLoadingSlots] = useState(false);
  const [isBooking, setIsBooking] = useState(false);

  const [error, setError] = useState<string | null>(null);
  const [bookingStatus, setBookingStatus] = useState<'success' | 'error' | null>(null);
  const [bookingMessage, setBookingMessage] = useState<string | null>(null);

  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  const fetchPatientAndSpecialties = useCallback(async (user: FirebaseUser) => {
    setLoadingAuthAndPatient(true);
    setLoadingSpecialties(true);
    setError(null);
    let patientInfoLoadedSuccessfully = false;
    try {
      const token = await user.getIdToken();
      const patientRes = await axios.get<{ id: number; name: string }>(`${apiUrl}/api/patients/me`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setLoggedInPatientInfo({ id: String(patientRes.data.id), name: patientRes.data.name });
      patientInfoLoadedSuccessfully = true;

      const specialtiesRes = await axios.get<Specialty[]>(`${apiUrl}/api/specialties`);
      setSpecialties(specialtiesRes.data);

    } catch (e) {
      const err = e as AxiosError<ApiErrorResponse>;
      console.error("Error fetching initial data (patient/specialties):", err.response?.data?.message || err.message);
      const specificError = err.response?.data?.message || "Could not load necessary data.";
      setError(patientInfoLoadedSuccessfully ? `Failed to load specialties: ${specificError}` : `Failed to load your patient details: ${specificError}. Please ensure your patient profile is complete or try logging in again.`);
      if (!patientInfoLoadedSuccessfully && (err.response?.status === 401 || err.response?.status === 403 || err.response?.status === 404)) {
        // If patient details specifically fail with auth/not found, redirect may be appropriate
        // router.push('/login?redirect=/patient/appointment&error=patient_profile_missing');
      }
    } finally {
      setLoadingAuthAndPatient(false);
      setLoadingSpecialties(false);
    }
  }, [apiUrl, router]);


  useEffect(() => {
    const unsubscribe = onAuthStateChanged(auth, (user) => {
      setFirebaseUser(user); // Set firebaseUser immediately
      if (user) {
        fetchPatientAndSpecialties(user);
      } else {
        setLoadingAuthAndPatient(false);
        router.push('/login?redirect=/patient/appointment');
      }
    });
    return () => unsubscribe();
  }, [router, fetchPatientAndSpecialties]); // fetchPatientAndSpecialties is stable due to useCallback


  useEffect(() => {
    if (selectedSpecialty && loggedInPatientInfo) {
      const fetchDoctorsBySpecialty = async () => {
        setLoadingDoctors(true); setError(null); setDoctors([]); setSelectedDoctor(''); setTimeSlots([]); setSelectedTimeSlotId(''); setSelectedTimeDisplay('');
        try {
          const doctorsRes = await axios.get<Doctor[]>(`${apiUrl}/api/doctors?specialty=${encodeURIComponent(selectedSpecialty)}`);
          setDoctors(doctorsRes.data);
          if (doctorsRes.data.length === 0 && !loadingSpecialties) setError(`No doctors found for the specialty: ${selectedSpecialty}.`);
        } catch (err) {
          const error = err as AxiosError<ApiErrorResponse>;
          console.error('Failed to load doctors for specialty:', error.response?.data?.message || error.message);
          setError(error.response?.data?.message || 'Failed to load doctors for the selected specialty.');
        } finally { setLoadingDoctors(false); }
      };
      fetchDoctorsBySpecialty();
    } else { setDoctors([]); setSelectedDoctor(''); }
  }, [selectedSpecialty, apiUrl, loggedInPatientInfo, loadingSpecialties]);


  useEffect(() => {
    if (selectedDoctor && selectedDate && loggedInPatientInfo) {
      const fetchTimeSlots = async () => {
        setLoadingSlots(true); setError(null); setTimeSlots([]); setSelectedTimeSlotId(''); setSelectedTimeDisplay('');
        try {
          const slotsRes = await axios.get<TimeSlot[]>(`${apiUrl}/api/doctors/${selectedDoctor}/timeslots?date=${selectedDate}`);
          setTimeSlots(slotsRes.data);
          if (slotsRes.data.length === 0 && !loadingDoctors) setError("No available time slots for this doctor on the selected date.");
        } catch (err) {
          const error = err as AxiosError<ApiErrorResponse>;
          console.error('Failed to load time slots:', error.response?.data?.message || error.message);
          setError(error.response?.data?.message || 'Failed to load available time slots.');
        } finally { setLoadingSlots(false); }
      };
      fetchTimeSlots();
    } else { setTimeSlots([]); }
  }, [selectedDoctor, selectedDate, apiUrl, loggedInPatientInfo, loadingDoctors]);


  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!loggedInPatientInfo?.id) {
      setError("Your patient information is not available. Please ensure you are logged in correctly and your profile is set up.");
      return;
    }
    if (!selectedSpecialty || !selectedDoctor || !selectedDate || !selectedTimeSlotId) {
      setError('Please complete all selections: specialty, doctor, date, and time slot.');
      return;
    }

    setIsBooking(true); setError(null); setBookingStatus(null); setBookingMessage(null);
    try {
      const token = await firebaseUser?.getIdToken();
      if (!token) {
        setError("Authentication error. Your session might have expired. Please log in again.");
        setIsBooking(false);
        // router.push('/login?redirect=/patient/appointment'); // Optionally force re-login
        return;
      }

      const response = await axios.post(`${apiUrl}/api/appointments`, {
        patientId: loggedInPatientInfo.id,
        doctorId: selectedDoctor,
        date: selectedDate,
        timeSlotId: selectedTimeSlotId,
      }, { headers: { Authorization: `Bearer ${token}` } });

      setBookingStatus('success');
      setBookingMessage(response.data.message || 'Appointment booked successfully!');
      // Reset parts of the form for another potential booking, or navigate away
      setSelectedDoctor('');
      setSelectedDate('');
      setSelectedTimeSlotId('');
      setSelectedTimeDisplay('');
      setTimeSlots([]);
      // Consider if setSelectedSpecialty('') is desired or if they might book another in same specialty.
      // For now, keeping specialty selected.

      // Optionally redirect after a delay
      // setTimeout(() => router.push('/patient/dashboard?booking=success'), 2500);
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>;
      console.error('Appointment booking failed:', error.response?.data || error.message, error.response?.status);
      setBookingStatus('error');
      const apiMsg = error.response?.data?.message;
      setBookingMessage(apiMsg || 'Failed to book appointment. The slot may have just been taken, or another error occurred.');
      setError(apiMsg || 'Booking failed. Please try again or contact support.');
    } finally {
      setIsBooking(false);
    }
  };

  const handleTimeSlotSelect = (slot: TimeSlot) => {
    setSelectedTimeSlotId(slot._id);
    setSelectedTimeDisplay(slot.time);
    setError(null); // Clear general errors when a slot is picked
    setBookingStatus(null); // Clear previous booking status
  };

  const minDate = new Date();
  minDate.setDate(minDate.getDate() + 1); // Booking from tomorrow

  if (loadingAuthAndPatient) {
    return (
      <div className="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4">
        <div className="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-600"></div>
        <p className="mt-3 text-gray-700">Loading your session...</p>
      </div>
    );
  }

  if (!firebaseUser || (!loggedInPatientInfo && !loadingAuthAndPatient)) {
    // This condition implies auth check is done, but user/patientInfo is still null.
    // The onAuthStateChanged should have redirected, but this is a fallback UI.
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center p-4 text-center">
        <div className="bg-white p-6 rounded-lg shadow-md max-w-md">
          <MdError className="text-4xl text-red-500 mx-auto mb-3"/>
          <p className="text-gray-700 mb-4">
            {error || "You need to be logged in as a patient to book an appointment. Your patient details could not be loaded."}
          </p>
          <button onClick={() => router.push('/login?redirect=/patient/appointment')} className="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Go to Login
          </button>
        </div>
      </div>
    );
  }


  return (
    <div className="min-h-screen bg-gray-100 p-4 md:p-8 flex justify-center items-start">
      <motion.form
        onSubmit={handleSubmit}
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="max-w-lg w-full bg-white rounded-xl p-6 sm:p-8 shadow-xl"
      >
        <div className="flex justify-between items-center mb-8">
            <h1 className="text-2xl sm:text-3xl font-bold text-blue-700 flex items-center gap-2">
            <MdLocalHospital className="text-blue-600 text-3xl" />
            Book Appointment
            </h1>
            <Link href="/patient/dashboard" className="text-sm text-blue-600 hover:underline">
                ← Back to Dashboard
            </Link>
        </div>

        {/* Display error related to initial data loading if loggedInPatientInfo is set but specialties failed, etc. */}
        {error && !bookingStatus && loggedInPatientInfo && (
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1}} className="p-3 mb-6 rounded-md text-sm bg-red-100 border border-red-300 text-red-700 flex items-start gap-2">
                <MdError className="text-lg mt-px flex-shrink-0" />
                <p className="flex-1 whitespace-pre-line">{error}</p>
                <button onClick={() => firebaseUser && fetchPatientAndSpecialties(firebaseUser)} className="ml-auto p-1 text-red-500 hover:text-red-700">
                    <MdRefresh size={18}/>
                </button>
            </motion.div>
        )}
        {/* Booking Status Message */}
        {bookingStatus && bookingMessage && (
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1}} className={`p-3 mb-6 rounded-md text-sm flex items-start gap-2 ${bookingStatus === 'success' ? 'bg-green-100 border border-green-300 text-green-700' : 'bg-red-100 border border-red-300 text-red-700'}`}>
                {bookingStatus === 'success' ? <MdCheckCircle className="text-lg mt-px flex-shrink-0" /> : <MdError className="text-lg mt-px flex-shrink-0" />}
                <div className="flex-1">
                    <p className="whitespace-pre-line font-medium">{bookingMessage}</p>
                    {bookingStatus === 'success' && loggedInPatientInfo && selectedDoctor && selectedDate && selectedTimeDisplay && (
                        <p className="text-xs mt-1">
                            For: <span className="font-semibold">{loggedInPatientInfo.name}</span> <br/>
                            With: <span className="font-semibold">{doctors.find(d => d._id === selectedDoctor)?.name || 'Selected Doctor'}</span> <br/>
                            On: <span className="font-semibold">{new Date(selectedDate + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })} at {selectedTimeDisplay}</span>
                        </p>
                    )}
                </div>
            </motion.div>
        )}

        {loggedInPatientInfo && (
            <div className="mb-6 p-3 bg-gray-50 border border-gray-200 rounded-lg flex items-center gap-3">
                <MdPerson className="text-xl text-gray-500"/>
                <div>
                    <p className="text-xs text-gray-500">Booking for:</p>
                    <p className="font-semibold text-gray-800">{loggedInPatientInfo.name}</p>
                </div>
            </div>
        )}

        {/* Specialty Selection */}
        <div className="mb-6">
          <label htmlFor="specialty" className="block text-sm font-medium text-gray-700 mb-1">
            Select Specialty {loadingSpecialties && <span className="text-xs text-gray-400">(Loading...)</span>}
          </label>
          <div className="relative">
            <select
              id="specialty"
              value={selectedSpecialty}
              onChange={(e) => { setSelectedSpecialty(e.target.value); setError(null); setBookingStatus(null);}}
              required
              disabled={loadingSpecialties || !loggedInPatientInfo} // Disabled if initial patient info not loaded
              className="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10 disabled:bg-gray-100"
            >
              <option value="" disabled>-- Choose a specialty --</option>
              {specialties.map(spec => ( <option key={spec.name} value={spec.name}> {spec.name} </option> ))}
            </select>
            <MdCategory className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" />
          </div>
        </div>

        {/* Doctor Selection */}
        <div className="mb-6">
          <label htmlFor="doctor" className="block text-sm font-medium text-gray-700 mb-1">
            Select Doctor {loadingDoctors && selectedSpecialty && <span className="text-xs text-gray-400">(Loading...)</span>}
          </label>
          <select
            id="doctor"
            value={selectedDoctor}
            onChange={(e) => { setSelectedDoctor(e.target.value); setError(null); setBookingStatus(null);}}
            required
            disabled={!selectedSpecialty || loadingDoctors || doctors.length === 0 || !loggedInPatientInfo}
            className="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
          >
            <option value="" disabled>
              {!selectedSpecialty ? '-- Select specialty first --' :
               loadingDoctors ? 'Loading doctors...' :
               doctors.length === 0 ? 'No doctors available' :
               '-- Choose a doctor --'}
            </option>
            {doctors.map(doctor => ( <option key={doctor._id} value={doctor._id}> {doctor.name} </option> ))}
          </select>
        </div>

        {/* Date Picker */}
        <div className="mb-6">
          <label htmlFor="date" className="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
          <div className="relative">
            <input
              id="date" type="date" value={selectedDate}
              onChange={(e) => { setSelectedDate(e.target.value); setError(null); setBookingStatus(null);}}
              required
              className="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10 disabled:bg-gray-100"
              min={minDate.toISOString().split('T')[0]}
              disabled={!selectedDoctor || !loggedInPatientInfo}
            />
            <MdCalendarToday className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" />
          </div>
        </div>

        {/* Time Slots - Conditionally render based on selections and patient info */}
        {loggedInPatientInfo && selectedDoctor && selectedDate && (
          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-1">
              <MdAccessTime /> Available Times {loadingSlots && <span className="text-xs text-gray-400">(Loading...)</span>}
            </label>
            {loadingSlots ? ( <div className="flex justify-center items-center h-20"><div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div></div> )
             : timeSlots.length > 0 ? (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    {timeSlots.map(slot => (
                        <button key={slot._id} type="button" onClick={() => handleTimeSlotSelect(slot)} disabled={!slot.available || isBooking}
                        className={`p-2.5 rounded-lg text-sm transition-all duration-150 ease-in-out shadow-sm ${selectedTimeSlotId === slot._id ? 'bg-blue-600 text-white ring-2 ring-blue-300 scale-105' : slot.available ? 'bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 border border-gray-200' : 'bg-gray-200 text-gray-400 cursor-not-allowed opacity-70' }`}
                        > {slot.time} </button>
                    ))}
                </div>
            ) : ( !error && <p className="text-sm text-gray-500">No time slots available for this selection.</p> )}
          </div>
        )}

        <button
          type="submit"
          className="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition"
          disabled={!loggedInPatientInfo || !selectedSpecialty || !selectedDoctor || !selectedDate || !selectedTimeSlotId || isBooking || loadingSlots || loadingDoctors || loadingSpecialties}
        >
          {isBooking ? (
            <>
              <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Booking...
            </>
          ) : (
            'Confirm Appointment'
          )}
        </button>
      </motion.form>
    </div>
  );
}