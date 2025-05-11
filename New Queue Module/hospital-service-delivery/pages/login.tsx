// pages/login.tsx (Now primarily for PATIENTS)
import { useState, FormEvent } from 'react';
import { auth, googleProvider } from '../firebase/firebase'; // Adjust path
import { signInWithEmailAndPassword, signInWithPopup, AuthError, getIdTokenResult, User } from 'firebase/auth'; // Added User, getIdTokenResult
import { useRouter } from 'next/router';
import { motion } from 'framer-motion';
import Link from 'next/link';
import { FcGoogle } from 'react-icons/fc';
import { MdAdminPanelSettings } from 'react-icons/md'; // Icon for admin login

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  const handleSuccessfulLogin = async (user: any) => { // user is Firebase User
    try {
      const idTokenResult = await getIdTokenResult(user);
      const userRole = idTokenResult.claims.role as string | undefined; // Assuming 'role' claim

      const intendedRedirect = router.query.redirect as string;

      if (userRole === 'admin' || userRole === 'staff') {
        router.push(intendedRedirect && intendedRedirect.startsWith('/admin') ? intendedRedirect : '/dashboard');
      } else {
        // Default to patient dashboard if no specific role or 'patient' role
        router.push(intendedRedirect && intendedRedirect.startsWith('/patient') ? intendedRedirect : '/patient/dashboard');
      }
    } catch (e) {
      console.error("Error getting ID token or redirecting:", e);
      // Fallback redirect if claims check fails
      router.push('/');
    }
  };

  const handleLogin = async (e: FormEvent) => {
    e.preventDefault();
    setError(null); setLoading(true);
    if (!email || !password) { /* ... */ setError("Please enter both email and password."); setLoading(false); return; }

    try {
      const userCredential = await signInWithEmailAndPassword(auth, email, password);
      await handleSuccessfulLogin(userCredential.user);
    } catch (err) { /* ... existing error handling ... */
        const authError = err as AuthError;
        console.error('Error logging in:', authError);
        if (authError.code === 'auth/user-not-found' || authError.code === 'auth/wrong-password' || authError.code === 'auth/invalid-credential') {
            setError('Invalid email or password. Please try again.');
        } else if (authError.code === 'auth/invalid-email') {
            setError('Please enter a valid email address.');
        } else {
            setError('Failed to log in. Please try again later.');
        }
    } finally { setLoading(false); }
  };

  const handleGoogleLogin = async () => {
    setError(null); setLoading(true);
    try {
      const userCredential = await signInWithPopup(auth, googleProvider);
      await handleSuccessfulLogin(userCredential.user);
    } catch (e) { /* ... existing error handling ... */
        const authError = e as AuthError;
        console.error('Error logging in with Google:', authError);
        if (authError.code === 'auth/popup-closed-by-user') {
            setError('Google sign-in was cancelled.');
        } else {
            setError('Failed to log in with Google. Please try again later.');
        }
    } finally { setLoading(false); }
  };

  return (
    // ... JSX remains largely the same as your refined login page ...
    // Make sure to include the error display, form, Google button, and link to register.
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -20 }}
      transition={{ duration: 0.3 }}
      className="min-h-screen bg-gray-100 flex items-center justify-center p-4"
    >
      <div className="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 className="text-3xl font-bold text-blue-700 mb-8 text-center">
          Welcome Back!
        </h2>
        {error && ( <motion.div /* ... error display ... */ className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6 text-sm" role="alert"> <span className="block sm:inline">{error}</span> </motion.div> )}
        <form onSubmit={handleLogin} className="space-y-6">
          {/* Email Input */}
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1"> Email address </label>
            <input id="email" type="email" placeholder="you@example.com" value={email} onChange={(e) => setEmail(e.target.value)} required className="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" disabled={loading} />
          </div>
          {/* Password Input */}
          <div>
            <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-1"> Password </label>
            <input id="password" type="password" placeholder="••••••••" value={password} onChange={(e) => setPassword(e.target.value)} required className="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" disabled={loading} />
          </div>
          {/* Submit Button */}
          <button type="submit" disabled={loading} className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 flex items-center justify-center">
            {loading ? <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" /* ... spinner svg ... */></svg> : 'Log In'}
          </button>
        </form>
        {/* "Or continue with" separator and Google Login Button */}
        <div className="mt-6"> <div className="relative"> <div className="absolute inset-0 flex items-center"> <div className="w-full border-t border-gray-300"></div> </div> <div className="relative flex justify-center text-sm"> <span className="px-2 bg-white text-gray-500">Or continue with</span> </div> </div>
          <button onClick={handleGoogleLogin} disabled={loading} className="w-full mt-4 py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-70 flex items-center justify-center"> <FcGoogle className="h-5 w-5 mr-2" /> Sign in with Google </button>
        </div>
        {/* Link to Register Page */}
        <p className="mt-8 text-center text-sm text-gray-600"> Dont have an account?{' '} <Link href="/register" className="font-medium text-blue-600 hover:text-blue-500 hover:underline"> Sign up </Link> </p>
      
	  {/* --- LINK TO ADMIN LOGIN --- */}
        <div className="mt-6 pt-6 border-t border-gray-200 text-center">
          <Link href="/admin/login"
              className="inline-flex items-center gap-2 text-xs text-gray-500 hover:text-blue-600 hover:underline"
            >
              <MdAdminPanelSettings />
              Admin/Staff Login
          </Link>
        </div>
        {/* --- END LINK TO ADMIN LOGIN --- */}
	  </div>
    </motion.div>
  );
}