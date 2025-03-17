import React from 'react'
import { Link } from 'react-router-dom'
import '../styles/Navbar.css'
//import CSS file

const Navbar = () => {
  return (
    <div className="navbar">
      <h1>Hospital Payment System</h1>
      <div className="nav-links">
        <Link to="/login">
          <button>Login</button>
        </Link>
        <Link to="/signup">
          <button>Sign Up</button>
        </Link>
        <Link to="/">
          <button>Home</button>
        </Link>
      </div>
    </div>
  )
}
export default Navbar
