import React, { useState } from 'react'
import axios from 'axios'
import { useNavigate } from 'react-router-dom'
import '../../styles/Auth/UnifiedLoginSignup.css'

const Auth = () => {
  // State for toggling between login and sign-up
  const [isSignup, setIsSignup] = useState(false)

  // Input fields states
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [role, setRole] = useState('user') // Default role is 'user'
  const [hospitalRole, setHospitalRole] = useState('') // For admin role
  const [adminCode, setAdminCode] = useState('') // Admin security code
  const [error, setError] = useState('') // To store error messages
  const [loading, setLoading] = useState(false) // To show loading state

  const navigate = useNavigate()

  // Function to switch between login and signup forms
  const toggleMode = () => {
    setIsSignup(!isSignup)
    setError('') // Clear errors when switching
  }

  // Function to handle form submission (Login & Signup)
  const handleSubmit = async (e) => {
    e.preventDefault() // Prevent default form submission
    setLoading(true)
    setError('')

    try {
      let data = { email, password } // Default data for login
      let url = 'http://localhost:5000/login' // Default API for login

      // If user is signing up
      if (isSignup) {
        data = {
          name,
          email,
          password,
          ...(role === 'admin' && { hospital_role: hospitalRole, adminCode }),
        }
        url =
          role === 'admin'
            ? 'http://localhost:5000/admin/register'
            : 'http://localhost:5000/user/register'
      }

      const response = await axios.post(url, data) // Sending request to backend
      alert(response.data.message) // Show success message

      if (response.data) {
        const userRole = response.data.role // e.g admin or user

        // Store email based on role after login
        localStorage.setItem('user', JSON.stringify(response.data))

        //Navigate based on role
        if (userRole === 'admin') {
          navigate('/admin-dashboard')
        } else {
          navigate("/user-dashboard")
        }
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Something went wrong') // Handle errors
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="auth-container">
      {/* Main Card Container with flipping effect */}
      <div className={`auth-card ${isSignup ? 'flip' : ''}`}>
        {/* FRONT: Login Form */}
        <div className="auth-side front">
          <h4 className="auth-title">Login</h4>
          {error && <p className="auth-error">{error}</p>}
          <form onSubmit={handleSubmit}>
            <label className="auth-input-group">
              <span>@</span> {/* Email Icon */}
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
                <path d="M80 192V144C80 64.47 144.5 0 224 0C303.5 0 368 64.47 368 144V192H384C419.3 192 448 220.7 448 256V448C448 483.3 419.3 512 384 512H64C28.65 512 0 483.3 0 448V256C0 220.7 28.65 192 64 192H80zM144 192H304V144C304 99.82 268.2 64 224 64C179.8 64 144 99.82 144 144V192z"></path>
              </svg>{' '}
              {/* Password Icon */}
              <input
                type="password"
                placeholder="Password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </label>
            <button type="submit" className="auth-btn" disabled={loading}>
              {loading ? 'Logging in...' : 'Login'}
            </button>
          </form>
          <p className="auth-toggle" onClick={toggleMode}>
            New here? Sign up
          </p>
        </div>

        {/* BACK: Signup Form */}
        <div className="auth-side back">
          <h4 className="auth-title">Sign Up</h4>
          {error && <p className="auth-error">{error}</p>}
          <form onSubmit={handleSubmit}>
            <label className="auth-input-group">
            <span className="input-icon">👤</span>
              {/* Name Icon */}
              <input
                type="text"
                placeholder="Full Name"
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
              />
            </label>
            <label className="auth-input-group">
            <span className="input-icon">@</span>
              {/* Email Icon */}
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
                <path d="M80 192V144C80 64.47 144.5 0 224 0C303.5 0 368 64.47 368 144V192H384C419.3 192 448 220.7 448 256V448C448 483.3 419.3 512 384 512H64C28.65 512 0 483.3 0 448V256C0 220.7 28.65 192 64 192H80zM144 192H304V144C304 99.82 268.2 64 224 64C179.8 64 144 99.82 144 144V192z"></path>
              </svg>{' '}
              {/* Password Icon */}
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
                <span className="input-icon">🏥</span>
                  {/* Hospital Role Icon */}
                  <input
                    type="text"
                    placeholder="Hospital Role"
                    value={hospitalRole}
                    onChange={(e) => setHospitalRole(e.target.value)}
                    required
                  />
                </label>
                <label className="auth-input-group">
                <span className="input-icon">🔒</span>
                  {/* Security Code Icon */}
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
          <p className="auth-toggle" onClick={toggleMode}>
            Already have an account? Login
          </p>
        </div>
      </div>
    </div>
  )
}

export default Auth
