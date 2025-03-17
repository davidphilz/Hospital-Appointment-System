import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../styles/PendingTransfers.css';

const PendingTransfers = () => {
    const [pendingTransfers, setPendingTransfers] = useState([]);
    const [error, setError] = useState('');
    const [message, setMessage] = useState('');

    //Fetch pending transfers when component mounts
    useEffect(() => {
        fetchTransfers();
    }, []);

    const fetchTransfers = () => {
        axios.get('http://localhost:5000/admin/pending-transfers')
            .then(response => setPendingTransfers(response.data))
            .catch(error => {
                console.error("Error fetching pending transfers: ", error);
                setError("Error fetching pending transfers");
            });
    };

    const handleApproval = (transferId, status) => {
        axios.post('http://localhost:5000/admin/approve-bank-transfer', { transfer_id: transferId, status })
            .then(response => {
                if (response.data.success) {
                    fetchTransfers(); // Fetch updated pending transfers
                    setMessage(response.data.message);
                } else {
                    setError("Error approving transfer");
                }
            })
            .catch(error => {
                console.error("Error processing transfer: ", error.response?.data || error.message);
                setError("Error processing transfer");
            });
    };
    
    return (
      <div className="pending-transfers-container">
        <h2>Pending Bank Transfer</h2>
        {error && <p className="error-message">{error}</p>}
        {message && <p className='status-message'>{message}</p>}
        {pendingTransfers.length > 0 ? (
            <table className='transfers-table'>
                <thead>
                    <tr>
                        <th>User Email</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {pendingTransfers.map(transfer => (
                        <tr key={transfer.transfer_id}>
                            <td>{transfer.user_email}</td>
                            <td>{transfer.amount}</td>
                            <td>
                                <button onClick={() => handleApproval(transfer.id, 'Approved')}>Approve</button>
                                <button onClick={() => handleApproval(transfer.id, 'Rejected')}>Reject</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
                
            </table>
        ) : (
            <p> No pending transfers found. </p>
        )}
      </div>
    );



};

export default PendingTransfers;