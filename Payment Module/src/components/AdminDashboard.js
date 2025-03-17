import React, { useState, useEffect } from 'react'
import axios from 'axios'
import { NavLink, Outlet } from 'react-router-dom';
import '../styles/AdminDashboard.css'

const AdminDashboard = () => {
  return (
    <div className="admin-dashboard-container">
      <aside className="admin-sidebar">
        <h2>Admin Dashboard</h2>
        <nav>
          <ul>
            <li>
              <NavLink
                to="overview"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                Overview
              </NavLink>
            </li>
            <li>
              <NavLink
                to="bank-details"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                Bank Details
              </NavLink>
            </li>
            <li>
              <NavLink
                to="pending-transfers"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                Pending Transfers
              </NavLink>
            </li>
            <li>
              <NavLink
                to="refunds"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                Refunds
              </NavLink>
            </li>
          </ul>
        </nav>
      </aside>
      <main className="admin-content">
        <Outlet />
      </main>
    </div>
  )
}

export default AdminDashboard;
