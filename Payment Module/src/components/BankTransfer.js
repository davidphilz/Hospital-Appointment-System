import React, { useState, useEffect } from 'react'
import axios from 'axios'
import { useNavigate } from 'react-router-dom'
import '../styles/BankTransfer.css'

const BankTransfer = () => {
  const [bankDetails, setBankDetails] = useState(null)
  const [amount, setAmount] = useState('')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const navigate = useNavigate()

  // Fetch bank details
  useEffect(() => {
    axios
      .get('http://localhost:5000/bank-details')
      .then((response) => setBankDetails(response.data))
      .catch((error) => console.error('Error fetching bank details: ', error))
  }, [])

  // Initiate bank transfer
  const handleInitiateTransfer = async (e) => {
    e.preventDefault()
    if (!amount) {
      setError('Please enter a amount.')
      return
    }

    //Assuming user email is stored in localStorage from login
    const user_email = localStorage.getItem('userEmail')
    if (!user_email) {
      setMessage('User email not found. Please log in again.')
      return
    }

    try {
      const response = await axios.post(
        'http://localhost:5000/user/initiate-bank-transfer',
        {
          user_email,
          amount,
        },
      )
      setMessage(response.data.message)
    } catch (err) {
      console.error('Bank transfer error: ', err.response?.data || err.message)
      setMessage('Bank transfer initalization failed.')
    }
  }

  return (
    <div className="bank-transfer-container">
      <h2>Bank Transfer</h2>

      {bankDetails ? (
        <div className="bank-details">
          <p>
            <strong>Bank Name:</strong> {bankDetails.bank_name}
          </p>
          <p>
            <strong>Account Number: </strong> {bankDetails.account_number}
          </p>
          <p>
            <strong>Account Name: </strong> {bankDetails.account_name}
          </p>
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
  )
}

export default BankTransfer
