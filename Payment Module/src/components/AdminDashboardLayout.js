import React from 'react'
import { NavLink, Outlet} from 'react-router-dom'
import '../styles/AdminDashboardLayout.css'

const AdminDashboardLayout = () => {
    return (
      <div className="admin-dashboard-container">
        <aside className="admin-sidebar">
          <h2>Admin Dashboard</h2>
          <nav>
            <ul>
              <li>
                <NavLink
                  to="/admin-dashboard/overview"
                  className={({ isActive }) => (isActive ? 'active' : '')}
                >
                  Overview
                </NavLink>
              </li>
              <li>
                <NavLink
                  to="/admin-dashboard/bank-details"
                  className={({ isActive }) => (isActive ? 'active' : '')}
                >
                  Bank Details
                </NavLink>
              </li>
              <li>
                <NavLink
                  to="/admin-dashboard/pending-transfers"
                  className={({ isActive }) => (isActive ? 'active' : '')}
                >
                  Pending Transfers
                </NavLink>
              </li>
              <li>
                <NavLink
                  to="/admin-dashboard/refunds"
                  className={({ isActive }) => (isActive ? 'active' : '')}
                >
                  Refunds
                </NavLink>
              </li>
            </ul>
          </nav>
        </aside>
        <main className='admin-content'>
            <Outlet />
        </main>
      </div>
    );
};

export default AdminDashboardLayout;