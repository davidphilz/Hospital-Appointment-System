import React from 'react'
import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom'
import Navbar from './components/User/Navbar'
import Home from './components/Auth/Home'
import Payment from './components/User/Payment'
import Success from './components/Success'
import Cancel from './components/Cancel'
import AdminDashboardLayout from './components/Admin/AdminDashboardLayout'
import UserDashboardLayout from './components/User/UserDashboardLayout'
import BankTransfer from './components/User/BankTransfer'
import Receipt from './components/User/Receipt'
import PaymentHistory from './components/User/PaymentHistory'
import PaymentDetails from './components/User/PaymentDetails'
import AdminOverview from './components/Admin/AdminOverview'
import BankDetails from './components/Admin/BankDetails'
import PendingTransfers from './components/Admin/PendingTransfers'
import Refunds from './components/Admin/Refunds'
import CashPayment from './components/User/CashPayment'
import PendingCashPayments from './components/Admin/PendingCashPayments'
import HMOPayment from './components/User/HMOPayment'
import AdminHMOProcessing from './components/Admin/AdminHMOProcessing'
import UserOverview from './components/User/UserOverview'
import Login from './components/Auth/Login'
import Signup from './components/Auth/Signup'
import PaymentConfirmation from './components/User/PaymentConfirmation'
import PaymentSuccess from './components/User/PaymentSuccess'
import PaymentFailed from './styles/User/PaymentFailed'
import AdminTransactionHistory from './components/Admin/AdminTransactionHistory'

function App() {
  return (
    <Router>
      <Navbar />
      <Routes>
        {/* Public Routes */}
        <Route path="/" element={<Home />} />
        <Route path="/login" element={<Login />} />
        <Route path="/signup" element={<Signup />} />

        {/* Admin Dashboard routes with sidebar layout */}
        <Route path="/admin-dashboard" element={<AdminDashboardLayout />}>
          <Route path="overview" element={<AdminOverview />} />
          <Route path="bank-details" element={<BankDetails />} />
          <Route path="pending-transfers" element={<PendingTransfers />} />
          <Route
            path="admin-transaction-history"
            element={<AdminTransactionHistory />}
          />
          <Route
            path="pending-cash-payments"
            element={<PendingCashPayments />}
          />
          <Route path="refunds" element={<Refunds />} />
          <Route path="hmo-processing" element={<AdminHMOProcessing />} />
        </Route>

        {/* User Dashboard routes with sidebar layout (no longer protected) */}
        <Route path="/user-dashboard/*" element={<UserDashboardLayout />}>
          <Route index element={<UserOverview />} />
          <Route path="payment" element={<Payment />} />
          <Route path="bank-transfer" element={<BankTransfer />} />
          <Route path="payment-history" element={<PaymentHistory />} />
          <Route path="cash-payment" element={<CashPayment />} />
          <Route path="hmo-payment" element={<HMOPayment />} />
        </Route>

        {/* Other public routes */}
        <Route
          path="/payment-confirmation/:email"
          element={<PaymentConfirmation />}
        />
        <Route path="/payment-details" element={<PaymentDetails />} />
        <Route path="/receipt" element={<Receipt />} />
        <Route path="/flutterwave-success" element={<Success />} />
        <Route path="/flutterwave-cancel" element={<Cancel />} />
        <Route path="/payment-success" element={<PaymentSuccess />} />
        <Route path="/payment-failed" element={<PaymentFailed />} />
      </Routes>
    </Router>
  )
}

export default App
