import React, { useState, useEffect } from "react";
import AccountSummary from "./AccountSummary";
import axios from "axios";
import "../../styles/User/UserOverview.css";

const UserOverview = () => {
  const [recentTransactions, setRecentTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchTransactions = async () => {
      const userEmail = localStorage.getItem("userEmail");
      if (!userEmail) {
        setError("User not logged in.");
        setLoading(false);
        return;
      }

      try {
        const response = await axios.get(
          `http://localhost:5000/user/recent-transactions?email=${userEmail}`
        );
        setRecentTransactions(response.data);
      } catch (error) {
        setError("Error fetching transactions.");
      } finally {
        setLoading(false);
      }
    };

    fetchTransactions();
  }, []);

  return (
    <div className="user-overview">
      <h1 className="dashboard-title">Dashboard Overview</h1>

      {/* Account Summary Section */}
      <AccountSummary />

      {/* Recent Transactions Table */}
      <div className="recent-transactions">
        <h2>Recent Transactions</h2>
        {loading ? (
          <p>Loading transactions...</p>
        ) : error ? (
          <p className="error-message">{error}</p>
        ) : recentTransactions.length > 0 ? (
          <table className="transaction-table">
            <thead>
              <tr>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              {recentTransactions.map((transaction, index) => (
                <tr key={index}>
                  <td>NGN {transaction.amount}</td>
                  <td>{transaction.payment_method}</td>
                  <td>{transaction.status}</td>
                  <td>{new Date(transaction.created_at).toLocaleString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <p className="no-transactions">No recent transactions found.</p>
        )}
      </div>
    </div>
  );
};

export default UserOverview;
