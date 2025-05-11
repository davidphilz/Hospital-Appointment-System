<?php
include("../include/connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment_id'])) {
    $appointment_id = intval($_POST['cancel_appointment_id']);
    $reason = trim($_POST['cancellation_reason']);

    // Update appointment
    $stmt = $connect->prepare("UPDATE appointments SET status = 'Cancelled', cancellation_reason = ? WHERE id = ?");
    $stmt->bind_param("si", $reason, $appointment_id);

    if ($stmt->execute()) {
        echo "<script>alert('Appointment cancelled successfully.'); window.location.href = 'admin_view_appointments.php';</script>";
    } else {
        echo "<script>alert('Failed to cancel appointment.');</script>";
    }
}
?>
