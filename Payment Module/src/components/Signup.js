import React, { useState } from 'react'
import axios from 'axios'
import { useNavigate } from 'react-router-dom'
import '../styles/Signup.css'

const Signup = () => {
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [role, setRole] = useState('user') // Default is "user"
  const [hospitalRole, setHospitalRole] = useState('') // for admin only
  const [adminCode, setAdminCode] = useState('') // Security code for admins
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false) // Loading state
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
        ...(role === 'admin' && {
          hospital_role: hospitalRole,
          adminCode: adminCode.trim(),
        }),
      }

      const url =
        role === 'admin'
          ? 'http://localhost:5000/admin/register'
          : 'http://localhost:5000/user/register'

      const response = await axios.post(url, data)

      alert(response.data.message)
      navigate('/login')
    } catch (err) {
      console.error('Signup Error:', err.response?.data || err.message)
      setError(
        err.response?.data?.message || 'Signup failed. Please try again.',
      )
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="signup-container">
      <h2>Sign Up</h2>
      {error && <p style={{ color: 'red' }}>{error}</p>}
      <form onSubmit={handleSignup}>
        <input
          type="text"
          placeholder="Name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
        />
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />

        <select value={role} onChange={(e) => setRole(e.target.value)}>
          <option value="user">Register as User</option>
          <option value="admin">Register as Admin</option>
        </select>

        {role === 'admin' && (
          <>
            <input
              type="text"
              placeholder="Hospital Role (e.g., Manager)"
              value={hospitalRole}
              onChange={(e) => setHospitalRole(e.target.value)}
              required
            />
            <input
              type="password"
              placeholder="Admin Security Code"
              value={adminCode}
              onChange={(e) => setAdminCode(e.target.value)}
              required
            />
          </>
        )}

        <button type="submit" disabled={loading}>
          {loading ? 'Signing up...' : 'Sign up'}
        </button>
      </form>
    </div>
  )
}

export default Signup
