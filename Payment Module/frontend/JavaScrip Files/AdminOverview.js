import React, { useEffect, useState } from 'react';
import axios from 'axios'
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer} from 'recharts';
import '../../styles/Admin/AdminOverview.css'

const PaymentChart = () => {
  const [chartData, setChartData] = useState([]);

  useEffect(() => {
    const fetchChartData = async () => {
      try {
        const response = await axios.get('http://localhost:5000/admin/payments-daily-summary');
        setChartData(response.data);
      } catch (error) {
        console.error('Error fetching chart data:', error);
      }
    };

    fetchChartData();
  }, []);

  return (
    <div className='payment-chart-container'>
      <h3>Payment Trends (Last 7 Days)</h3>
      <ResponsiveContainer width="100%" height={300}>
        <LineChart data={chartData}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis dataKey="total" />
          <Tooltip />
          <Line type="monotone" dataKey="total"  strokeWidth={2} />
        </LineChart>
        </ResponsiveContainer>
    </div>
  )

}
const AdminOverview = () => {
  const storedUser = JSON.parse(localStorage.getItem('user'))

  //Destructure safely
  const adminData = storedUser?.admin
  const adminName = adminData?.name || 'Admin'
  const hospitalRole = adminData?.hospital_role || 'Admin'
  const role = storedUser?.role || 'admin'

  // Overview Stats Summary
  const [stats, setStats] = useState({
    totalPayments: 0,
    pendingApprovals: 0,
    totalUsers: 0,
    totalRevenue: 0,
  })

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const res = await axios.get(
          'http://localhost:5000/admin/overview-stats',
        )
        setStats(res.data)
      } catch (err) {
        console.error('Error fetching stats: ', err)
      }
    }

    fetchStats()
  }, [])

  return (
    <div className="admin-overview">
      {/* Header section using grid */}
      <header className="admin-header">
        <div className="admin-header_title">
          <h1>Admin Overview</h1>
          <p>
            Welcome back, <span>{adminName}</span>
          </p>
        </div>
        <div className="admin-header_role">
          <p>{hospitalRole}</p>
        </div>
      </header>

      <div className="admin-stats">
        <div className="stats-card">
          <h3>Total Payments</h3>
          <p>{stats.totalPayments}</p>
        </div>
        <div className="stats-card">
          <h3>Pending Approvals</h3>
          <p>{stats.pendingApprovals}</p>
        </div>
        <div className="stats-card">
          <h3>Registered Users</h3>
          <p>{stats.totalUsers}</p>
        </div>
        <div className="stats-card">
          <h3>Total Revenue</h3>
          <p>₦{stats.totalRevenue.toLocaleString()}</p>
        </div>
        
        
      </div>
      
    </div>
    

    
  )
}

export default AdminOverview
