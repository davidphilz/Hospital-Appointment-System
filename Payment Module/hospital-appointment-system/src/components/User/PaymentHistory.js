import React, { useEffect, useState} from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import '../../styles/User/PaymentHistory.css';

const PaymentHistory = () => {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const userEmail = localStorage.getItem("userEmail"); // Store user email from local storage
    const navigate = useNavigate();

    useEffect(() => {
        fetchTransactions();
        const interval = setInterval(fetchTransactions, 5000); //Auto-refresh every 5 seconds
        return () => clearInterval(interval);
    }, []);

    const fetchTransactions = () => {
        axios.get(`http://localhost:5000/user/transactions?email=${userEmail}`)
            .then((response) => {
                setTransactions(response.data);
                setLoading(false);
            })
            .catch((error) => {
                console.error("Error fetching transactions: ", error);
                setError("Failed to load transactions.");
                setLoading(false);
            })
    }

    return (
        <div className='payment-history'>
            <h2>Transaction History</h2>
            {loading ? (
                <p>Loading transactions...</p>
            ) : error ? (
                <p className='error'>{error}</p>
            ) : transactions.length === 0 ? (
                <p>No transactions found.</p>
            ) : (
                <table>
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        {transactions.map((transaction) => (
                            <tr key={transaction.id}>
                                <td>NGN{transaction.amount}</td>
                                <td>{transaction.payment_method || "N/A"}</td>
                                <td>{transaction.status}</td>
                                <td>{new Date(transaction.created_at).toLocaleString()}</td>
                                <td>
                                    <button onClick={() => navigate(`/payment-details?transaction_id=${transaction.id}`)}>
                                        View Receipt Details
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