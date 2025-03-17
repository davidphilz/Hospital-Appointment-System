import React, { useState } from 'react'
import axios from 'axios'
import { NavLink, Outlet } from 'react-router-dom'
import '../styles/UserDashboard.css'

const UserDashboard = () => {
  return (
    <div className="user-dashboard-container">
      <aside className="user-sidebar">
        <h2>User Dashboard</h2>
        <nav>
          <ul>
            <li>
              <NavLink
                to="payment"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                Flutterwave Payment
              </NavLink>
            </li>
            <li>
              <NavLink
                to="bank-transfer"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                Offline Bank Transfer Payment
              </NavLink>
            </li>
            <li>
              <NavLink
                to="payment-history"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                Payment History
              </NavLink>
            </li>
          </ul>
        </nav>
      </aside>
      <main className="user-content">
        <Outlet />
      </main>
    </div>
  )
}

export default UserDashboard
