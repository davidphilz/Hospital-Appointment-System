<?php
session_start();
require_once("../include/configure.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_name = trim($_POST['doctor_name']);
    $notice_type = trim($_POST['notice_type']);
    $description = trim($_POST['description']);
    

    if (empty($doctor_name) || empty($notice_type) || empty($description)) {
        die('All fields are required.');
    }

    $stmt = $pdo->prepare("INSERT INTO notices (doctor_name, notice_type, description) VALUES (?, ?, ?)");
    if ($stmt->execute([$doctor_name, $notice_type, $description])) {
        header("Location: notice_success.php");
        exit;
    } else {
        die('Error sending notice.');
    }
} else {
    header("Location: notice_form.php");
    exit;
}

?>
