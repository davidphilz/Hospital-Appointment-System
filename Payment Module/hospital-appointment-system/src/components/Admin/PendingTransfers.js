import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../../styles/Admin/PendingTransfers.css';

const PendingTransfers = () => {
    const [pendingTransfers, setPendingTransfers] = useState([]);
    const [error, setError] = useState('');
    const [message, setMessage] = useState('');
    const [loading, setLoading] = useState(true);

    //Fetch pending transfers when component mounts
    useEffect(() => {
        fetchTransfers();
    }, []);

    const fetchTransfers = () => {
        setLoading(true); 
        axios.get('http://localhost:5000/admin/pending-transfers')
            .then(response => {
                setPendingTransfers(response.data);
                setLoading(false);
            })
            .catch(error => {
                console.error("Error fetching pending transfers: ", error);
                setError("Error fetching pending transfers");
                setLoading(false);
            });
    };

    const handleApproval = (transferId, status) => {
        console.log("Approving transfer - ID:", transferId, "Status: ", status);
        axios.post('http://localhost:5000/admin/approve-bank-transfer', { transfer_id: transferId, status })
            .then(response => {
                setMessage(response.data.message || `Transfer ${status.toLowerCase()} successfully.`);

                // Remove the approved/rejected transfer from the list
                setPendingTransfers(prevTransfers => prevTransfers.filter(transfer => transfer.id !== transferId));
            })
            .catch(error => {
                console.error("Error processing transfer: ", error.response?.data || error.message);
                setError("Error processing transfer");
            });
    };
    
    return (
      <div className="pending-transfers-container">
        <h2>Pending Bank Transfer</h2>
        {loading ? <p>Loading pending tranfers...</p> : null} 

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
                        <tr key={transfer.id}>
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
            !loading && <p>No pending transfers found.</p>
        )}
      </div>
    );
};

export default PendingTransfers;