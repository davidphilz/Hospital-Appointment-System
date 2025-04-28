import React, { useState, useEffect } from 'react'
import axios from 'axios'
import { NavLink, Outlet } from 'react-router-dom'
import {
  FaTachometerAlt,
  FaMoneyBillWave,
  FaUniversity,
  FaWallet,
  FaNotesMedical,
  FaHistory,
  FaBars,
} from 'react-icons/fa'
import '../../styles/Admin/AdminDashboardLayout.css'

const AdminDashboardLayout = () => {
  const [isCollapsed, setIsCollapsed] = useState(true)

  return (
    <div className="admin-dashboard-container">
      {/* Sidebar */}

      <aside
        className={`admin-sidebar ${isCollapsed ? 'collapsed' : ''}`}
        onMouseEnter={() => setIsCollapsed(false)}
        onMouseLeave={() => setIsCollapsed(true)}
      >
        <div className="sidebar-header">
          <FaBars className="menu-icon" />
          {!isCollapsed && <h2>Admin Dashboard</h2>}
        </div>
        <nav>
          <ul>
            <li>
              <NavLink
                to="/admin-dashboard/overview"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                <FaTachometerAlt />
                {!isCollapsed && 'Overview'}
              </NavLink>
            </li>
            <li>
              <NavLink
                to="/admin-dashboard/bank-details"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                <FaUniversity />
                {!isCollapsed && 'Bank Details'}
              </NavLink>
            </li>
            <li>
              <NavLink
                to="/admin-dashboard/pending-transfers"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                <FaMoneyBillWave />
                {!isCollapsed && 'Pending Transfers'}
              </NavLink>
            </li>
            <li>
              <NavLink
                to="/admin-dashboard/pending-cash-payments"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                <FaWallet />
                {!isCollapsed && 'Pending Cash Payments'}
              </NavLink>
            </li>
            <li>
              <NavLink
                to="/admin-dashboard/hmo-processing"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                <FaNotesMedical />
                {!isCollapsed && 'HMO Processing'}
              </NavLink>
            </li>
            {/*<li>
              <NavLink
                to="/admin-dashboard/refunds"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                <FaHistory />
                {!isCollapsed && 'Refunds'}
              </NavLink>
            </li>*/}
            <li>
              <NavLink
                to="/admin-dashboard/admin-transaction-history"
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                <FaHistory />
                {!isCollapsed && 'History'}
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

export default AdminDashboardLayout
