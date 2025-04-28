import React, {useEffect} from 'react'
import { useNavigate } from 'react-router-dom'
import {toast} from 'react-toastify'

const Success = () => {
  const navigate = useNavigate()
  useEffect(() => {
    toast.success('Payment Successful')
  }, [])
  

  return (
    <div style={{ textAlign: 'center', padding: '50px' }}>
      <h2>Payment Successful!</h2>
      <p>Thank you for your payment.</p>
      <button onClick={() => navigate('/dashboard')}>Go to Dashboard</button>
    </div>
  )
}

export default Success
