<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('includes/db.php'); // Corrected the path to db.php

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$appointmentId = $input['appointmentId'] ?? null;

if (!$appointmentId) {
    echo json_encode(['success' => false, 'message' => 'Appointment ID is required.']);
    exit();
}

$userId = $_SESSION['id'];

// Fetch user details
$query = "SELECT email FROM patients WHERE id = $userId";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit();
}

$user = mysqli_fetch_assoc($result);

// Debugging: Log the email being fetched
error_log("Fetched email: " . $user['email']);

// Generate a secure token (e.g., JWT)
$secretKey = 'your_secret_key'; // Replace with your secret key
$payload = [
    'email' => $user['email'],
    'appointmentId' => $appointmentId,
    'exp' => time() + 3600, // Token expires in 1 hour
];

// Debugging: Log the token payload
error_log("Token payload: " . json_encode($payload));

$token = base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), $secretKey);

echo json_encode(['success' => true, 'token' => $token]);
?>