import React from 'react'
import { useNavigate } from 'react-router-dom'
import '../styles/Dashboard.css'

const Dashboard = () => {
  const navigate = useNavigate()

  const handlelogout = () => {
    localStorage.removeitem('token') //remove the authentication token
    alert('Logged out successfully!')
    navigate('/login')
  }

  return (
    <div className="dashboard-container">
      <div className="dashboard-box">
        <h2>Welcome to Your Dashboard</h2>
        <p>This is a protected page for logged-in users.</p>
        <button className="logout-button" onclick={handlelogout}>
          Logout
        </button>
      </div>
    </div>
  )
}

export default Dashboard
