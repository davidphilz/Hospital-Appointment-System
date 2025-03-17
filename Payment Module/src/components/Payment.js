import React, { useState } from 'react'
import axios from 'axios'
import { useNavigate } from 'react-router-dom'
import '../styles/Payment.css'

const Payment = () => {
  const [amount, setAmount] = useState('')
  const [paymentMethod, setPaymentMethod] = useState('flutterwave') // Default option
  const [error, setError] = useState('')
  const navigate = useNavigate()

  const handlePayment = async () => {
    if (!amount) {
      setError('Please enter an amount.')
      return
    }
    setError('')

    const userEmail = localStorage.getItem('userEmail')
    if (!userEmail) {
      setError('User email not found. Please log in again.')
      return
    }

    try {
      if (paymentMethod === 'flutterwave') {
        //Initiate Flutterwave Payment
        const tx_ref = 'REF' + Date.now()
        console.log('Initiating payment with tx_ref:', tx_ref)

        //if you have a logged in user
        const response = await axios.post(
          'http://localhost:5000/flutterwave/pay',
          {
            amount,
            currency: 'NGN',
            email: userEmail,
            tx_ref,
          },
        )

        // Redirect user to the flutterwave payment page
        window.location.href = response.data.url
      } else if (paymentMethod === 'bank') {
        // For offline bank transfers. redirect to a dedicated bank transfer page
        navigate('/bank-transfer')
      }
    } catch (err) {
      console.error('Payment error:', err.response?.data || err.message)
      setError('Payment Initiation failed. Please try again.')
    }
  }

  return (
    <div className="payment-container">
      <div className="payment-box">
        <h2>Make a Payment</h2>
        {error && <p className="error-message">{error}</p>}
        <input
          type="number"
          placeholder="Enter Amount (NGN)"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
        />
        <div className="payment-options">
          <label>
            <input
              type="radio"
              name="paymentMethod"
              value="flutterwave"
              checked={paymentMethod === 'flutterwave'}
              onChange={() => setPaymentMethod('flutterwave')}
            />
            Flutterwave Payment
          </label>
          <label>
            <input
              type="radio"
              name="paymentMethod"
              value="bank"
              checked={paymentMethod === 'bank'}
              onChange={() => setPaymentMethod('bank')}
            />
            Bank Transfer (Offline)
          </label>
        </div>
        <button className="pay-button" onClick={handlePayment}>
          Proceed with Payment
        </button>
      </div>
    </div>
  )
}

export default Payment
