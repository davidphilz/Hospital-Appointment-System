import React, { useState, useEffect } from 'react'
import axios from 'axios'
import '../../styles/Admin/PendingCashPayments.css'

const PendingCashPayments = () => {
  const [cashPayments, setCashPayments] = useState([])
  const [message, setMessage] = useState('')

  //Fetch pending payments
  useEffect(() => {
    fetchPendingCashPayments()
  }, [])

  const fetchPendingCashPayments = () => {
    axios
      .get('http://localhost:5000/admin/pending-cash-payments')
      .then((response) => setCashPayments(response.data))
      .catch((error) =>
        console.error('Error fetching pending cash payments:', error),
      )
  }

  const handleApproval = async (paymentId, status) => {
    try {
      // Sending the approval/rejection request
      const response = await axios.post(
        'http://localhost:5000/admin/cash-payment/approve',
        {
          payment_id: paymentId,
          status,
        },
      )

      setMessage(response.data.message)

      // Add slight delay before re-fetching
      setTimeout(fetchPendingCashPayments, 500)

      // ✅ Re-fetch updated list instead of filtering manually
      const refreshed = await axios.get(
        'http://localhost:5000/admin/pending-cash-payments',
      )
      setCashPayments(refreshed.data)
    } catch (error) {
      console.error(
        'Error processing payment:',
        error.response?.data || error.message,
      )
      setMessage('Error processing payment.')
    }
  }

  return (
    <div className="pending-cash-payments">
      <h2>Pending Cash Payments</h2>
      {message && <p>{message}</p>}
      <table>
        <thead>
          <tr>
            <th>User Email</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          {cashPayments.map((payment) => (
            <tr key={payment.id}>
              <td>{payment.user_email}</td>
              <td>{payment.amount}</td>
              <td>{payment.status}</td>
              <td>
                <button
                  onClick={() => handleApproval(payment.id, 'Approved')}
                  className="approve-btn"
                >
                  Approve
                </button>
                <button
                  onClick={() => handleApproval(payment.id, 'Rejected')}
                  className="reject-btn"
                >
                  Reject
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

export default PendingCashPayments
