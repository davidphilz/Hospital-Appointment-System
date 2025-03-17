require('dotenv').config()
const express = require('express')
const mysql = require('mysql2')
const cors = require('cors')
const bodyParser = require('body-parser')
const bcrypt = require('bcrypt') // import bcrypt for password encryption

const axios = require('axios')

const app = express()
const port = process.env.PORT || 5000

const ADMIN_SECRET_CODE = '2004' // Security code for admins

//Enable CORS for all requests
app.use(cors())
app.use(bodyParser.json())

// MySQL Connection
const db = mysql.createConnection({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME,
})

db.connect((err) => {
  if (err) {
    console.error('MYSQL connection error:', err)
  } else {
    console.log('Connected to MySQL database.')
  }
})

// Flutterwave configuration using Axios
const FLW_BASE_URL = 'https://api.flutterwave.com/v3'
const FLW_SECRET_KEY = process.env.FLW_SECRET_KEY

//Route to initiate a Flutterwave payment
app.post('/flutterwave/pay', async (req, res) => {
  const { amount, currency, email, tx_ref } = req.body

  //Validation
  if (!amount || !email || !tx_ref) {
    return res
      .status(400)
      .json({
        message: 'Amount, email, and transaction reference are required.',
      })
  }

  // Create the payload for flutterwave
  const payload = {
    tx_ref: tx_ref, // Unique transaction reference
    amount: amount, // Payment amount
    currency: currency || 'NGN', // currency (default is NGN)
    redirect_url: 'http://localhost:3000/flutterwave-success', // where to redirect after payment
    customer: { email },
  }

  try {
    // Call Flutterwave API to create a payment
    const response = await axios.post(
      'https://api.flutterwave.com/v3/payments',
      payload,
      {
        headers: {
          Authorization: `Bearer ${FLW_SECRET_KEY}`,
          'Content-Type': 'application/json',
        },
      },
    )

    // if successful, return the payment link
    if (response.data.status === 'success') {
      return res.json({ url: response.data.data.link })
    } else {
      console.log('Flutterwave response:', response.data)
      return res
        .status(400)
        .json({ message: 'Payment initiation failed', details: response.data })
    }
  } catch (error) {
    //Return any errors from Flutterwave
    return res.status(500).json({
      message: 'Flutterwave error',
      error: error.response ? error.response.data : error.message,
    })
  }
})

//Success route after flutterwave payment
app.get('/flutterwave-success', (req, res) => {
  const { paymentId, tx_ref, status, amount, email } = req.query
  if (!paymentId || !status) {
    return res.status(400).send('Missing payment details.')
  }

  //save payment details to the database
  const sql =
    'INSERT INTO payments ( transaction_id, tx_ref, payment_status, amount, user_email) VALUES (?, ?, ?, ?, ?)'
  db.query(sql, [paymentId, tx_ref, status, amount, email], (err, result) => {
    if (err) {
      console.error('Database insert error:', err)
      return res
        .status(500)
        .send('Database error while saving payment details.')
    }
    // Redirect to the React receipt page with details as query parameters

    res.redirect(
      `http://localhost:3000/receipt?transaction_id=${paymentId}&tx_ref=${tx_ref}&status=${status}&amount=${amount}&email=${email}`,
    )
  })
})

// Cancel route for payment cancellations
app.get('/flutterwave-cancel', (req, res) => {
  res.send('Payment was cancelled. Please try again.')
})

// USER REGISTER API (Sign_up)
app.post('/user/register', (req, res) => {
  const { email, password } = req.body

  // Validate input
  if (!email || !password) {
    return res.status(400).json({ message: 'Email and password are required.' })
  }

  // Check if user already registered
  db.query('SELECT * FROM users WHERE email = ?', [email], (err, results) => {
    if (err) {
      return res.status(500).json({ message: 'Database error' })
    }
    if (results.length > 0) {
      return res.status(400).json({ message: 'User already registered' })
    }

    //Encrypt password before Saving to database
    bcrypt.hash(password, 10, (err, hash) => {
      if (err) {
        return res.status(500).json({ message: 'Error encryption password' })
      }

      //Insert new user into database
      db.query(
        'INSERT INTO users (email, password) VALUES (?, ?)',
        [email, hash],
        (err, result) => {
          if (err) {
            return res.status(500).json({ message: 'Database error' })
          }
          res.status(201).json({ message: 'User registered successfully' })
        },
      )
    })
  })
})

//Admin Registration
app.post('/admin/register', (req, res) => {
  const { name, email, password, hospital_role, adminCode } = req.body
  console.log('Received Admin Code:', adminCode) // Debugging step

  if (adminCode !== ADMIN_SECRET_CODE) {
    return res.status(403).json({ message: 'Invalid admin security code' })
  }

  db.query('SELECT * FROM admins WHERE email = ?', [email], (err, results) => {
    if (err) return res.status(500).json({ message: 'Database error' })
    if (results.length > 0)
      return res.status(400).json({ message: 'Admin already exists' })

    bcrypt.hash(password, 10, (err, hash) => {
      if (err)
        return res.status(500).json({ message: 'Error hashing password' })

      db.query(
        'INSERT INTO admins (name, email, password, hospital_role) VALUES (?, ?, ?, ?)',
        [name, email, hash, hospital_role],
        (err, result) => {
          if (err) return res.status(500).json({ message: 'Database error' })
          res.json({ message: 'Admin registered successfully' })
        },
      )
    })
  })
})

