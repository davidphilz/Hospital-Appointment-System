<?php
session_start();
include 'includes/db.php'; // database connection

// Sanitize input
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

// Fetch user from database
$sql = "SELECT * FROM patients WHERE email = '$email' LIMIT 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);

    // Check password
    if (password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['patient_id'] = $user['id'];
        $_SESSION['patient_name'] = $user['name'];
        $_SESSION['email'] = $user['email']; // Add this line to set the email in the session

        // Redirect to patient dashboard
        header("Location: /Hospital-Appointment-System/Queuing%20Module/dashboard/index.php");
        exit();
    } else {
        header("Location: login.php?error=Invalid password");
        exit();
    }
} else {
    header("Location: login.php?error=User not found");
    exit();
}
?>
