// pages/contact-us.tsx
import React from 'react';
import { motion } from 'framer-motion';
import { MdEmail, MdPhone, MdLocationOn } from 'react-icons/md';
import Link from 'next/link'; // For a back button or home link

export default function ContactUsPage() {
  return (
    <div className="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="max-w-3xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden"
      >
        <div className="bg-blue-600 p-6 sm:p-8">
          <h1 className="text-3xl font-bold text-white text-center">Contact Us</h1>
          <p className="mt-2 text-blue-100 text-center text-sm sm:text-base">
            We're here to help and answer any question you might have.
          </p>
        </div>

        <div className="p-6 sm:p-8 space-y-8">
          <section>
            <h2 className="text-xl font-semibold text-gray-700 mb-3">Get in Touch</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="flex items-start p-4 bg-gray-50 rounded-lg">
                <MdEmail className="text-3xl text-blue-500 mr-4 mt-1 flex-shrink-0" />
                <div>
                  <h3 className="font-medium text-gray-800">Email Us</h3>
                  <p className="text-sm text-gray-600">
                    Send your queries to our support team.
                  </p>
                  <a href="mailto:support@hospitalsystem.com" className="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    support@hospitalsystem.com
                  </a>
                </div>
              </div>
              <div className="flex items-start p-4 bg-gray-50 rounded-lg">
                <MdPhone className="text-3xl text-blue-500 mr-4 mt-1 flex-shrink-0" />
                <div>
                  <h3 className="font-medium text-gray-800">Call Us</h3>
                  <p className="text-sm text-gray-600">
                    Speak directly with our representatives.
                  </p>
                  <a href="tel:+1234567890" className="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    +1 (234) 567-890
                  </a>
                </div>
              </div>
            </div>
          </section>

          <section>
            <h2 className="text-xl font-semibold text-gray-700 mb-3">Our Location</h2>
            <div className="flex items-start p-4 bg-gray-50 rounded-lg">
              <MdLocationOn className="text-3xl text-blue-500 mr-4 mt-1 flex-shrink-0" />
              <div>
                <h3 className="font-medium text-gray-800">Main Office</h3>
                <p className="text-sm text-gray-600">
                  123 Health St, Wellness City, HC 45678
                </p>
                <a
                  href="https://maps.google.com/?q=123+Health+St,+Wellness+City"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Get Directions
                </a>
              </div>
            </div>
            {/* You could embed a map here if desired */}
            {/* <div className="mt-4 h-64 bg-gray-200 rounded-md"> Map Placeholder </div> */}
          </section>

          <div className="text-center mt-8 pt-6 border-t border-gray-200">
            <Link href="/"
                className="text-blue-600 hover:text-blue-800 font-medium"
              >
                ← Back to Home
            </Link>
          </div>
        </div>
      </motion.div>
    </div>
  );
}