// UNIFIED LOGIN
app.post('/login', (req, res) => {
  const { email, password } = req.body

  //First, Check in the users database
  db.query(
    'SELECT * FROM users WHERE email = ?',
    [email],
    (err, userResults) => {
      if (err) {
        return res.status(500).json({ message: 'Database error' })
      }

      if (userResults.length > 0) {
        //user found, verify password
        bcrypt.compare(password, userResults[0].password, (err, isMatch) => {
          if (err) {
            return res.status(500).json({ message: 'Error verifying password' })
          }
          if (!isMatch) {
            return res
              .status(400)
              .json({ message: 'Invalid email or password' })
          }

          //successful user login
          return res.json({
            message: 'User login successful',
            role: 'user',
            user: {
              id: userResults[0].id,
              email: userResults[0].email,
              name: userResults[0].name,
            },
          })
        })
        return // Stop further execution if user is found
      }

      //If no user found, check in the admins table
      db.query(
        'SELECT * FROM admins WHERE email = ?',
        [email],
        (err, adminResults) => {
          if (err) {
            return res.status(500).json({ message: 'Database error' })
          }
          if (adminResults.length > 0) {
            //Admin found, verify password
            bcrypt.compare(
              password,
              adminResults[0].password,
              (err, isMatch) => {
                if (err) {
                  return res
                    .status(500)
                    .json({ message: 'Error verifying password' })
                }
                if (!isMatch) {
                  return res
                    .status(400)
                    .json({ message: 'Invalid email or password' })
                }
                // Successful admin login
                return res.json({
                  message: 'Admin login successful',
                  role: 'admin',
                  admin: {
                    id: adminResults[0].id,
                    email: adminResults[0].email,
                    name: adminResults[0].name,
                    hospital_role: adminResults[0].hospital_role,
                  },
                })
              },
            )
          } else {
            // Not found in either table
            return res.status(400).json({ message: 'User not found ' })
          }
        },
      )
    },
  )
})

//API to update bank details (Admin Only)
app.post('/admin/update-bank-details', (req, res) => {
  const { bank_name, account_number, account_name } = req.body

  //Validate input
  if (!bank_name || !account_number || !account_name) {
    return res.status(400).json({ message: 'All fields are required.' })
  }

  //Update bank details
  const query =
    'INSERT INTO bank_details (bank_name, account_number, account_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE bank_name = ?, account_number = ?, account_name = ?'
  const values = [
    bank_name,
    account_number,
    account_name,
    bank_name,
    account_number,
    account_name,
  ]

  db.query(query, values, (err, results) => {
    if (err) {
      console.error('Database error:', err)
      return res.status(500).json({ message: 'Database error.' })
    }
    res.json({ message: 'Bank details updated successfully.' })
  })
})

//GET endpoint to retrieve bank details from database
app.get('/bank-details', (req, res) => {
  const query = 'SELECT * FROM bank_details LIMIT 1'
  db.query(query, (err, results) => {
    if (err) {
      console.error('Error fetching bank details.', err)
      return res.status(500).json({ message: 'Database error' })
    }
    if (results.length === 0) {
      return res.status(404).json({ message: 'No bank details found' })
    }
    res.json(results[0])
  })
})

//API to initiate Bank transfer (User)
app.post('/user/initiate-bank-transfer', (req, res) => {
  const { user_email, amount } = req.body

  //Validate input
  if (!user_email || !amount) {
    return res.status(400).json({ message: 'All fields are required.' })
  }

  //Insert into bank_transfer table
  const query = 'INSERT INTO bank_transfers (user_email, amount) VALUES (?, ?)'
  const values = [user_email, amount]

  db.query(query, values, (err, result) => {
    if (err) {
      return res.status(500).json({ message: 'Database Error. ' })
    }
    res.json({
      message: 'Bank transfer initiated. Waiting for admin approval.',
    })
  })
})

//GET endpoint to retrieve pending bank transfers
app.get('/admin/pending-transfers', (req, res) => {
  const query = "SELECT * FROM bank_transfers WHERE status = 'Pending' OR status IS NULL";
  db.query(query, (err, results) => {
    if (err) {
      console.error("Error fetching pending transfers: ", err);
      return res.status(500).json({ message: "Database error"});
    }
    res.json(results);
  });
});

//API to approve/reject bank transfer (Admin)
app.post('/admin/approve-bank-transfer', (req, res) => {
  const { transfer_id, status } = req.body

  // Validate input
  if (!transfer_id || !status) {
    return res.status(400).json({ message: 'All fields are required.' })
  }

  //Update bank transfer status
  const query = 'UPDATE bank_transfers SET status = ? WHERE id = ?'
  const values = [status, transfer_id]

  db.query(query, values, (err, rows) => {
    if (err) {
      return res.status(500).json({ message: 'Database Error.' })
    }
    res.json({ message: `Bank Transfer ${status.toLowerCase()} successfully.` })
  })
})
// Start Server
app.listen(port, () => {
  console.log('Server running on http://localhost:${port}')
})
