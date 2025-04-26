<?php
session_start();
include("header.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender   = $_POST['sender'];
    $receiver = $_POST['receiver'];
    $message  = $_POST['message'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "resource";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert notice into the 'hospital_notices' table
    $stmt = $conn->prepare("INSERT INTO hospital_notices (sender, receiver, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $sender, $receiver, $message);
    if ($stmt->execute()) {
        // Redirect the user based on their role
        if ($sender == 'staff') {
            header("Location: equipment.php?status=notice_sent");
        } elseif ($sender == 'doctor') {
            header("Location: doctor_dashboard.php?status=notice_sent");
        } else {
            echo "Notice sent.";
        }
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
