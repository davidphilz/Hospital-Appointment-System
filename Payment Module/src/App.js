import React from 'react'
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import Navbar from './components/Navbar'
import Home from './components/Home'
import Login from './components/Login'
import Signup from './components/Signup'
import Payment from './components/Payment'
import Success from './components/Success'
import Cancel from './components/Cancel'
import AdminDashboard from './components/AdminDashboard'
import AdminDashboardLayout from './components/AdminDashboardLayout'
import UserDashboardLayout from './components/UserDashboardLayout'
import UserDashboard from './components/UserDashboard'
import BankTransfer from './components/BankTransfer'
import Receipt from './components/Receipt'
import PaymentHistory from './components/PaymentHistory'
import PaymentDetails from './components/PaymentDetails'
import AdminOverview from './components/AdminOverview'
import BankDetails from './components/BankDetails'
import PendingTransfers from './components/PendingTransfers'
import Refunds from './components/Refunds'

function App() {
  return (
    <Router>
      <Navbar />
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/login" element={<Login />} />
        <Route path="/Signup" element={<Signup />} />
        <Route path="/user-dashboard" element={<UserDashboard />} />

        {/* Admin Dashboard routes with sidebar layout */}
        <Route path="/admin-dashboard" element={<AdminDashboardLayout />}>
          <Route path="overview" element={<AdminOverview />} />
          <Route path="bank-details" element={<BankDetails />} />
          <Route path="pending-transfers" element={<PendingTransfers />} />
          <Route path="refunds" element={<Refunds />} />
        </Route>

        {/* User Dashboard routes with sidebar layout */}
        <Route path="/user-dashboard" element={<UserDashboardLayout />}>
          <Route path="payment" element={<Payment />} />
          <Route path="bank-transfer" element={<BankTransfer />} />
          <Route path="payment-history" element={<PaymentHistory />} />
          <Route path="payment-details/:id" element={<PaymentDetails />} />
        </Route>

        <Route path="/receipt" element={<Receipt />} />
        <Route path="/flutterwave-success" element={<Success />} />
        <Route path="/flutterwave-cancel" element={<Cancel />} />
      </Routes>
    </Router>
  )
}
export default App
