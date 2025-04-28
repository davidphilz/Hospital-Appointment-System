import React, { useEffect, useState } from 'react'
import { useLocation } from 'react-router-dom'
import { jsPDF } from 'jspdf'
import '../../styles/User/Receipt.css'

const useQuery = () => {
  return new URLSearchParams(useLocation().search)
}

const Receipt = () => {
  const query = useQuery()
  const [receiptData, setReceiptData] = useState({
    transaction_id: query.get('transaction_id') || 'N/A',
    tx_ref: query.get('tx_ref') || 'N/A',
    status: query.get('status') || 'N/A',
    amount: query.get('amount') || 'N/A',
    email: query.get('email') || 'N/A',
  })

  // Function to generate and download PDF using jsPDF
  const downlaodReceipt = () => {
    const doc = new jsPDF()

    doc.setFontSize(12)
    doc.text(`Transaction ID: ${receiptData.transaction_id}`, 20, 40)
    doc.text(`Reference: ${receiptData.tx_ref}`, 20, 50)
    doc.text(`Status: ${receiptData.status}`, 20, 60)
    doc.text(`Amount: ${receiptData.amount}`, 20, 70)
    doc.text(`Customer Email: ${receiptData.email}`, 20, 80)

    // Save the PDF with a filename
    doc.save('receipt.pdf')
  }

  return (
    <div className="receipt-container">
      <h2>Payment Receipt</h2>
      <div className="receipt-content">
        <p>
          <strong>Transaction ID: </strong> {receiptData.transaction_id}
        </p>
        <p>
          <strong>Reference: </strong> {receiptData.tx_ref}
        </p>
        <p>
          <strong>Status: </strong> {receiptData.status}
        </p>
        <p>
          <strong>Amount: </strong> {receiptData.amount}
        </p>
        <p>
          <strong>Customer Email: </strong> {receiptData.email}
        </p>
      </div>
      <button className="download-button" onClick={downlaodReceipt}>
        {' '}
        Download Receipt
      </button>
    </div>
  )
}

export default Receipt
