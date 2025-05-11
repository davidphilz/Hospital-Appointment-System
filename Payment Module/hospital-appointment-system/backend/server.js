require('dotenv').config()
const express = require('express')
const mysql = require('mysql2')
const cors = require('cors')
const bodyParser = require('body-parser')
const bcrypt = require('bcrypt') // import bcrypt for password encryption
const jwt = require('jsonwebtoken') // Used for token generation

const axios = require('axios')

const app = express()
const port = process.env.PORT || 5000

const ADMIN_SECRET_CODE = '2004' // Security code for admins

const nodemailer = require('nodemailer') // import nodemailer for notifications

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

// Middleware to verify token
function verifyToken(req, res, next) {
    const token = req.query.token;

    if (!token) {
        return res.status(401).json({ message: 'Access denied. No token provided.' });
    }

    try {
        const decoded = jwt.verify(token, '2004'); // Replace with your secret key
        console.log('Decoded token:', decoded); // Debugging
        req.user = decoded; // Attach the decoded token to the request
        next();
    } catch (err) {
        console.error('Token verification failed:', err);
        res.status(400).json({ message: 'Invalid token.' });
    }
}

//Route to initiate a Flutterwave payment
app.post('/flutterwave/pay', async (req, res) => {
  const { amount, currency, email, tx_ref } = req.body

  //Validation
  if (!amount || !email || !tx_ref) {
    return res.status(400).json({
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

// Example route for Flutterwave payment
app.get('/flutterwave/pay', (req, res) => {
    const { email, appointmentId } = req.query;

    if (!email || !appointmentId) {
        return res.status(400).json({ message: 'Email and appointment ID are required.' });
    }

    console.log('Processing Flutterwave Payment for:', email, 'Appointment ID:', appointmentId);

    res.send(`Processing Flutterwave Payment for ${email}, Appointment ID: ${appointmentId}`);
})

//Route to process a bank transfer
app.get('/bank-transfer', (req, res) => {
    const { email, appointmentId } = req.query;

    if (!email || !appointmentId) {
        return res.status(400).json({ message: 'Email and appointment ID are required.' });
    }

    console.log('Processing Bank Transfer for:', email, 'Appointment ID:', appointmentId);

    res.send(`Processing Bank Transfer for ${email}, Appointment ID: ${appointmentId}`);
})

//Route to process a cash payment
app.get('/cash-payment', verifyToken, (req, res) => {
    const { email, appointmentId } = req.user;

    // Use the email and appointmentId to process the cash payment
    res.send(
        `Processing Cash Payment for ${email}, Appointment ID: ${appointmentId}`
    );
})

//Route to process an HMO claim
app.get('/hmo-claim', verifyToken, (req, res) => {
    const { email, appointmentId } = req.user;

    // Use the email and appointmentId to process the HMO claim
    res.send(
        `Processing HMO Claim for ${email}, Appointment ID: ${appointmentId}`
    );
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

    // Insert into the receipts table
    if (status === 'successful') {
      const receiptQuery =
        'INSERT INTO receipts (user_email, amount, payment_method, status, transaction_id, tx_ref) VALUES (?, ?, ?, ?, ?, ?)'
      db.query(
        receiptQuery,
        [email, amount, 'Flutterwave', 'Completed', paymentId, tx_ref],
        (err, result) => {
          if (err) {
            return res
              .status(500)
              .json({ message: 'Error generating receipt.' })
          }
        },
      )
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
  const { name, email, password } = req.body

  // Validate input
  if (!email || !password) {
    return res.status(400).json({ message: 'All fields are required.' })
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
        'INSERT INTO users (name, email, password) VALUES (?, ?, ?)',
        [name, email, hash],
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

// Patient Registration
app.post('/patient/register', (req, res) => {
  const { name, email, password } = req.body;

  // Validate input
  if (!name || !email || !password) {
    return res.status(400).json({ message: 'All fields are required.' });
  }

  // Check if the patient already exists
  db.query('SELECT * FROM patients WHERE email = ?', [email], (err, results) => {
    if (err) {
      return res.status(500).json({ message: 'Database error' });
    }
    if (results.length > 0) {
      return res.status(400).json({ message: 'Patient already registered' });
    }

    // Encrypt password before saving to the database
    bcrypt.hash(password, 10, (err, hash) => {
      if (err) {
        return res.status(500).json({ message: 'Error encrypting password' });
      }

      // Insert new patient into the database
      db.query(
        'INSERT INTO patients (name, email, password) VALUES (?, ?, ?)',
        [name, email, hash],
        (err, result) => {
          if (err) {
            return res.status(500).json({ message: 'Database error' });
          }
          res.status(201).json({ message: 'Patient registered successfully' });
        }
      );
    });
  });
});

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

// Unified Login
app.post('/login', (req, res) => {
  const { email, password } = req.body;

  console.log('Login Request:', { email, password }); // Debugging

  if (!email || !password) {
    return res.status(400).json({ message: 'Email and password are required.' });
  }

  db.query('SELECT * FROM patients WHERE email = ?', [email], (err, patientResults) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ message: 'Database error' });
    }

    if (patientResults.length > 0) {
      bcrypt.compare(password, patientResults[0].password, (err, isMatch) => {
        if (err) {
          console.error('Error verifying password:', err);
          return res.status(500).json({ message: 'Error verifying password' });
        }

        if (!isMatch) {
          console.log('Password does not match.');
          return res.status(400).json({ message: 'Invalid email or password' });
        }

        console.log('Login successful for:', patientResults[0].email);
        return res.json({
          message: 'Patient login successful',
          role: 'patient',
          user: {
            id: patientResults[0].id,
            email: patientResults[0].email,
            name: patientResults[0].name,
          },
        });
      });
      return; // Stop further execution if patient is found
    }

    // If no patient found, check in the admins table
    db.query('SELECT * FROM admins WHERE email = ?', [email], (err, adminResults) => {
      if (err) {
        return res.status(500).json({ message: 'Database error' });
      }
      if (adminResults.length > 0) {
        // Admin found, verify password
        bcrypt.compare(password, adminResults[0].password, (err, isMatch) => {
          if (err) {
            return res.status(500).json({ message: 'Error verifying password' });
          }
          if (!isMatch) {
            return res.status(400).json({ message: 'Invalid email or password' });
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
          });
        });
      } else {
        // Not found in either table
        return res.status(400).json({ message: 'User not found' });
      }
    });
  });
});

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
  const query =
    "SELECT * FROM bank_transfers WHERE status = 'Pending' OR status IS NULL"
  db.query(query, (err, results) => {
    if (err) {
      console.error('Error fetching pending transfers: ', err)
      return res.status(500).json({ message: 'Database error' })
    }
    res.json(results)
  })
})

//API to approve/reject bank transfer (Admin)
app.post('/admin/approve-bank-transfer', (req, res) => {
  const { transfer_id, status } = req.body;

  if (!transfer_id || !status) {
    return res.status(400).json({ message: 'All fields are required.' });
  }

  // Fetch payment details before updating the status
  db.query(
    'SELECT * FROM bank_transfers WHERE id = ?',
    [transfer_id],
    (err, transferDetails) => {
      if (err || transferDetails.length === 0) {
        console.error('Error fetching bank transfer details: ', err);
        return res
          .status(500)
          .json({ message: 'Error fetching bank transfer details.' });
      }

      const { user_email, amount } = transferDetails[0];

      // Update transfer status
      db.query(
        'UPDATE bank_transfers SET status = ? WHERE id = ?',
        [status, transfer_id],
        (err, updateResult) => {
          if (err) {
            console.error('Error updating bank transfer status: ', err);
            return res
              .status(500)
              .json({ message: 'Error updating bank transfer status.' });
          }

          // If approved, insert into receipts and payments
          if (status === 'Approved') {
            const receiptQuery =
              'INSERT INTO receipts (user_email, amount, payment_method, status) VALUES (?, ?, ?, ?)';
            db.query(
              receiptQuery,
              [user_email, amount, 'Offline Bank Transfer', 'Completed'],
              (err, receiptResult) => {
                if (err) {
                  console.error('Error inserting into receipts: ', err);
                  return res
                    .status(500)
                    .json({ message: 'Error generating receipt.' });
                }

                // Insert into payments table
                const paymentQuery = `
                  INSERT INTO payments (name, email, unit, problem_description, amount_paid, time_completed)
  SELECT p.name, p.email, a.unit, a.problem_description, ?, NOW()
  FROM patients p
  JOIN appointments a ON p.id = a.patient_id
  WHERE p.email = ?
                `;
                db.query(
                  paymentQuery,
                  [amount, user_email],
                  (err, paymentResult) => {
                    if (err) {
                      console.error('Error inserting into payments table: ', err);
                      return res
                        .status(500)
                        .json({ message: 'Error saving payment details.' });
                    }

                    console.log('Payment details saved successfully.');
                  })
                

                // Send email notification
                const transporter = nodemailer.createTransport({
                  host: 'smtp.gmail.com',
                  port: 587,
                  secure: false,
                  auth: {
                    user: 'doctorphil36@gmail.com',
                    pass: 'zrdtygtahgiwtqbi', // Google app password
                  },
                })

                const mailOptions = {
                  from: '"Hospital Payment System" <doctorphil36@gmail.com>',
                  to: user_email,
                  subject: `Your Bank Transfer has been ${status}`,
                  text: `Hello,

                        Your bank transfer of ₦${amount} has been ${status.toLowerCase()}.

                        Thank you for using our hospital payment system.

                        Regards,
                        Hospital Payment Team`,
                }

                transporter.sendMail(mailOptions, (err, info) => {
                    if (err) {
                      console.error('Error inserting into payments table: ', err);
                      return res
                        .status(500)
                        .json({ message: 'Error saving payment details.' });
                    }

                    console.log('Payment details saved successfully.');
                    return res.json({
                      message:
                        'Bank Transfer approved, receipt generated, and payment details saved.',
                    });
                  }
                );
              }
            );
          } else {
            // If rejected, simply return a response
            return res.json({
              message: `Bank Transfer ${status.toLowerCase()} successfully.`,
            });
          }
        }
      );
    }
  );
});

//API to initiate Cash Payment
app.post('/user/cash-payment', (req, res) => {
  const { email, amount } = req.body

  if (!email || !amount) {
    return res.status(400).json({ message: 'All fields are required.' })
  }

  const query = 'INSERT INTO cash_payments (user_email, amount) VALUES (?, ?)'
  const values = [email, amount]

  db.query(query, values, (err, result) => {
    if (err) {
      return res.status(500).json({ message: 'Database error' })
    }
    res.json({
      message: 'Cash Payment recorded. Waiting for admin approval.',
      email,
    })
  })
})

//API to Approve/Reject Cash Payment
app.post('/admin/cash-payment/approve', (req, res) => {
  const { payment_id, status } = req.body

  // Setup the email transporter
  const transporter = nodemailer.createTransport({
    host: 'smtp.gmail.com',
    port: 587,
    secure: false,
    auth: {
      user: 'doctorphil36@gmail.com',
      pass: 'zrdtygtahgiwtqbi', // Google app password
    },
  })

  if (!payment_id || !status) {
    return res.status(400).json({ message: 'All fields are required.' })
  }

  console.log(`Processing cash payment: ID=${payment_id}, Status=${status}`) //Debugging

  //Update cash payment status in the
  const query = 'UPDATE cash_payments SET status = ? WHERE id = ?'
  const values = [status, payment_id]

  db.query(query, values, (err, result) => {
    if (err) {
      console.error('Database update error:', err)
      return res
        .status(500)
        .json({ message: 'Error approving/rejecting transactions.' })
    }

    if (result.affectedRows === 0) {
      return res.status(400).json({ message: 'Payment ID not found.' })
    }

    //Fetch payment details for receipt generation
    db.query(
      'SELECT * FROM cash_payments WHERE id = ?',
      [payment_id],
      (err, paymentDetails) => {
        if (err || paymentDetails.length === 0) {
          return res
            .status(500)
            .json({ message: 'Error fetching payment details.' })
        }

        const { user_email, amount, status } = paymentDetails[0]

        if (status === 'Approved') {
          //Insert receipt into database
          const receiptQuery =
            'INSERT INTO receipts (user_email, amount, payment_method, status) VALUES (?, ?, ?, ?)'
          db.query(
            receiptQuery,
            [user_email, amount, 'Cash', 'Completed'],
            (err, result) => {
              if (err) {
                return res
                  .status(500)
                  .json({ message: 'Error generating receipt.' })
              }

              
            },
          )

          // Insert into payments table
          const paymentQuery = `
          INSERT INTO payments (name, email, unit, problem_description, amount_paid, time_completed)
  SELECT p.name, p.email, a.unit, a.problem_description, ?, NOW()
  FROM patients p
  JOIN appointments a ON p.id = a.patient_id
  WHERE p.email = ?
        `;
        db.query(
          paymentQuery,
          [amount, user_email],
          (err, paymentResult) => {
            if (err) {
              console.error('Error inserting into payments table: ', err);
              return res
                .status(500)
                .json({ message: 'Error saving payment details.' });
            }

            console.log('Payment details saved successfully.');
          }
        );
      
          
        }

        // Email notification
        const mailOptions = {
          from: '"Hospital Payment System" <doctorphil36@gmail.com>',
          to: user_email,
          subject: `Your Cash Payment has been ${status}`,
          text: `Hello,

                Your cash payment of ₦${amount} has been ${status.toLowerCase()}.

                Thank you for using our hospital payment system.

                Regards,
                Hospital Payment Team`,
        }

        transporter.sendMail(mailOptions, (err, info) => {
          if (err) {
            console.error('Error sending email:', err)
          } else {
            console.log('Email sent:', info.response)
            return res.json({
              message: `Cash payment ${status.toLowerCase()} successfully.`,
            })
          }
        })
      },
    )
  })
})

//API to fetch pending cash payments for admin
app.get('/admin/pending-cash-payments', (req, res) => {
  const query = "SELECT * FROM cash_payments WHERE status = 'Pending'"

  db.query(query, (err, results) => {
    if (err) {
      return res
        .status(500)
        .json({ message: 'Error getting pending cash payments' })
    }
    res.json(results)
  })
})

//API to fetch user payment history
app.get('/user/transactions', (req, res) => {
  const { email } = req.query

  if (!email) {
    return res.status(400).json({ message: 'User email is required.' })
  }
  // Fetch transactions where user_email is either:
  // 1. The logged in email
  // 2. The email used in the HMO request if it is different
  const query =
    'SELECT * FROM receipts WHERE user_email = ? OR user_email IN (SELECT patient_email FROM hmo_requests WHERE patient_email = ?) ORDER BY created_at DESC'
  db.query(query, [email, email], (err, results) => {
    if (err) {
      console.error('Error fetching transaction history:', err)
      return res
        .status(500)
        .json({ message: 'Database error fetching transactions.' })
    }
    res.json(results)
  })
})

// API to fetch details of a single transaction
app.get('/user/transaction-details', (req, res) => {
  const { transaction_id } = req.query

  if (!transaction_id) {
    return res.status(400).json({ message: 'Transaction ID is required.' })
  }

  const query = 'SELECT * FROM receipts WHERE id = ?'
  db.query(query, [transaction_id], (err, result) => {
    if (err) {
      console.error('Error fetching transaction details: ', err)
      return res.status(500).json({ message: 'Database error.' })
    }
    if (result.length === 0) {
      return res.status(404).json({ message: 'Transaction not found.' })
    }
    res.json(result[0])
  })
})

//API to handle HMO Payment Requests
app.post('/user/hmo-payment', (req, res) => {
  const { email, phone, amount } = req.body

  if (!email || !phone || !amount) {
    return res.status(400).json({ message: 'All fields are required.' })
  }

  const query =
    "INSERT INTO hmo_requests (patient_email, patient_phone, amount, request_status, request_date) VALUES (?, ?, ?, 'Pending', NOW())"
  const values = [email, phone, amount]

  db.query(query, values, (err, result) => {
    if (err) {
      console.error('Database error:', err)
      return res
        .status(500)
        .json({ message: 'Database error. Please try again.' })
    }
    res.json({
      message:
        'HMO Payment request submitted successfully. Awaiting admin approval.',
    })
  })
})

// API for Admin to Process HMO Claim requests
app.post('/admin/process-hmo-claim', (req, res) => {
  const { request_id, admin_email } = req.body

  if (!request_id || !admin_email) {
    return res
      .status(400)
      .json({ message: 'Request ID and Admin Email are required.' })
  }

  //Step 1: Fetch HMO Request Details
  db.query(
    'SELECT * FROM HMO_Requests WHERE request_id = ?',
    [request_id],
    (err, requestResults) => {
      if (err || requestResults.length === 0) {
        return res.status(404).json({ message: 'HMO request not found.' })
      }

      const { patient_email, patient_phone, amount } = requestResults[0]

      //step 2: Verify Patient Details in HMO_Patients
      db.query(
        'SELECT * FROM HMO_Patients WHERE patient_email = ? AND phone_number = ?',
        [patient_email, patient_phone],
        (err, patientResults) => {
          if (err || patientResults.length === 0) {
            db.query(
              "UPDATE HMO_Requests SET request_status = 'Rejected' WHERE request_id = ?",
              [request_id],
            )
            return res.status(400).json({
              message: 'Patient not found or not eligible for HMO payment.',
            })
          }

          const patient = patientResults[0]
          const patient_id = patient.patient_id
          const hmo_plan_id = patient.hmo_plan_id

          // Step 3: Retrieve HMO Plan Details
          db.query(
            'SELECT * FROM HMO_Plans WHERE hmo_plan_id = ?',
            [hmo_plan_id],
            (err, planResults) => {
              if (err || planResults.length === 0) {
                return res
                  .status(500)
                  .json({ message: 'Error fetching HMO plan details.' })
              }

              const hmoPlan = planResults[0]
              const coverage_limit = parseFloat(hmoPlan.coverage_limit)
              const requestedAmount = parseFloat(amount)

              console.log(
                `Requested Amount: ${requestedAmount}, Coverage Limit: ${coverage_limit}`,
              )

              // Step 4: Check if Requested Amount is within coverage limit.
              if (requestedAmount > coverage_limit) {
                db.query(
                  "UPDATE HMO_Requests SET request_status = 'Rejected' WHERE request_id = ?",
                  [request_id],
                )
                return res.status(400).json({
                  message: 'Requested amount exceeds HMO Coverage Limit.',
                })
              }

              // Step 5: Approve the Claim & Insert into HMO_Claims
              db.query(
                'INSERT INTO HMO_Claims (request_id, patient_id, patient_email, claim_amount, claim_status, processed_by, claim_date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
                [
                  request_id,
                  patient_id,
                  patient_email,
                  requestedAmount,
                  'Approved',
                  admin_email,
                ],
                (err, result) => {
                  if (err) {
                    return res
                      .status(500)
                      .json({ message: 'Error inserting claim into database.' })
                  }

                  // Step 6: INSERT into HMO_Transactions
                  db.query(
                    'INSERT INTO HMO_Transactions (patient_email, request_id, patient_id, amount, transaction_status, transaction_date) VALUES (?, ?, ?, ?, ?, NOW())',
                    [
                      patient_email,
                      request_id,
                      patient_id,
                      requestedAmount,
                      'Successful',
                    ],

                    (err, transactionResult) => {
                      if (err) {
                        console.error('Error inserting HMO transaction:', err)
                        return res.status(500).json({
                          message: 'Error processing HMO transaction.',
                        })
                      }
                      console.log(
                        'HMO Transaction successfully inserted for request: ',
                        request_id,
                      )

                      // Step 7: Insert Receipt into Receipts Table
                      db.query(
                        'INSERT INTO receipts (user_email, amount, payment_method, status, created_at) VALUES (?, ?, ?, ?, NOW())',
                        [patient_email, requestedAmount, 'HMO', 'Completed'],
                        (err, receiptResult) => {
                          if (err) {
                            return res
                              .status(500)
                              .json({ message: 'Error generating receipt.' })
                          }

                          // Insert into payments table
          const paymentQuery = `
          INSERT INTO payments (name, email, unit, problem_description, amount_paid, time_completed)
  SELECT p.name, p.email, a.unit, a.problem_description, ?, NOW()
  FROM patients p
  JOIN appointments a ON p.id = a.patient_id
  WHERE p.email = ?
        `;
        db.query(
          paymentQuery,
          [amount, patient_email],
          (err, paymentResult) => {
            if (err) {
              console.error('Error inserting into payments table: ', err);
              return res
                .status(500)
                .json({ message: 'Error saving payment details.' });
            }

            console.log('Payment details saved successfully.');
          }
        );

                          // Step 8: Update the HMO Request Status to "Processed"
                          db.query(
                            "UPDATE HMO_Requests SET request_status = 'Processed' WHERE request_id = ?",
                            [request_id],
                          )

                          // Step 9: Send email notification to the patient
                          const transporter = nodemailer.createTransport({
                            host: 'smtp.gmail.com',
                            port: 587,
                            secure: false,
                            auth: {
                              user: 'doctorphil36@gmail.com',
                              pass: 'zrdtygtahgiwtqbi', // Google app password
                            },
                          })

                          const mailOptions = {
                            from: '"Hospital Payment System" <doctorphil36@gmail.com>',
                            to: patient_email,
                            subject: `Your HMO claim for ₦ ${requestedAmount} has been approved and processed.`,
                            text: `Hello,
                            Your HMO claim for ₦${requestedAmount} has been approved and processed.
                  
                            Thank you for using our hospital payment system.
                  
                            Regards,
                            Hospital Payment Team`,
                          }

                          transporter.sendMail(mailOptions, (err, info) => {
                            if (err) {
                              console.error('Error sending email:', err)
                            } else {
                              console.log('Email sent:', info.response)
                            }
                          })

                          return res.json({
                            message:
                              'HMO Claim approved, transaction recorded, and receipt generated successfully.',
                          })
                        },
                      )
                    },
                  )
                },
              )
            },
          )
        },
      )
    },
  )
})

// API to fetch all pending HMO requests
app.get('/admin/hmo-requests', (req, res) => {
  db.query(
    "SELECT * FROM HMO_Requests WHERE request_status = 'Pending'",
    (err, results) => {
      if (err) {
        console.error('Database error fetching HMO requests:', err)
        return res
          .status(500)
          .json({ message: 'Database error fetching HMO requests.' })
      }

      if (results.length === 0) {
        return res.json({ message: 'No pending requests found.' })
      }
      res.json(results)
    },
  )
})

// API to fetch user details for Account Summary
app.get('/user/account-summary', (req, res) => {
  const userEmail = req.query.email;

  if (!userEmail) {
    return res.status(400).json({ message: 'Email is required.' });
  }

  const query = `
    SELECT 
      patients.id,  
      patients.name, 
      patients.email, 
      COALESCE(SUM(receipts.amount), 0) AS total_payments
    FROM patients  
    LEFT JOIN receipts ON patients.email = receipts.user_email
    WHERE patients.email = ?
    GROUP BY patients.id, patients.name, patients.email
  `;

  db.query(query, [userEmail], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res
        .status(500)
        .json({ message: 'Error fetching account summary.' });
    }

    if (results.length === 0) {
      return res.status(404).json({ message: 'User not found.' });
    }

    res.json(results[0]); // Return just the user's data
  });
});

