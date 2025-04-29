import React, { useEffect, useState } from 'react'
import { useParams, useNavigate } from 'react-router-dom'

const PaymentConfirmation = () => {
  const { email } = useParams() // Get the email from URL params
  const navigate = useNavigate() // use the history to navigate
  const [status, setStatus] = useState("Pending"); // Default status after initiating a payment

  useEffect(() => {
    const checkPaymentStatus = async () => {
        try {
            const response = await fetch(`http://localhost:5000/check-pending-status/${email}`);
            const data = await response.json();

            if (data.status !== "Pending") {
                setStatus(data.status); // update status with API response
            }
        } catch (error) {
            console.error("Error checking payment status:", error);
        }
    }

    // Check status every 5 seconds
    const intervalId = setInterval(checkPaymentStatus, 5000);

    // Cleanup the interval when the component unmounts
    return () => clearInterval(intervalId);
  }, [email]);

  useEffect(() => {
    if (status === 'Completed') {
      navigate('/payment-success') // Redirect to success page
    } else if (status === 'Failed') {
      navigate('/payment-failed') // Redirect to failed page
    }
  }, [status]);

  return (
    <div>
      <h2> Payment Confirmation</h2>
      <p>Waiting for Admin approval... </p>
      <p>Status: {status}</p> {/*Display current payment status */}
    </div>
  )

}
export default PaymentConfirmation