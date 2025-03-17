import React from 'react'
import '../styles/Home.css'

const Home = () => {
  return (
    <div className="home-container">
      <h1 className="home-title">Welcome to the Hospital Payment System</h1>
      <p className="home-description">
        A fast, secure, and convenient way to manage your hospital payments
        online. Pay using credit card, bank transfer, HMO coverage.
      </p>
      <button className="home-button">Get Started</button>
    </div>
  )
}

export default Home
