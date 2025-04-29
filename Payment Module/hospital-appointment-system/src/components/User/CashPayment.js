import React, { useState } from 'react';
import axios from 'axios'; // Used for API requests
import '../../styles/User/CashPayment.css';
import { useNavigate } from 'react-router-dom';

const CashPayment = () => {
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [amount, setAmount] = useState('');
  const [message, setMessage] = useState('');
  const navigate = useNavigate();

  // Function to handle cash payment submission
  const handleCashPayment = async (e) => {
    e.preventDefault();

    // Validate inputs
    if (!email || !phone || !amount) {
      setMessage('All fields are required');
      return;
    }

    try {
      // Send payment request to backend
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
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />

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
