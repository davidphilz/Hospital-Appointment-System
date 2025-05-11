<?php
// cancel_appointment.php
session_start();
include("../include/db.php");
$appt_id = $_GET['id'] ?? null;
if (!$appt_id) {
    die("No appointment specified.");
}

// fetch appointment details if you like
$stmt = $pdo->prepare("SELECT a.*, d.name AS doctor_name
                      FROM appointments a
                      JOIN doctors d ON a.doctor_id = d.id
                      WHERE a.id = ?");
$stmt->execute([$appt_id]);
$appt = $stmt->fetch();
if (!$appt) {
    die("Appointment not found.");
}
?>
<!DOCTYPE html>
<html>
<head><title>Cancel Appointment #<?= htmlspecialchars($appt_id) ?></title></head>
<body>
  <h1>Cancel Appointment #<?= htmlspecialchars($appt_id) ?></h1>
  <p>Doctor: <?= htmlspecialchars($appt['doctor_name']) ?><br>
     Patient: <?= htmlspecialchars($appt['patient_name']) ?><br>
     Scheduled at: <?= htmlspecialchars($appt['scheduled_time']) ?></p>

  <form action="process_cancellation.php" method="post">
    <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appt_id) ?>">
    <label for="reason">Reason for cancellation:</label><br>
    <textarea name="reason" id="reason" rows="5" cols="50" required></textarea><br><br>
    <button type="submit">Submit Cancellation</button>
  </form>
</body>
</html>
