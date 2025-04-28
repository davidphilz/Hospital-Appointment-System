import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../../styles/Admin/AdminHMOProcessing.css';

const HMOProcessing = () => {
    const [pendingRequests, setPendingRequests] = useState([]);
    const [message, setMessage] = useState('');

    useEffect(() => {
        fetchPendingRequests();
    }, []);

    //Fetch all pending HMO requests
    const fetchPendingRequests = () => {
        axios.get('http://localhost:5000/admin/hmo-requests')
            .then(response => setPendingRequests(response.data))
            .catch(error => console.error("Error fetching HMO requests:", error));
    };

    // Handle claim processing
    const processClaim = (requestId) => {
        console.log("Processing cllaim for request ID ", requestId); //Debugging
        const adminEmail = localStorage.getItem('userEmail');

        axios.post('http://localhost:5000/admin/process-hmo-claim', { request_id: requestId, admin_email: adminEmail})
            .then(response => {
                setMessage(response.data.message);
                fetchPendingRequests(); // Refresh the list after processing
            })
            .catch(error => {
                console.error("Error processing HMO claim: ", error.response?.data || error.message);
                setMessage("Error processing HMO claim")});
    };

    return (
        <div className='hmo-processing'>
            <h2> HMO Payment Processing </h2>
            { message && <p className='message'>{message}</p>}

            {pendingRequests.length > 0 ? (
                <table>
                    <thead>
                        <tr>
                            <th>Patient Email</th>
                            <th>Phone Number</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {pendingRequests.map((request) => (
                            <tr key={request.request_id}>
                                <td>{request.patient_email}</td>
                                <td>{request.patient_phone}</td>
                                <td>NGN {request.amount}</td>
                                <td>{request.request_status}</td>
                                <td>
                                    {request.request_status === "Pending" && (
                                        
                                        <button className='claim' onClick={() => processClaim(request.request_id)}>
                                            File Claim 
                                        </button>
                                        
                                        
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            ) : (
                <p> No pending HMO requests.</p>
            )}
        </div>
    );
};

export default HMOProcessing;