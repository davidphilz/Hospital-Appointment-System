import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate, useLocation } from 'react-router-dom';
import '../../styles/User/CashPayment.css';

const CashPayment = () => {
  const [phone, setPhone] = useState('');
  const [amount, setAmount] = useState('');
  const [message, setMessage] = useState('');
  const navigate = useNavigate();
  const location = useLocation();
  const queryParams = new URLSearchParams(location.search);
  const email = queryParams.get('email'); // Get email from query parameters
  const appointmentId = queryParams.get('appointmentId'); // Get appointmentId from query parameters

  console.log('Email:', email);
  console.log('Appointment ID:', appointmentId);

  const handleCashPayment = async (e) => {
    e.preventDefault();

    if (!email || !phone || !amount) {
      setMessage('All fields are required');
      return;
    }

    try {
      const response = await axios.post('http://localhost:5000/user/cash-payment', {
        email,
        phone,
        amount,
      });

      setMessage(response.data.message);

      // Navigate to confirmation page with email
      navigate(`/payment-confirmation/${email}`);
    } catch (error) {
      setMessage('Error processing cash payment. Please try again.');
    }
  };

  return (
    <div className="cash-payment-container">
      <h2>Cash Payment</h2>
      {message && <p className="message">{message}</p>}
      <form onSubmit={handleCashPayment}>
        <label>Email:</label>
        <input type="email" value={email} readOnly />

        <label>Phone Number:</label>
        <input
          type="text"
          value={phone}
          onChange={(e) => setPhone(e.target.value)}
          required
        />

        <label>Amount:</label>
        <input
          type="number"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
          required
        />

        <button type="submit">Process Payment</button>
      </form>
    </div>
  );
};

export default CashPayment;
