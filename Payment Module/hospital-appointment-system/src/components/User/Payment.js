import React, { useState } from 'react';
import axios from 'axios';
import { motion } from 'framer-motion'; // For smooth animations
import { Loader } from 'lucide-react'; // Loading icon
import { useLocation } from 'react-router-dom';
import '../../styles/User/Payment.css';

const Payment = () => {
  const [amount, setAmount] = useState(''); // Stores the payment amount
  const [error, setError] = useState(''); // Stores error messages
  const [loading, setLoading] = useState(false); // Tracks loading state for UX feedback

  const location = useLocation();
  const queryParams = new URLSearchParams(location.search);
  const email = queryParams.get('email'); // Get email from query parameters
  const appointmentId = queryParams.get('appointmentId'); // Get appointmentId from query parameters

  console.log('Email:', email);
  console.log('Appointment ID:', appointmentId);

  const handlePayment = async () => {
    if (!amount || parseFloat(amount) <= 0) {
      setError('Please enter a valid amount.'); // Prevents invalid input
      return;
    }
    setError('');
    setLoading(true); // Shows loading indicator

    try {
      const tx_ref = 'REF' + Date.now(); // Generate a unique transaction reference
      console.log('Initiating payment with tx_ref:', tx_ref);

      const response = await axios.post('http://localhost:5000/flutterwave/pay', {
        amount,
        currency: 'NGN',
        email,
        tx_ref,
        appointmentId,
      });

      window.location.href = response.data.url; // Redirect to Flutterwave payment page
    } catch (err) {
      console.error('Payment error:', err.response?.data || err.message);
      setError('Payment initiation failed. Please try again.');
    } finally {
      setLoading(false); // Hides loading indicator after payment attempt
    }
  };

  return (
    <motion.div
      className="payment-container"
      initial={{ opacity: 0, y: -20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5 }}
    >
      <motion.div
        className="payment-box"
        whileHover={{ scale: 1.02 }}
        transition={{ type: 'spring', stiffness: 200 }}
      >
        <h2>Make a Payment</h2>
        {error && <p className="error-message">{error}</p>}
        <input
          type="number"
          placeholder="Enter Amount (NGN)"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
        />
        <motion.button
          className="pay-button"
          onClick={handlePayment}
          whileTap={{ scale: 0.95 }}
          disabled={loading}
        >
          {loading ? <Loader className="loading-icon" /> : 'Proceed with Payment'}
        </motion.button>
        <div>
          <p>Email: {email}</p>
          <p>Appointment ID: {appointmentId}</p>
        </div>
      </motion.div>
    </motion.div>
  );
};

export default Payment;