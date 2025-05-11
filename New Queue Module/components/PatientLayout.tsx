// components/PatientLayout.tsx
import React, { ReactNode, useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { auth } from '../firebase/firebase'; // Adjust path
import { signOut, User } from 'firebase/auth';
import { MdDashboard, MdEventNote, MdPersonOutline, MdExitToApp, MdMenu, MdClose, MdBookOnline } from 'react-icons/md';

interface PatientLayoutProps {
  children: ReactNode;
}

const PatientLayout: React.FC<PatientLayoutProps> = ({ children }) => {
  const router = useRouter();
  const [currentUser, setCurrentUser] = useState<User | null>(auth.currentUser); // Initialize with current user
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  useEffect(() => {
    const unsubscribe = auth.onAuthStateChanged(user => {
      setCurrentUser(user);
      if (!user) { // If user logs out or session expires, redirect
        router.push('/login?redirect=' + router.pathname);
      }
    });
    return () => unsubscribe();
  }, [router]);

  const handleLogout = async () => {
    try {
      await signOut(auth);
      router.push('/login');
    } catch (error) {
      console.error("Error signing out: ", error);
    }
  };

  const patientNavItems = [
    { href: '/patient/dashboard', label: 'My Dashboard', icon: MdDashboard },
    { href: '/patient/appointment', label: 'Book Appointment', icon: MdBookOnline },
    // { href: '/patient/profile', label: 'My Profile', icon: MdPersonOutline }, // If you create this page
  ];

  const NavLinks: React.FC<{isMobile?: boolean}> = ({isMobile = false}) => (
    <>
      {patientNavItems.map((item) => (
        <Link key={item.href} href={item.href}
            className={`flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
              ${router.pathname === item.href
                ? (isMobile ? 'bg-blue-100 text-blue-700' : 'bg-blue-700 text-white')
                : (isMobile ? 'text-gray-700 hover:bg-gray-100' : 'text-blue-100 hover:bg-blue-600 hover:text-white')}
              ${isMobile ? 'text-base' : ''}
            `}
            onClick={() => isMobile && setIsMobileMenuOpen(false)}
          >
            <item.icon className={`mr-2 ${isMobile ? 'text-xl text-blue-600' : 'text-lg'}`} />
            {item.label}
        </Link>
      ))}
      <button
        onClick={handleLogout}
        className={`flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
          ${isMobile ? 'text-gray-700 hover:bg-gray-100' : 'text-blue-100 hover:bg-red-600 hover:text-white'}
          ${isMobile ? 'text-base w-full text-left' : ''}
        `}
      >
        <MdExitToApp className={`mr-2 ${isMobile ? 'text-xl text-red-600' : 'text-lg'}`} />
        Logout
      </button>
    </>
  );

  if (!currentUser) { // Optional: show a loading or a "redirecting" state
    return <div className="min-h-screen flex items-center justify-center"><p>Loading user session...</p></div>;
  }

  return (
    <div className="min-h-screen flex flex-col">
      <nav className="bg-blue-gradient-dark shadow-md sticky top-0 z-50" style={{background: 'linear-gradient(to right, #1e3a8a, #2563eb)'}}>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <Link href="/patient/dashboard" className="text-white text-xl font-semibold hover:opacity-80 transition-opacity">
                Patient Portal
            </Link>
            <div className="hidden md:block">
              <div className="ml-10 flex items-baseline space-x-1">
                <NavLinks />
              </div>
            </div>
            <div className="md:hidden flex items-center">
                <button
                    onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                    className="p-2 rounded-md text-blue-200 hover:text-white hover:bg-blue-700/50 focus:outline-none"
                >
                    {isMobileMenuOpen ? <MdClose size={24} /> : <MdMenu size={24} />}
                </button>
            </div>
          </div>
        </div>
        {isMobileMenuOpen && (
            <div className="md:hidden bg-white shadow-lg rounded-b-md mx-1 p-2 space-y-1">
                <NavLinks isMobile={true}/>
            </div>
        )}
      </nav>
      <main className="flex-grow bg-gray-100">
        {children}
      </main>
       <footer className="bg-gray-800 text-gray-300 text-center p-3 text-xs">
        © {new Date().getFullYear()} Your Hospital Name. Patient Secure Portal.
      </footer>
    </div>
  );
};

export default PatientLayout;