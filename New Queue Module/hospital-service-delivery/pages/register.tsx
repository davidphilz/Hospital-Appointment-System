// pages/register.tsx
import { useState, FormEvent } from 'react';
import { auth } from '../firebase/firebase'; // Adjust path as necessary
import { createUserWithEmailAndPassword, AuthError, updateProfile, User as FirebaseUser } from 'firebase/auth';
import { useRouter } from 'next/router';
import { motion } from 'framer-motion';
import Link from 'next/link';
import axios, { AxiosError } from 'axios'; // For calling the onboarding API

interface ApiErrorResponse { message: string; error?: string; }

export default function PatientRegistrationPage() { // Renamed component for clarity
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const router = useRouter();
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  const handleRegister = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    // Client-side validations
    if (!name.trim() || !email.trim() || !password.trim() || !confirmPassword.trim()) {
      setError("Please fill in all fields.");
      setLoading(false);
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
        setError("Please enter a valid email address.");
        setLoading(false);
        return;
    }
    if (password !== confirmPassword) {
      setError('Passwords do not match.');
      setLoading(false);
      return;
    }
    if (password.length < 6) {
      setError('Password should be at least 6 characters long.');
      setLoading(false);
      return;
    }

    try {
      // 1. Create Firebase User
      const userCredential = await createUserWithEmailAndPassword(auth, email.trim(), password);
      const firebaseUser = userCredential.user;

      // 2. Update Firebase User Profile (Optional, but good for display name)
      if (firebaseUser) {
        await updateProfile(firebaseUser, { displayName: name.trim() });
      } else {
        // This state should ideally not be reached if createUserWithEmailAndPassword succeeded.
        throw new Error("Firebase user not available immediately after registration.");
      }

      // 3. Onboard Patient to MySQL Database via your API
      // Send the ID token for backend verification
      const token = await firebaseUser.getIdToken();
      console.log("Attempting to onboard patient to MySQL. Firebase UID:", firebaseUser.uid); // Debug log

      await axios.post(`${apiUrl}/api/patients/onboard`,
        {
          firebaseUid: firebaseUser.uid,
          email: firebaseUser.email, // Already available from firebaseUser
          name: name.trim(),
          // If you add a 'contact' field to this form, pass it here:
          // contact: contactValue,
        },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      console.log("Patient onboarding API call successful."); // Debug log


      // 4. Redirect to Patient Dashboard on full success
      router.push('/patient/dashboard?registration=success');

    } catch (err) {
      const error = err as AuthError | AxiosError<ApiErrorResponse> | Error;
      console.error('Error during registration process:', error); // Log the full error object

      if (axios.isAxiosError(error)) { // Error from our onboarding API
        const apiError = error as AxiosError<ApiErrorResponse>;
        const apiErrorMessage = apiError.response?.data?.message || "Failed to complete your patient profile setup after account creation.";
        console.error("Onboarding API Error Details:", apiError.response?.data);
        setError(`Account created, but profile setup failed: ${apiErrorMessage} Please contact support or try logging in to see if setup can be completed.`);
      } else if ((error as AuthError).code) { // Firebase Auth Error
        const fbError = error as AuthError;
        if (fbError.code === 'auth/email-already-in-use') {
          setError('This email address is already in use. Please try logging in instead.');
        } else if (fbError.code === 'auth/invalid-email') {
          setError('The email address is not valid.');
        } else if (fbError.code === 'auth/weak-password') {
          setError('The password is too weak. Please choose a stronger one (at least 6 characters).');
        } else {
          setError(`Registration failed: ${fbError.message}`);
        }
      } else { // Other JavaScript errors (e.g., the "Firebase user not available" error I added)
          setError('An unexpected error occurred during registration: ' + error.message);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -20 }}
      transition={{ duration: 0.3 }}
      className="min-h-screen bg-gray-100 flex items-center justify-center p-4"
    >
      <div className="bg-white p-8 rounded-xl shadow-xl w-full max-w-md">
        <h2 className="text-2xl sm:text-3xl font-bold text-blue-700 mb-8 text-center">
          Create Your Patient Account
        </h2>

        {error && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6 text-sm"
            role="alert"
          >
            <span className="block sm:inline whitespace-pre-line">{error}</span>
          </motion.div>
        )}

        <form onSubmit={handleRegister} className="space-y-5">
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">Full Name <span className="text-red-500">*</span></label>
            <input
              id="name" type="text" placeholder="e.g., Jane Doe" value={name}
              onChange={(e) => setName(e.target.value)} required
              className="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              disabled={loading}
            />
          </div>
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">Email Address <span className="text-red-500">*</span></label>
            <input
              id="email" type="email" placeholder="you@example.com" value={email}
              onChange={(e) => setEmail(e.target.value)} required
              className="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              disabled={loading}
            />
          </div>
          <div>
            <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-1">Password <span className="text-red-500">*</span></label>
            <input
              id="password" type="password" placeholder="Min. 6 characters" value={password}
              onChange={(e) => setPassword(e.target.value)} required
              className="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              disabled={loading}
            />
          </div>
          <div>
            <label htmlFor="confirmPassword" className="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span className="text-red-500">*</span></label>
            <input
              id="confirmPassword" type="password" placeholder="Re-type your password" value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)} required
              className="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              disabled={loading}
            />
          </div>
          <button
            type="submit" disabled={loading}
            className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 flex items-center justify-center"
          >
            {loading ? (
              <>
                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating Account...
              </>
            ) : (
              'Create Account'
            )}
          </button>
        </form>
        <p className="mt-8 text-center text-sm text-gray-600">
          Already have an account?{' '}
          <Link href="/login" className="font-medium text-blue-600 hover:text-blue-500 hover:underline">
            Log In
          </Link>
        </p>
      </div>
    </motion.div>
  );
}