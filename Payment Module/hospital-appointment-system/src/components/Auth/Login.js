import React, { useState } from 'react'
import axios from 'axios'
import { useNavigate } from 'react-router-dom'
import '../../styles/Auth/Login.css'

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
      const response = await axios.post('http://localhost:5000/login', {
        email,
        password,
      })

      console.log('Login Response:', response.data)

      localStorage.removeItem('user')
      localStorage.setItem('user', JSON.stringify(response.data))

      // ✅ This is the missing piece
      localStorage.setItem('userEmail', response.data.user?.email || email)

      console.log('Saved to localStorage:', localStorage.getItem('user'))
      alert(response.data.message)

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
        <h4 className="auth-title">Login</h4>
        {error && <p className="auth-error">{error}</p>}
        <form onSubmit={handleLogin}>
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
      </div>
    </div>
  )
}

export default Login
