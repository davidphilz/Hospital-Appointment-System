<?php
// mark_read.php
session_start();
include("../include/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notif_id'])) {
    $stmt = $pdo->prepare("
      UPDATE doctor_notifications
      SET is_read = 1
      WHERE id = ? AND doctor_id = ?
    ");
    $stmt->execute([$_POST['notif_id'], $_SESSION['doctor_id']]);
}

header('Location: doctor_dashboard.php');
exit;
