import React, { useEffect, useState } from 'react'
import axios from 'axios';
import '../../styles/Admin/AdminTransactionsHistory.css';

const AdminTransactionsHistory = () => {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('')

    useEffect(() => {
        fetchTransactions();
    }, [])

    const fetchTransactions = () => {
        axios.get('http://localhost:5000/admin/transactions')
            .then(response => {
                setTransactions(response.data);
                setLoading(false);
            }) 
            .catch(error => {
                console.error("Error fetching transactions:", error);
                setError("Failed to load transactions.");
                setLoading(false);
            })
    };

    return (
        <div className='admin-transaction-history'>
            <h2> All Transactions </h2>
            {loading ? (<p>Loading Transactions...</p>) : error ? (<p className='error'>{error}</p>) : transactions.length === 0 ? (<p>No transactions found.</p>): (
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        {transactions.map((transactions) => (
                            <tr key={transactions.id}>
                                <td>{transactions.name || "Unknown"}</td>
                                <td>NGN{transactions.amount}</td>
                                <td>{transactions.payment_method}</td>
                                <td>{transactions.status}</td>
                                <td>{new Date(transactions.created_at).toLocaleString()}</td>
                            </tr>
                        ))}
                    </tbody>
                        
                </table>
            )}
        </div>
    )
}

export default AdminTransactionsHistory;