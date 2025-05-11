<!-- filepath: c:\xampp\htdocs\Hospital-Appointment-System\Resource Management Module\admin\close_appointment.php -->
<?php
session_start();
include("../include/connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    // Update the appointment status to "Closed"
    $query = "UPDATE appointments SET status = 'Closed' WHERE id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $appointment_id);

    if ($stmt->execute()) {
        // Redirect back to the Payment Reports page with a success message
        $_SESSION['success'] = "Appointment ID $appointment_id has been successfully closed.";
        header("Location: admin_payment_reports.php");
        exit();
    } else {
        // Redirect back with an error message
        $_SESSION['error'] = "Failed to close Appointment ID $appointment_id. Please try again.";
        header("Location: admin_payment_reports.php");
        exit();
    }
} else {
    // Redirect back if accessed without POST data
    header("Location: admin_payment_reports.php");
    exit();
}
?>