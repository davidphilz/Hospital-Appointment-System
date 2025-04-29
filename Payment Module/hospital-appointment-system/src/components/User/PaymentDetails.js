import React, { useEffect, useState } from 'react'
import axios from 'axios'
import { useLocation } from 'react-router-dom'
import '../../styles/User/PaymentDetails.css'

const PaymentDetails = () => {
  const [transaction, setTransaction] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  const location = useLocation()
  const params = new URLSearchParams(location.search)
  const transactionId = params.get('transaction_id') // Get transaction ID from URL

  useEffect(() => {
    if (!transactionId) {
      setError('Transaction ID not provided.')
      setLoading(false)
      return
    }

    axios
      .get(
        `http://localhost:5000/user/transaction-details?transaction_id=${transactionId}`,
      )
      .then((response) => {
        setTransaction(response.data)
        setLoading(false)
      })
      .catch((error) => {
        setError('Error fetching payment details.')
        setLoading(false)
      })
  }, [transactionId])

  return (
    <div className="payment-details">
      <h2>Payment Details</h2>
      {loading ? (
        <p>Loading transaction details...</p>
      ) : error ? (
        <p className="error">{error}</p>
      ) : (
        <div className="details-container">
          <p>
            <strong>Amount:</strong> NGN{transaction.amount}
          </p>
          <p>
            <strong>Payment Method:</strong> {transaction.payment_method}
          </p>
          <p>
            <strong>Status:</strong> {transaction.status}
          </p>
          <p>
            <strong>Transaction ID:</strong>{' '}
            {transaction.transaction_id || 'N/A'}
          </p>
          <p>
            <strong>Reference:</strong> {transaction.tx_ref || 'N/A'}
          </p>
          <p>
            <strong>Date:</strong>{' '}
            {new Date(transaction.created_at).toLocaleString()}
          </p>
        </div>
      )}
    </div>
  )
}

export default PaymentDetails
