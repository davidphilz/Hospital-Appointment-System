<?php
// process_cancellation.php
session_start();
// ensure admin is logged in...
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_dashboard.php');
    exit;
}

$appointment_id = $_POST['appointment_id'] ?? null;
$reason         = trim($_POST['reason'] ?? '');

if (!$appointment_id || $reason === '') {
    die("All fields are required.");
}

// 1. Insert into appointment_cancellations
$ins = $pdo->prepare("
    INSERT INTO appointment_cancellations (appointment_id, cancelled_by, reason)
    VALUES (?, 'admin', ?)
");
$ins->execute([$appointment_id, $reason]);
$cancellation_id = $pdo->lastInsertId();

// 2. Update appointment status
$upd = $pdo->prepare("
    UPDATE appointments SET status = 'cancelled'
    WHERE id = ?
");
$upd->execute([$appointment_id]);

// 3. Find the doctor for this appointment
$q = $pdo->prepare("SELECT doctor_id FROM appointments WHERE id = ?");
$q->execute([$appointment_id]);
$doctor = $q->fetchColumn();

// 4. Insert doctor notification
$notif = $pdo->prepare("
    INSERT INTO doctor_notifications (doctor_id, cancellation_id)
    VALUES (?, ?)
");
$notif->execute([$doctor, $cancellation_id]);

// 5. Redirect back with a success message
$_SESSION['flash'] = "Appointment #{$appointment_id} cancelled and doctor notified.";
header('Location: admin_dashboard.php');
exit;
