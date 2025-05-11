// pages/index.tsx
import { motion } from 'framer-motion';
import Link from 'next/link';

export default function Home() {
  return (
    <div className="min-h-screen bg-gray-100">
      {/* Hero Section - Responsive text sizing and spacing */}
      <motion.section
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        className="py-12 md:py-20 px-4"
      >
        <div className="container mx-auto text-center max-w-6xl">
          <motion.h1
            initial={{ letterSpacing: '0px' }}
            animate={{ letterSpacing: '0.5px' }}
            className="text-3xl md:text-4xl lg:text-5xl font-bold text-blue-800 mb-4 md:mb-6 px-2"
          >
            Transform Healthcare Experiences with Smart Queue Management
          </motion.h1>
          <p className="text-base md:text-xl text-gray-700 mb-6 md:mb-8 max-w-3xl mx-auto">
            Minimize wait times, empower staff, and deliver patient-centered care through real-time coordination.
          </p>
          <Link href='/login'>
            <motion.button // Use motion.button directly as child
              whileHover={{ scale: 1.05 }}
              transition={{ type: "spring", stiffness: 400, damping: 10 }}
              className="bg-blue-600 text-white px-6 py-2 md:px-8 md:py-3 rounded-lg text-base md:text-lg font-semibold cursor-pointer"
            >
              Get Started →
            </motion.button>
          </Link>
        </div>
      </motion.section>

      {/* Features Section - Responsive grid and card sizing */}
      <section className="py-12 md:py-16 bg-white">
        <div className="container mx-auto px-4">
          <h2 className="text-2xl md:text-3xl font-bold text-center mb-8 md:mb-12">
            Why Hospitals Love Our System
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 lg:gap-8">
            {[
              {
                title: "Real-Time Patient Tracking",
                description: "Watch live queue updates, predict delays, and notify patients automatically. No more crowded waiting rooms."
              },
              {
                title: "Instant Appointment Booking",
                description: "Patients book slots online in 2 clicks. Doctors see schedules at a glance. Zero double-booking."
              },
              {
                title: "Priority & Emergency Handling",
                description: "Smart algorithms prioritize critical cases. Staff receive instant alerts for urgent needs."
              },
              {
                title: "Staff Productivity Dashboard",
                description: "Monitor resource usage, track performance, and optimize workflows in one unified view."
              }
            ].map((feature, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }} // Ensures animation plays only once when it enters view
                transition={{ delay: index * 0.2 }}
                className="p-4 md:p-6 bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-shadow"
              >
                <h3 className="text-lg md:text-xl font-semibold mb-2 md:mb-3">
                  {feature.title}
                </h3>
                <p className="text-sm md:text-base text-gray-600">
                  {feature.description}
                </p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section - Responsive button layout */}
      <section className="py-12 md:py-16 bg-blue-50">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-2xl md:text-3xl font-bold mb-4 md:mb-6">
            Ready to Modernize Your Hospital's Workflow?
          </h2>
          <p className="text-base md:text-xl mb-6 md:mb-8 max-w-2xl mx-auto">
            Join 500+ clinics delivering faster, kinder, and more efficient care.
          </p>
          <div className="flex flex-col sm:flex-row justify-center gap-3 md:gap-4">
            <Link href='/patient-appointment'>
              <motion.button // Use motion.button directly as child
                whileHover={{ scale: 1.05 }}
                className="bg-blue-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-lg text-sm md:text-base cursor-pointer"
              >
                Schedule appointment →
              </motion.button>
            </Link>
            <Link href='/contact-us'>
              <motion.button // Use motion.button directly as child
                whileHover={{ scale: 1.05 }}
                className="bg-teal-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-lg text-sm md:text-base cursor-pointer"
              >
                Talk to Our Team →
              </motion.button>
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}