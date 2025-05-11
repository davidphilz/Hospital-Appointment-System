// pages/patient-appointment.tsx
import { useState, useEffect, FormEvent } from 'react';
import { motion } from 'framer-motion';
import { MdAccessTime, MdCalendarToday, MdLocalHospital, MdError, MdCheckCircle, MdPersonPin } from 'react-icons/md'; // Added MdPersonPin
import axios, { AxiosError } from 'axios';
import { useRouter } from 'next/router';

interface Doctor {
  _id: string;
  name: string;
  specialty: string;
}

interface TimeSlot {
  _id: string;
  time: string;
  available: boolean;
}

// Interface for the patient list dropdown
interface PatientListItem {
  _id: string; // patient.id
  name: string;
}

interface ApiErrorResponse {
  message: string;
  error?: string;
}

export default function AppointmentScheduler() {
  const router = useRouter();
  const [doctors, setDoctors] = useState<Doctor[]>([]);
  const [patients, setPatients] = useState<PatientListItem[]>([]); // <-- NEW: State for patients
  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);

  const [selectedPatient, setSelectedPatient] = useState(''); // <-- NEW: State for selected patient ID
  const [selectedDoctor, setSelectedDoctor] = useState('');
  const [selectedDate, setSelectedDate] = useState('');
  const [selectedTimeSlotId, setSelectedTimeSlotId] = useState('');
  const [selectedTimeDisplay, setSelectedTimeDisplay] = useState('');

  const [loadingDoctors, setLoadingDoctors] = useState(true);
  const [loadingPatients, setLoadingPatients] = useState(true); // <-- NEW: Loading state for patients
  const [loadingSlots, setLoadingSlots] = useState(false);
  const [isBooking, setIsBooking] = useState(false);

  const [error, setError] = useState<string | null>(null);
  const [bookingStatus, setBookingStatus] = useState<'success' | 'error' | null>(null);
  const [bookingMessage, setBookingMessage] = useState<string | null>(null);

  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  // Fetch doctors and patients on component mount
  useEffect(() => {
    const fetchData = async () => {
      setLoadingDoctors(true);
      setLoadingPatients(true);
      setError(null);
      try {
        const [doctorsRes, patientsRes] = await Promise.all([
          axios.get<Doctor[]>(`${apiUrl}/api/doctors`),
          axios.get<PatientListItem[]>(`${apiUrl}/api/patients/list`) // <-- NEW: Fetch patients
        ]);
        setDoctors(doctorsRes.data);
        setPatients(patientsRes.data);
      } catch (err) {
        const error = err as AxiosError<ApiErrorResponse>;
        console.error('Failed to load initial data:', error.response?.data || error.message);
        setError(error.response?.data?.message || 'Failed to load necessary data. Please try again later.');
      } finally {
        setLoadingDoctors(false);
        setLoadingPatients(false);
      }
    };
    fetchData();
  }, [apiUrl]);

  // Fetch time slots (useEffect remains the same)
  useEffect(() => {
    if (selectedDoctor && selectedDate) {
      // ... (existing time slot fetching logic - no changes needed here)
      const fetchTimeSlots = async () => {
        setLoadingSlots(true);
        setError(null);
        setTimeSlots([]);
        setSelectedTimeSlotId('');
        setSelectedTimeDisplay('');
        try {
          const slotsRes = await axios.get<TimeSlot[]>(`${apiUrl}/api/doctors/${selectedDoctor}/timeslots?date=${selectedDate}`);
          setTimeSlots(slotsRes.data);
          if (slotsRes.data.length === 0 && !loadingDoctors && !loadingPatients) { // Only show if other data loaded
            setError("No available slots for this doctor on the selected date.");
          }
        } catch (err) {
          // ... (error handling)
          const error = err as AxiosError<ApiErrorResponse>;
          console.error('Failed to load time slots:', error.response?.data || error.message);
          setError(error.response?.data?.message || 'Failed to load available time slots.');
        } finally {
          setLoadingSlots(false);
        }
      };
      fetchTimeSlots();
    } else {
      setTimeSlots([]);
    }
  }, [selectedDoctor, selectedDate, apiUrl, loadingDoctors, loadingPatients]);

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!selectedPatient || !selectedDoctor || !selectedDate || !selectedTimeSlotId) { // <-- ADDED: selectedPatient check
      setError('Please select a patient, doctor, date, and time slot.');
      return;
    }

    setIsBooking(true);
    setError(null);
    setBookingStatus(null);
    setBookingMessage(null);

    try {
      const response = await axios.post(`${apiUrl}/api/appointments`, {
        patientId: selectedPatient, // <-- NEW: Send selectedPatient ID
        doctorId: selectedDoctor,
        date: selectedDate,
        timeSlotId: selectedTimeSlotId,
      });
      setBookingStatus('success');
      setBookingMessage(response.data.message || 'Appointment booked successfully!');
      // Reset patient selection as well
      // setSelectedPatient(''); // Optional: reset patient or keep for next booking
      setSelectedTimeSlotId('');
      setSelectedTimeDisplay('');
      const currentSlots = timeSlots.map(slot => slot._id === selectedTimeSlotId ? {...slot, available: false} : slot);
      setTimeSlots(currentSlots);
    } catch (err) {
      // ... (existing error handling)
      const error = err as AxiosError<ApiErrorResponse>;
      console.error('Appointment booking failed:', error.response?.data || error.message);
      setBookingStatus('error');
      setBookingMessage(error.response?.data?.message || 'Failed to book appointment. The slot may have just been taken, or an error occurred.');
      setError(error.response?.data?.message || 'Booking failed. Please try again.');
    } finally {
      setIsBooking(false);
    }
  };

  const handleTimeSlotSelect = (slot: TimeSlot) => {
    setSelectedTimeSlotId(slot._id);
    setSelectedTimeDisplay(slot.time);
    setError(null);
    setBookingStatus(null);
  };

  const minDate = new Date();
  minDate.setDate(minDate.getDate() + 1);

  if (loadingDoctors || loadingPatients) { // Check both loading states
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div className="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-600"></div>
        <p className="ml-3 text-gray-700">Loading appointment data...</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 p-4 md:p-8 flex justify-center items-start">
      <motion.form
        // ... (form props remain the same)
        onSubmit={handleSubmit}
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="max-w-lg w-full bg-white rounded-xl p-6 sm:p-8 shadow-xl"
      >
        {/* ... (title, error messages, booking status messages remain the same) ... */}
         <h1 className="text-2xl sm:text-3xl font-bold text-blue-700 mb-8 flex items-center gap-2 justify-center">
          <MdLocalHospital className="text-blue-600 text-3xl" />
          Book Appointment
        </h1>

        {error && !bookingStatus && (
          <motion.div /* ... error display ... */ className="p-3 mb-6 rounded-lg text-sm bg-red-100 border border-red-300 text-red-700 flex items-start gap-2">
            <MdError className="text-lg mt-px flex-shrink-0" />
            <p className="flex-1 whitespace-pre-line">{error}</p>
          </motion.div>
        )}
        {bookingStatus && bookingMessage && (
          <motion.div /* ... booking status display ... */  className={`p-3 mb-6 rounded-lg text-sm flex items-start gap-2 ${bookingStatus === 'success' ? 'bg-green-100 border border-green-300 text-green-700' : 'bg-red-100 border border-red-300 text-red-700'}`}>
            {bookingStatus === 'success' ? <MdCheckCircle className="text-lg mt-px flex-shrink-0" /> : <MdError className="text-lg mt-px flex-shrink-0" />}
            <p className="flex-1 whitespace-pre-line">{bookingMessage}</p>
            {bookingStatus === 'success' && selectedPatient && selectedDoctor && selectedDate && selectedTimeDisplay && (
                 <p className="text-sm mt-1">
                    For: {patients.find(p => p._id === selectedPatient)?.name} <br/>
                    With: {doctors.find(d => d._id === selectedDoctor)?.name} <br/>
                    On: {new Date(selectedDate + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })} at {selectedTimeDisplay}
                 </p>
            )}
          </motion.div>
        )}

        {/* Patient Selection - NEW FIELD */}
        <div className="mb-6">
          <label htmlFor="patient" className="block text-sm font-medium text-gray-700 mb-1">Select Patient</label>
          <div className="relative">
            <select
              id="patient"
              value={selectedPatient}
              onChange={(e) => {
                setSelectedPatient(e.target.value);
                setError(null);
                setBookingStatus(null);
              }}
              required
              className="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10"
            >
              <option value="" disabled>-- Choose a patient --</option>
              {patients.map(patient => (
                <option key={patient._id} value={patient._id}>
                  {patient.name}
                </option>
              ))}
            </select>
            <MdPersonPin className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" />
          </div>
        </div>


        {/* Doctor Selection */}
        <div className="mb-6">
          <label htmlFor="doctor" className="block text-sm font-medium text-gray-700 mb-1">Select Doctor</label>
          <select /* ... (existing doctor select, no changes needed here) ... */
            id="doctor"
            value={selectedDoctor}
            onChange={(e) => {
              setSelectedDoctor(e.target.value);
              setError(null);
              setBookingStatus(null);
            }}
            required
            className="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            disabled={!selectedPatient} // Optionally disable until patient is selected
          >
            <option value="" disabled>-- Choose a doctor --</option>
            {doctors.map(doctor => (
              <option key={doctor._id} value={doctor._id}>
                {doctor.name} ({doctor.specialty})
              </option>
            ))}
          </select>
        </div>

        {/* Date Picker */}
        <div className="mb-6">
          <label htmlFor="date" className="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
          <div className="relative">
            <input /* ... (existing date input, no changes needed here, but ensure disabled logic considers selectedPatient too) ... */
              id="date"
              type="date"
              value={selectedDate}
              onChange={(e) => {
                setSelectedDate(e.target.value);
                setError(null);
                setBookingStatus(null);
              }}
              required
              className="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10"
              min={minDate.toISOString().split('T')[0]}
              disabled={!selectedPatient || !selectedDoctor} // Disable if patient or doctor not selected
            />
            <MdCalendarToday className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" />
          </div>
        </div>

        {/* Time Slots */}
        {selectedPatient && selectedDoctor && selectedDate && ( // Ensure all previous selections are made
          // ... (existing time slot display logic - no changes needed here)
           <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-1">
              <MdAccessTime /> Available Times {loadingSlots && <span className="text-xs">(Loading...)</span>}
            </label>
            {loadingSlots ? ( /* ... loading slots display ... */ <div className="flex justify-center items-center h-20"><div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div></div> )
             : timeSlots.length > 0 ? (
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                {timeSlots.map(slot => ( <button key={slot._id} type="button" onClick={() => handleTimeSlotSelect(slot)} disabled={!slot.available || isBooking}
                    className={`p-2.5 rounded-lg text-sm transition-all duration-150 ease-in-out shadow-sm ${selectedTimeSlotId === slot._id ? 'bg-blue-600 text-white ring-2 ring-blue-300 scale-105' : slot.available ? 'bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 border border-gray-200' : 'bg-gray-200 text-gray-400 cursor-not-allowed opacity-70' }`} >
                    {slot.time} </button>
                ))} </div>
            ) : ( !error && <p className="text-sm text-gray-500">No time slots available for this selection.</p> )}
          </div>
        )}

        <button
          type="submit"
          // ... (existing button props, ensure disabled logic considers selectedPatient)
          className="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 flex items-center justify-center"
          disabled={!selectedPatient || !selectedDoctor || !selectedDate || !selectedTimeSlotId || isBooking || loadingSlots}
        >
          {/* ... (existing button content) ... */}
           {isBooking ? ( <><svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" /* ... */></svg>Booking...</> ) : ( 'Confirm Appointment' )}
        </button>
      </motion.form>
    </div>
  );
}