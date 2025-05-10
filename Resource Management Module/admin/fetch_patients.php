<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Fetch the patient data from session
if (isset($_SESSION['patients'])) {
    echo json_encode($_SESSION['patients']);
}
?>
