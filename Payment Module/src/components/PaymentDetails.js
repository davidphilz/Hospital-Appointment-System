import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useParams} from 'react-router-dom';
import '../styles/PaymentDetails.css';

const PaymentDetails = () => {
    const { id } = useParams();
    const [payment, setPayment] = useState(null);
    const [error, setError] = useState('');

    useEffect(() => {
        axios.get(`http://localhost:5000/user/payment/${id}`)
            .then(response => setPayment(response.data))
            .catch(err => {
                console.error("Error fetching payment details:", err);
                setError("Error fetching payment details.");
            });
    }, [id]);

    if (error) return <p className='error-message'>{error}</p>;
    if (!payment) return <p>Loading payment details...</p>;

    return (
        <div className='payment-details-container'>
            <h2>Payment Details</h2>
            <p><strong>Transaction ID:</strong> {payment.transaction_id} </p>
            <p><strong>Reference:</strong> {payment.tx_ref}</p>
            <p><strong>Status:</strong> {payment.payment_status}</p>
            <p><strong>Amount:</strong> {payment.amount} </p>
            <p><strong>User Email:</strong> {payment.user_email}</p>
            <p><strong>Payment Method:</strong> {payment.payment_method}</p>
            <p><strong>Created At:</strong> {payment.created_at}</p>
        </div>
    );
};

export default PaymentDetails;