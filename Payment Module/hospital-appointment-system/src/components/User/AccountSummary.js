import React, { useEffect, useState } from 'react';
import axios from 'axios';
import '../../styles/User/AccountSummary.css';

const AccountSummary = () => {
    const [userData, setUserData] = useState({ userName: '', balance: "0.00", totalPayments: "0.00" });
    const[loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        const fetchUserDetails = async () => {
          const userEmail = localStorage.getItem("userEmail");
      
          if (!userEmail) {
            setError("User not logged in.");
            setLoading(false);
            return;
          }
      
          try {
            console.log("Fetching summary for email:", userEmail);
            const response = await axios.get(`http://localhost:5000/user/account-summary?email=${userEmail}`);
            const user = response.data;
      
            setUserData({
              name: user.name,
              totalPayments: user.total_payments,
              hmoPlan: user.hmo_plan
            });
      
          } catch (error) {
            console.error("Error fetching user details", error);
            setError("Error getting user details.");
          } finally {
            setLoading(false);
          }
        };
      
        fetchUserDetails();
      }, []);

    if (loading) return <p>Loading account details...</p>;
    if (error) return <p className='error-message'>{error}</p>;

    return (
        <div className='account-summary-card'>
            <h2 className='summary-title'>Account Summary</h2>
            <p className='user-name'>{userData.name || "No Name Available"}</p>
            <div className='summary-details'>
                <div className='summary-item'>
                    <span className='label'>Total Payments: </span>
                    <span className='value'>NGN {userData.totalPayments}</span>
                </div>
                <div className='summary-item'>
                    <span className='label'>HMO Plan:</span>
                    <span className='value'>NGN {userData.hmoPlan}</span>
                </div>
            </div>
        </div>
    )
}

export default AccountSummary;