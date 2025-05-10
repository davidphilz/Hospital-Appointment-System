<?php

// Database credentials
$host = 'localhost'; // Corrected typo
$dbname = 'hospital_appointment_system'; // Database name
$username = 'root'; // Added missing semicolon
$password = ''; // No changes needed

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Added missing semicolon
}
?>