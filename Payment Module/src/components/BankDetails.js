import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../styles/BankDetails.css';

const BankDetails = () => {
    const [bankDetails, setBankDetails] = useState(null);
    const [formData, setFormData] = useState({
        bank_name: '',
        account_number: '',
        account_name: ''
    });

    const [error, setError] = useState('');
    const [message, setMessage] = useState('');

    //Fetch bank details when component mounts
    useEffect(() => {
        axios.get('http://localhost:5000/bank-details')
            .then(response => {
                setBankDetails(response.data);

                setFormData({
                    bank_name: response.data.bank_name,
                    account_number: response.data.account_number,
                    account_name: response.data.account_name
                });
            })
            .catch(error => {
                console.error("Error fetching bank details:", error);
                setError('Error fetching bank details');
            });
    }, []);

    const handleSubmit = (e) => {
        e.preventDefault();
        setError('');
        setMessage('');

        axios.post('http://localhost:5000/admin/update-bank-details', formData)
            .then(response => {
                setMessage(response.data.message);
            })
            .catch(error => {
                console.error("Error updating bank details:", error.response?.data || error.message);
                setError("Error updating bank details.");
            });
    };

    return (
      <div className="bank-details-container">
        <h2>Bank Details</h2>
        {error && <p className="error-message">{error}</p>}
        {message && <p className="status-message">{message}</p>}
        {bankDetails ? (
            <form onSubmit={handleSubmit}>
                <input
                    type='text'
                    placeholder='Bank Name'
                    value={formData.bank_name}
                    onChange={(e) => setFormData({ ...formData, bank_name: e.target.value })}
                    required
                />
                <input
                    type='text'
                    placeholder='Account Number'
                    value={formData.account_number}
                    onChange={(e) => setFormData({ ...formData, account_number: e.target.value })}
                    required
                />
                <input
                    type='text'
                    placeholder='Account Name'
                    value={formData.account_name}
                    onChange={(e) => setFormData({ ...formData, account_name: e.target.value })}
                    required
                />
                <button type='submit'>Update Bank Details</button>
            </form>
        ) : (
            <p>Loading bank details...</p>
        )}
      </div>
    );

};

export default BankDetails;