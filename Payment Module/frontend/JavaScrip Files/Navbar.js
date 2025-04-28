import React, { useState, useEffect } from 'react';
import { NavLink } from 'react-router-dom';
import { FiMenu, FiX, FiChevronDown } from 'react-icons/fi';
import '../../styles/User/Navbar.css';

const Navbar = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 10);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <nav className={`navbar ${scrolled ? 'scrolled' : ''}`}>
      {/* Logo Section with subtle shine effect */}
      <div className="nav-brand">
        <h1 className="logo-shine">Hospital Payment System</h1>
      </div>

      {/* Desktop Navigation */}
      <ul className="nav-links">
        <li>
          <NavLink 
            to="/" 
            className="nav-item"
            activeClassName="active"
          >
            Home
          </NavLink>
        </li>
        <li>
          <NavLink 
            to="/about" 
            className="nav-item"
            activeClassName="active"
          >
            About
          </NavLink>
        </li>
        <li>
          <NavLink 
            to="/contact" 
            className="nav-item"
            activeClassName="active"
          >
            Contact
          </NavLink>
        </li>
        <div className="auth-buttons">
          <NavLink 
            to="/login" 
            className="login-btn"
            activeClassName="active"
          >
            Login
          </NavLink>
          <NavLink 
            to="/signup" 
            className="signup-btn"
            activeClassName="active"
          >
            Sign Up
          </NavLink>
        </div>
      </ul>

      {/* Mobile Menu Trigger */}
      <button 
        className={`menu-button ${isMenuOpen ? 'open' : ''}`}
        onClick={() => setIsMenuOpen(!isMenuOpen)}
      >
        {isMenuOpen ? <FiX size={24} /> : <FiMenu size={24} />}
      </button>

      {/* Mobile Menu with sliding animation */}
      <div className={`mobile-menu ${isMenuOpen ? 'open' : ''}`}>
        <ul className="mobile-nav-links">
          <li>
            <NavLink 
              to="/" 
              className="nav-item"
              onClick={() => setIsMenuOpen(false)}
              activeClassName="active"
            >
              Home
            </NavLink>
          </li>
          <li>
            <NavLink 
              to="/about" 
              className="nav-item"
              onClick={() => setIsMenuOpen(false)}
              activeClassName="active"
            >
              About
            </NavLink>
          </li>
          <li>
            <NavLink 
              to="/contact" 
              className="nav-item"
              onClick={() => setIsMenuOpen(false)}
              activeClassName="active"
            >
              Contact
            </NavLink>
          </li>
          <div className="mobile-auth-buttons">
            <NavLink 
              to="/login" 
              className="login-btn"
              onClick={() => setIsMenuOpen(false)}
            >
              Login
            </NavLink>
            <NavLink 
              to="/signup" 
              className="signup-btn"
              onClick={() => setIsMenuOpen(false)}
            >
              Sign Up
            </NavLink>
          </div>
        </ul>
      </div>
    </nav>
  );
};

export default Navbar;