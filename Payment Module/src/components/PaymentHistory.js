import React, { useEffect, useState} from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import '../styles/PaymentHistory.css';

const PaymentHistory = () => {
    const [payments, setPayments] = useState([]);
    const navigate = useNavigate();
    const userEmail = localStorage.getItem('userEmail');

    useEffect(() => {
        if (!userEmail) return;
        axios.get(`http://localhost:5000/user/payments?email=${userEmail}`)
            .then(response => setPayments(response.data))
            .catch(error => console.error("Error fetching payments: ", error));
    }, [userEmail]);

    const viewDetails = (paymentId) => {
        navigate(`/payment-details/${paymentId}`);
    };

    return (
        <div className='payment-history-container'>
            <h2>Payment History</h2>
            {payments.length === 0 ? (
                <p>No transactions found.</p>
            ) : (
                <table className='payment-history-table'>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {payments.map(payment => (
                            <tr key = {payment.id}>
                                <td>{payment.transaction_id}</td>
                                <td>{payment.amount}</td>
                                <td>{payment.payment_status}</td>
                                <td>{new Date(payment.created_at).toLocaleString()}</td>
                                <td>
                                    <button onClick={() => viewDetails(payment.id)}>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
};

export default PaymentHistory