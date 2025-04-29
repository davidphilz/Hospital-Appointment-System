import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom'; // Import the navigate function from react-router-dom

import '../../styles/User/HMOPayment.css'; // Import CSS file for styling

const HMOPayment = () => {
  // State variables for form fields and messages
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [amount, setAmount] = useState('');
  const [message, setMessage] = useState('');
  const navigate = useNavigate();

  // Function to handle form submission
  const handleHMOPayment = async (e) => {
    e.preventDefault(); // Prevent page reload

    // Validate inputs
    if (!email || !phone || !amount) {
      setMessage('All fields are required');
      return;
    }

    try {
      // Send a POST request to the backend to initiate HMO payment
      const response = await axios.post('http://localhost:5000/user/hmo-payment', {
        email,
        phone,
        amount,
      });

      // Display response message from backend
      setMessage(response.data.message);

      // Navigate to confirmation page using the email as a route param
      navigate(`/payment-confirmation/${email}`);


    } catch (error) {
      // Handle any errors during API call
      setMessage('Error initiating HMO payment. Please try again.');
    }
  };

  return (
    <div className="hmo-page"> {/* Page background container */}
      <div className="hmo-payment-container"> {/* Main form container */}
        <h2>HMO Payment</h2> {/* Title */}
        
        {/* Display any success/error messages */}
        {message && <p className="message">{message}</p>}

        {/* Payment Form */}
        <form onSubmit={handleHMOPayment}>
          <label>Email: </label>
          <input 
            type="email" 
            value={email} 
            onChange={(e) => setEmail(e.target.value)} 
            required 
          />

          <label>Phone Number: </label>
          <input 
            type="text" 
            value={phone} 
            onChange={(e) => setPhone(e.target.value)} 
            required 
          />

          <label>Amount: </label>
          <input 
            type="number" 
            value={amount} 
            onChange={(e) => setAmount(e.target.value)} 
            required 
          />

          <button type="submit">Initiate Payment</button>
        </form>
      </div>
    </div>
  );
};

export default HMOPayment;
