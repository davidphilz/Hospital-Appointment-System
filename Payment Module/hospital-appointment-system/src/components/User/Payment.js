import React, { useState } from 'react';
import axios from 'axios';
import { motion } from 'framer-motion'; // For smooth animations
import { Loader } from 'lucide-react'; // Loading icon
import '../../styles/User/Payment.css';

const Payment = () => {
  const [amount, setAmount] = useState(''); // Stores the payment amount
  const [error, setError] = useState(''); // Stores error messages
  const [loading, setLoading] = useState(false); // Tracks loading state for UX feedback

  const handlePayment = async () => {
    if (!amount || parseFloat(amount) <= 0) {
      setError('Please enter a valid amount.'); // Prevents invalid input
      return;
    }
    setError('');
    setLoading(true); // Shows loading indicator

    const userEmail = localStorage.getItem('userEmail'); // Retrieves user email from local storage
    if (!userEmail) {
      setError('User email not found. Please log in again.');
      setLoading(false);
      return;
    }

    try {
      const tx_ref = 'REF' + Date.now(); // Generates a unique transaction reference
      console.log('Initiating payment with tx_ref:', tx_ref);

      const response = await axios.post('http://localhost:5000/flutterwave/pay', {
        amount,
        currency: 'NGN',
        email: userEmail,
        tx_ref,
      });

      window.location.href = response.data.url; // Redirects user to the Flutterwave payment page
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
      </motion.div>
    </motion.div>
  );
};

export default Payment;