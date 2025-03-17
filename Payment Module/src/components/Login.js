import React, { useState } from 'react'
import '../styles/Login.css'
import axios from 'axios'
import { useNavigate } from 'react-router-dom'

const Login = () => {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const handleLogin = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')

    try {
      // Send a POST request to the unified login route
      const response = await axios.post('http://localhost:5000/login', {
        email,
        password,
      })
      console.log('Response received:', response.data)

      // Redirect based on returned role
      if (response.data.role === 'admin') {
        localStorage.setItem('userEmail', response.data.admin.email) // Used to store the email used in making initiating a payment for admin
        navigate('/admin-dashboard')
      } else if (response.data.role === 'user') {
        localStorage.setItem('userEmail', response.data.user.email) // Used to store the email used in making initiating a payment for user
        navigate('/user-dashboard')
      }

      alert(response.data.message)
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="login-container">
      <div className="login-box">
        <h2>Login</h2>
        {error && <p className="error-message">{error}</p>}
        <form onSubmit={handleLogin}>
          <input
            type="email"
            placeholder="Enter your email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
          <input
            type="password"
            placeholder="Enter your password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />

          <button type="submit" className="login-button" disabled={loading}>
            {loading ? 'Logging in...' : 'Login'}
          </button>
        </form>
      </div>
    </div>
  )
}

export default Login
