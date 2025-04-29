import React, { useState } from 'react'
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
import '../../styles/User/UserDashboardLayout.css'

const UserDashboardLayout = () => {
  const [isCollapsed, setIsCollapsed] = useState(true)

  return (
    <div className="user-dashboard-container">
      {/* Sidebar */}
      <aside
        className={`user-sidebar ${isCollapsed ? 'collapsed' : ''}`}
        onMouseEnter={() => setIsCollapsed(false)}
        onMouseLeave={() => setIsCollapsed(true)}
      >
        <div className="sidebar-header">
          {!isCollapsed && <h2>User Dashboard</h2>}
        </div>
        <nav>
          <ul>
            <li>
              <NavLink to="/user-dashboard" className={({ isActive }) => (isActive ? 'active' : '')}>
                <FaTachometerAlt />
                {!isCollapsed && 'Overview'}
              </NavLink>
            </li>
            <li>
              <NavLink to="/user-dashboard/payment" className={({ isActive }) => (isActive ? 'active' : '')}>
                <FaMoneyBillWave />
                {!isCollapsed && 'Flutterwave Payment'}
              </NavLink>
            </li>
            <li>
              <NavLink to="/user-dashboard/bank-transfer" className={({ isActive }) => (isActive ? 'active' : '')}>
                <FaUniversity />
                {!isCollapsed && 'Offline Bank Transfer'}
              </NavLink>
            </li>
            <li>
              <NavLink to="/user-dashboard/cash-payment" className={({ isActive }) => (isActive ? 'active' : '')}>
                <FaWallet />
                {!isCollapsed && 'Cash'}
              </NavLink>
            </li>
            <li>
              <NavLink to="/user-dashboard/hmo-payment" className={({ isActive }) => (isActive ? 'active' : '')}>
                <FaNotesMedical />
                {!isCollapsed && 'HMO Payment'}
              </NavLink>
            </li>
            <li>
              <NavLink to="/user-dashboard/payment-history" className={({ isActive }) => (isActive ? 'active' : '')}>
                <FaHistory />
                {!isCollapsed && 'Payment History'}
              </NavLink>
            </li>
          </ul>
        </nav>
      </aside>

      {/* Main Content */}
      <main className="user-content">
        <Outlet />
      </main>
    </div>
  )
}

export default UserDashboardLayout