// API to fetch recent transactions for User Overview
app.get('/user/recent-transactions', (req, res) => {
  const email = req.query.email;
  const limit = req.query.limit || 5; // Default to 5 transactions

  if (!email) {
    return res.status(400).json({ message: 'User email is required.' });
  }

  // SQL query to fetch recent transactions
  const query =
    'SELECT * FROM receipts WHERE user_email = ? ORDER BY created_at DESC LIMIT ?';

  db.query(query, [email, limit], (err, results) => {
    if (err) {
      return res.status(500).json({ message: 'Database error.' });
    }

    if (results.length === 0) {
      return res.json({ message: 'No recent transactions found.' });
    }

    // Send back the transactions as a response
    res.json(results);
  });
});

// API to fetch summary for Admin Overview
app.get('/admin/overview-stats', (req, res) => {
  const stats = {}

  const totalPaymentsQuery = 'SELECT COUNT(*) AS totalPayments FROM receipts'
  const totalRevenueQuery = 'SELECT SUM(amount) AS totalRevenue FROM receipts'
  const totalUsersQuery = 'SELECT COUNT(*) AS totalUsers FROM users'

  // Query for Payments Approval summary
  const pendingBankTransfersQuery = `SELECT COUNT(*) AS count FROM bank_transfers WHERE status = 'pending'`
  const pendingCashPaymentsQuery = `SELECT COUNT(*) AS count FROM cash_payments WHERE status = 'pending'`
  const pendingHmoRequestsQuery = `SELECT COUNT(*) AS count FROM hmo_requests WHERE request_status = 'pending'`

  db.query(totalPaymentsQuery, (err, paymentsResults) => {
    if (err)
      return res.status(500).json({ message: 'Error fetching payments count ' })

    stats.totalPayments = paymentsResults[0].totalPayments

    db.query(totalRevenueQuery, (err, revenueResult) => {
      if (err)
        return res.status(500).json({ message: 'Error fetching revenue' })

      stats.totalRevenue = revenueResult[0].totalRevenue || 0

      db.query(totalUsersQuery, (err, usersResult) => {
        if (err)
          return res.status(500).json({ message: 'Error fetching users' })

        stats.totalUsers = usersResult[0].totalUsers

        // Start fetching pending approvals from all 3 tables
        db.query(pendingBankTransfersQuery, (err, bankResult) => {
          if (err)
            return res
              .status(500)
              .json({ message: 'Error fetching bank transfers' })

          const bankPending = bankResult[0].count

          db.query(pendingCashPaymentsQuery, (err, cashResult) => {
            if (err)
              return res
                .status(500)
                .json({ message: 'Error fetching cash payments' })

            const cashPending = cashResult[0].count

            db.query(pendingHmoRequestsQuery, (err, hmoResult) => {
              if (err)
                return res
                  .status(500)
                  .json({ message: 'Error fetching HMO requests' })

              const hmoPending = hmoResult[0].count

              stats.pendingApprovals = bankPending + cashPending + hmoPending

              res.json(stats)
            })
          })
        })
      })
    })
  })
})

