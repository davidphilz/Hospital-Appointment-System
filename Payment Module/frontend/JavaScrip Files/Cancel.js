import React, {useEffect} from 'react'
import { useNavigate } from 'react-router-dom'
import { toast } from 'react-toastify'

const Cancel = () => {
  const navigate = useNavigate()

  useEffect(() => {
    toast.error(' Payment failed or was cancelled')
  })

  return (
    <div style={{ textAlign: 'center', padding: '50px' }}>
      <h2> Payment Cancelled</h2>
      <p>Your payment was not completed. </p>
      <button onClick={() => navigate('/payment')}>Try Again</button>
    </div>
  )
}

export default Cancel
