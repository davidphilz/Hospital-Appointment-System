import React, { useState } from 'react'
import axios from 'axios'
import { useNavigate } from 'react-router-dom'
import '../../styles/Auth/Signup.css';

const Signup = () => {
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [role, setRole] = useState('user')
  const [hospitalRole, setHospitalRole] = useState('')
  const [adminCode, setAdminCode] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const handleSignup = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')

    try {
      const data = {
        name,
        email,
        password,
        ...(role === 'admin' && { hospital_role: hospitalRole, adminCode }),
      }

      const url =
        role === 'admin'
          ? 'http://localhost:5000/admin/register'
          : 'http://localhost:5000/patient/register'; // Updated to use 'patient/register'

      const response = await axios.post(url, data)

      alert(response.data.message)
      localStorage.setItem('user', JSON.stringify(response.data))

      if (response.data.role === 'admin') {
        navigate('/admin-dashboard')
      } else {
        navigate('/user-dashboard')
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Something went wrong')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="auth-container">
      <div className="auth-card">
        <h4 className="auth-title">Sign Up</h4>
        {error && <p className="auth-error">{error}</p>}
        <form onSubmit={handleSignup}>
          <label className="auth-input-group">
            <span>👤</span>
            <input
              type="text"
              placeholder="Full Name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
          </label>
          <label className="auth-input-group">
            <span>@</span>
            <input
              type="email"
              placeholder="Email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </label>
          <label className="auth-input-group">
            <svg className="input-icon" viewBox="0 0 500 500">
              <path d="..." />
            </svg>
            <input
              type="password"
              placeholder="Create Password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </label>
          <select
            value={role}
            onChange={(e) => setRole(e.target.value)}
            className="auth-select"
          >
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
          {role === 'admin' && (
            <>
              <label className="auth-input-group">
                <span>🏥</span>
                <input
                  type="text"
                  placeholder="Hospital Role"
                  value={hospitalRole}
                  onChange={(e) => setHospitalRole(e.target.value)}
                  required
                />
              </label>
              <label className="auth-input-group">
                <span>🔒</span>
                <input
                  type="password"
                  placeholder="Admin Security Code"
                  value={adminCode}
                  onChange={(e) => setAdminCode(e.target.value)}
                  required
                />
              </label>
            </>
          )}
          <button type="submit" className="auth-btn" disabled={loading}>
            {loading ? 'Signing up...' : 'Sign Up'}
          </button>
        </form>
      </div>
    </div>
  )
}

export default Signup
