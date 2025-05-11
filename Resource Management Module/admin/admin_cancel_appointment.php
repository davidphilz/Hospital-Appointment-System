<?php
include("../include/connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment_id'])) {
    $appointment_id = intval($_POST['cancel_appointment_id']);
    $reason = trim($_POST['cancellation_reason']);

    $stmt = $connect->prepare("UPDATE appointments SET status = 'Cancelled', cancellation_reason = ? WHERE id = ?");
    $stmt->bind_param("si", $reason, $appointment_id);

    if ($stmt->execute()) {
        // Use header redirect and exit immediately
        header("Location: admin_view_appointments.php?cancel=success");
        exit();
    } else {
        header("Location: admin_view_appointments.php?cancel=fail");
        exit();
    }
}
?>
