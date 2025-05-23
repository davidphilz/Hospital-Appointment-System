<?php
// Include the database connection
include 'includes/db.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    // Check if the email already exists
    $check_email_query = "SELECT * FROM patients WHERE email = '$email'";
    $check_email_result = mysqli_query($conn, $check_email_query);

    if (mysqli_num_rows($check_email_result) > 0) {
        echo "Email already exists. Please use a different email or <a href='auth/login.php'>login here</a>.";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL query to insert user
    $sql = "INSERT INTO patients (name, email, password) VALUES ('$full_name', '$email', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        echo "Signup successful! <a href='auth/login.php'>Login here</a>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    // Close connection
    mysqli_close($conn);
} else {
    echo "Invalid request method.";
}
?>