// pages/_app.tsx
import type { AppProps } from 'next/app';
import "../styles/globals.css";
import { useEffect } from 'react';
import AdminLayout from '../components/Layout'; // Assuming this is your original Layout renamed
import PatientLayout from '../components/PatientLayout';
import { useRouter } from 'next/router';

export default function App({ Component, pageProps }: AppProps) {
  const router = useRouter();

  useEffect(() => {
    fetch('/api/socket');
  }, []);

  // Determine which layout to use
  if (router.pathname.startsWith('/patient/')) {
    // No layout for login/register if they are full-page designs
    if (router.pathname === '/patient/login' || router.pathname === '/patient/register') { // If you have patient-specific login/register
        return <Component {...pageProps} />;
    }
    return (
      <PatientLayout>
        <Component {...pageProps} />
      </PatientLayout>
    );
  } else if (router.pathname.startsWith('/admin/')) { // Example if you move admin pages
     // No layout for admin login if it's a full-page design
    if (router.pathname === '/admin/login') {
        return <Component {...pageProps} />;
    }
    return (
        <AdminLayout> {/* Your existing Layout, perhaps renamed or modified for Admin */}
            <Component {...pageProps} />
        </AdminLayout>
    );
  } else if (router.pathname === '/login' || router.pathname === '/register') {
    // Generic login/register pages without a specific area layout
    return <Component {...pageProps} />;
  } else if (router.pathname === '/') { // Landing page might not need full auth layout
    return <Component {...pageProps} />;
  }


  // Default layout for other admin pages (like /dashboard, /queue)
  // Or for public pages that still use the main layout. Adjust as needed.
  // This assumes /dashboard, /queue etc are admin pages.
  // If these are public facing but use a layout, this is fine.
  // If they are admin only, they should be under /admin/ and use AdminLayout
  // For simplicity, current AdminLayout (original Layout) will apply to non-specific paths
  // You need a clear strategy for which pages are admin vs public vs patient.
  if (router.pathname === '/dashboard' || router.pathname === '/queue' || router.pathname === '/patient-registration' || router.pathname === '/patient-appointment') {
     // These are now considered ADMIN pages based on your clarification
     return (
        <AdminLayout>
            <Component {...pageProps} />
        </AdminLayout>
     )
  }


  return <Component {...pageProps} />; // For pages without a layout (e.g. 404, _error, or landing if designed so)
}