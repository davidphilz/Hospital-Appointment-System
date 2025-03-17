import React from 'react';
import { NavLink, Outlet } from 'react-router-dom';
import '../styles/UserDashboardLayout.css';

const UserDashboardLayout = () => {
    return (
        <div className='dashboard-container'>
            <aside className='sidebar'>
                <h2>User Dashboard</h2>
                <nav>
                    <ul>
                        <li>
                            <NavLink to="/user-dashboard/payment" className={({ isActive}) => isActive ? "active" : ""}>
                                Flutterwave Payment
                            </NavLink>
                        </li>
                        <li>
                            <NavLink to="/user-dashboard/bank-transfer" className={({ isActive}) => isActive ? "active" : ""}>
                                Offline Bank Transfer
                            </NavLink>
                        </li>
                        <li>
                            <NavLink to="/user-dashboard/payment-history" className={({ isActive}) => isActive ? "active" : ""}>
                                Payment History
                            </NavLink>
                        </li>
                        
                    </ul>
                </nav>
            </aside>
            <main className='content'>
                <Outlet />
            </main>
        </div>
    );
};

export default UserDashboardLayout