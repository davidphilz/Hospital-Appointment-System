import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate, useLocation } from 'react-router-dom';
import '../../styles/User/BankTransfer.css';

const BankTransfer = () => {
  const [bankDetails, setBankDetails] = useState(null);
  const [amount, setAmount] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const location = useLocation();
  const queryParams = new URLSearchParams(location.search);
  const email = queryParams.get('email'); // Get email from query parameters
  const appointmentId = queryParams.get('appointmentId'); // Get appointmentId from query parameters

  console.log('Email:', email);
  console.log('Appointment ID:', appointmentId);

  useEffect(() => {
    axios
      .get('http://localhost:5000/bank-details')
      .then((response) => setBankDetails(response.data))
      .catch((error) => console.error('Error fetching bank details: ', error));
  }, []);

  const handleInitiateTransfer = async (e) => {
    e.preventDefault();
    if (!amount) {
      setError('Please enter an amount.');
      return;
    }

    try {
      const response = await axios.post(
        'http://localhost:5000/user/initiate-bank-transfer',
        { user_email: email, amount } // Use the email from query parameters
      );
      setMessage(response.data.message);

      // Redirect to payment confirmation page
      navigate(`/payment-confirmation/${email}`);
    } catch (err) {
      console.error('Bank transfer error: ', err.response?.data || err.message);
      setMessage('Bank transfer initialization failed.');
    }
  };

  return (
    <div className="bank-transfer-container">
      <div className="bank-transfer-card">
        <h2>Bank Transfer</h2>
        <p>Email: {email}</p>
        <p>Appointment ID: {appointmentId}</p>
        {bankDetails ? (
          <div className="bank-details">
            <p><strong>Bank Name:</strong> {bankDetails.bank_name}</p>
            <p><strong>Account Number:</strong> {bankDetails.account_number}</p>
            <p><strong>Account Name:</strong> {bankDetails.account_name}</p>
          </div>
        ) : (
          <p>Loading bank details...</p>
        )}
        <form onSubmit={handleInitiateTransfer}>
          <input
            type="number"
            placeholder="Enter Amount (NGN)"
            value={amount}
            onChange={(e) => setAmount(e.target.value)}
            required
          />
          <button type="submit">Initiate Transfer</button>
        </form>
        {message && <p className="status-message">{message}</p>}
      </div>
    </div>
  );
};

export default BankTransfer;