// API to transactions with "Pending" status for receipts
app.get('/check-pending-status/:user_email', (req, res) => {
  const userEmail = req.params.user_email

  const query =
    'SELECT status FROM receipts WHERE user_email = ? AND status != "Pending" ORDER BY created_at DESC LIMIT 1'

  db.query(query, [userEmail], (err, result) => {
    if (err) {
      console.error('Error fetching transactions status:', err)
      return res.status(500).json({ error: 'Database error ' })
    }

    if (result.length === 0) {
      // No completed/failed transactions found
      return res.status(200).json({ status: 'Pending' })
    }

    // Send back the updated status
    return res.status(200).json({ status: result[0].status })
  })
})

// API to fetch all transactions for the admin
app.get('/admin/transactions', (req, res) => {
  const query = `
    SELECT p.name AS patient_name, r.amount, r.payment_method, r.status, r.created_at 
    FROM receipts r 
    JOIN patients p ON r.user_email = p.email 
    ORDER BY r.created_at DESC
  `;

  db.query(query, (err, results) => {
    if (err) {
      console.error('Error fetching all transactions:', err);
      return res.status(500).json({ message: 'Database error fetching transactions.' });
    }
    res.json(results);
  });
});

// Start Server
app.listen(port, () => {
  console.log('Server running on http://localhost:${port}')
})
