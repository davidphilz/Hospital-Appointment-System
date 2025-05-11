// components/Layout.tsx
import React, { ReactNode, useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { auth } from '../firebase/firebase'; // Adjust path
import { signOut, onAuthStateChanged, User } from 'firebase/auth';
import { MdDashboard, MdPeople, MdEventAvailable, MdQueue, MdLogout, MdLogin, MdPersonAdd, MdHome, MdMenu, MdClose } from 'react-icons/md';

interface LayoutProps {
  children: ReactNode;
}

interface NavItem {
  href: string;
  label: string;
  icon: React.ElementType;
  authRequired?: boolean; // True if link should only show for logged-in users
  publicOnly?: boolean;   // True if link should only show for logged-out users
}

const navItems: NavItem[] = [
  { href: '/', label: 'Home', icon: MdHome },
  { href: '/dashboard', label: 'Dashboard', icon: MdDashboard, authRequired: true },
  { href: '/queue', label: 'Queue Mgmt', icon: MdQueue, authRequired: true },
  { href: '/patient-registration', label: 'Register Patient', icon: MdPersonAdd, authRequired: true }, // Staff action
  { href: '/patient-appointment', label: 'Book Appointment', icon: MdEventAvailable }, // Can be public or auth
  { href: '/notification-board', label: 'Patient Queue', icon: MdPeople }, // Public facing
  { href: '/login', label: 'Login', icon: MdLogin, publicOnly: true },
  { href: '/register', label: 'Sign Up', icon: MdPersonAdd, publicOnly: true },
];

const Layout: React.FC<LayoutProps> = ({ children }) => {
  const router = useRouter();
  const [currentUser, setCurrentUser] = useState<User | null>(null);
  const [loadingAuth, setLoadingAuth] = useState(true);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  useEffect(() => {
    const unsubscribe = onAuthStateChanged(auth, (user) => {
      setCurrentUser(user);
      setLoadingAuth(false);
    });
    return () => unsubscribe();
  }, []);

  const handleLogout = async () => {
    try {
      await signOut(auth);
      router.push('/login');
    } catch (error) {
      console.error("Error signing out: ", error);
    }
  };

  const filteredNavItems = navItems.filter(item => {
    if (loadingAuth) return false; // Don't show auth-dependent links while loading
    if (item.authRequired) return !!currentUser;
    if (item.publicOnly) return !currentUser;
    return true; // Default: show item
  });

  const NavLinks: React.FC<{isMobile?: boolean}> = ({isMobile = false}) => (
    <>
      {filteredNavItems.map((item) => (
        <Link key={item.href} href={item.href}
            className={`flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
              ${router.pathname === item.href
                ? 'bg-blue-700 text-white'
                : 'text-gray-300 hover:bg-blue-600 hover:text-white'}
              ${isMobile ? 'text-base text-gray-700 hover:bg-gray-100 w-full' : ''}
            `}
            onClick={() => isMobile && setIsMobileMenuOpen(false)} // Close mobile menu on click
          >
            <item.icon className={`mr-2 ${isMobile ? 'text-xl text-blue-600' : 'text-lg'}`} />
            {item.label}
        </Link>
      ))}
      {currentUser && !loadingAuth && (
        <button
          onClick={handleLogout}
          className={`flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
            text-gray-300 hover:bg-red-600 hover:text-white
            ${isMobile ? 'text-base text-gray-700 hover:bg-gray-100 w-full' : ''}
          `}
        >
          <MdLogout className={`mr-2 ${isMobile ? 'text-xl text-red-600' : 'text-lg'}`} />
          Logout
        </button>
      )}
    </>
  );


  return (
    <div className="min-h-screen flex flex-col">
      <nav className="bg-blue-800 shadow-lg">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center">
              <Link href="/" className="text-white text-xl font-bold">
                Queue Management
              </Link>
            </div>
            {/* Desktop Menu */}
            <div className="hidden md:block">
              <div className="ml-10 flex items-baseline space-x-2">
                <NavLinks />
              </div>
            </div>
            {/* Mobile menu button */}
            <div className="md:hidden flex items-center">
                <button
                    onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                    className="inline-flex items-center justify-center p-2 rounded-md text-blue-200 hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                >
                    <span className="sr-only">Open main menu</span>
                    {isMobileMenuOpen ? <MdClose size={24} /> : <MdMenu size={24} />}
                </button>
            </div>
          </div>
        </div>
        {/* Mobile Menu, show/hide based on state */}
        {isMobileMenuOpen && (
            <div className="md:hidden bg-white shadow-md rounded-b-lg mx-2 mb-2 p-2 space-y-1">
                <NavLinks isMobile={true}/>
            </div>
        )}
      </nav>

      <main className="flex-grow bg-gray-100">
        {/* Loading Auth State Indicator (Optional) */}
        {/* {loadingAuth && <div className="p-4 text-center text-gray-500">Authenticating...</div>} */}
        {!loadingAuth && children}
      </main>

      <footer className="bg-gray-700 text-white text-center p-4 text-sm">
        © {new Date().getFullYear()} Hospital Service Delivery. All rights reserved.
      </footer>
    </div>
  );
};

export default Layout;