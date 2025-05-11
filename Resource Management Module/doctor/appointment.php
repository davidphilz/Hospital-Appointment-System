<?php
session_start();
include("../include/connection.php");
include("../include/header.php");

if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../doctorlogin.php");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];

// Handle update to 'Completed'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_appointment_id'])) {
    $complete_id = intval($_POST['complete_appointment_id']);
    $update = $connect->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ? AND doctor_id = ?");
    $update->bind_param("ii", $complete_id, $doctor_id);
    $update->execute();
}

// Handle update to 'Ongoing'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ongoing_appointment_id'])) {
    $ongoing_id = intval($_POST['ongoing_appointment_id']);
    $update = $connect->prepare("UPDATE appointments SET status = 'Ongoing' WHERE id = ? AND doctor_id = ?");
    $update->bind_param("ii", $ongoing_id, $doctor_id);
    $update->execute();
}

// Fetch unread cancellation notifications
$notifStmt = $connect->prepare(
    "SELECT c.appointment_id, c.reason, c.cancelled_by, c.created_at, n.id AS notif_id
     FROM doctor_notifications n
     JOIN appointment_cancellations c ON n.cancellation_id = c.id
     WHERE n.doctor_id = ? AND n.is_read = 0
     ORDER BY c.created_at DESC"
);
$notifStmt->bind_param("i", $doctor_id);
$notifStmt->execute();
$notifications = $notifStmt->get_result();

// Fetch all appointments for doctor
$query = $connect->prepare("SELECT * FROM appointments WHERE doctor_id = ? ORDER BY appointment_date DESC");
$query->bind_param("i", $doctor_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard - Appointments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
         body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: #fff;
        }
        .sidebar a {
            color: #ddd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: #fff;
            text-decoration: none;
        }
        .dashboard-card {
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
            height: 160px;
        }
        .dashboard-card:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .dashboard-card i {
            font-size: 40px;
        }
        .bg-success { background: linear-gradient(45deg, #28a745, #218838); }
        .bg-info { background: linear-gradient(45deg, #17a2b8, #117a8b); }
        .bg-warning { background: linear-gradient(45deg, #ffc107, #e0a800); }
        .bg-danger { background: linear-gradient(45deg, #dc3545, #c82333); }
        .bg-primary { background: linear-gradient(45deg, #007bff, #0069d9); }
        .bg-secondary { background: linear-gradient(45deg, #6c757d, #5a6268); }
        .dashboard-heading { color: #444; font-weight: 600; margin-bottom: 1rem; }
        .dashboard-subtext { color: #777; margin-bottom: 2rem; }
        .dashboard-card h5 { margin-bottom: 8px; font-size: 1.1rem; font-weight: 600; }
        .big-number { font-size: 1.7rem; font-weight: bold; margin-bottom: 6px; }

        /* Center the content */
        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 20px;
        }

        .appointments-container {
            width: 80%;
            max-width: 1200px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .appointment-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid px-0">
    <div class="row gx-0 vh-100">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3">
            <?php include("sidenav.php"); ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 content-wrapper">
            <div class="appointments-container">
                <h3 class="text-center"><?php echo htmlspecialchars($_SESSION['doctor_name']); ?></h3>
                <h4 class="my-4 text-center">My Appointments</h4>

                <!-- Cancellation Notifications -->
                <?php if ($notifications->num_rows > 0): ?>
                    <div class="mb-4">
                        <h5>New Cancellation Notices</h5>
                        <?php while ($note = $notifications->fetch_assoc()): ?>
                            <div class="alert alert-danger">
                                <strong>Appointment #<?= htmlspecialchars($note['appointment_id']) ?> cancelled on <?= htmlspecialchars($note['created_at']) ?></strong><br>
                                <em>By: <?= htmlspecialchars($note['cancelled_by']) ?></em><br>
                                <p>Reason: <?= nl2br(htmlspecialchars($note['reason'])) ?></p>
                            </div>
                            <?php
                            // mark as read
                            $mark = $connect->prepare("UPDATE doctor_notifications SET is_read = 1 WHERE id = ?");
                            $mark->bind_param("i", $note['notif_id']);
                            $mark->execute();
                            ?>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $pid = $row['patient_id'];
                        $aid = $row['id'];
                        $dt  = htmlspecialchars($row['appointment_date']);
                        $st  = htmlspecialchars($row['status']);

                        // Fetch patient name
                        $pq = $connect->prepare("SELECT name FROM patients WHERE id = ?");
                        $pq->bind_param("i", $pid);
                        $pq->execute();
                        $pn = $pq->get_result()->fetch_assoc()['name'] ?? 'Unknown';
                    ?>
                    <div class="card appointment-card">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($pn); ?></h5>
                            <p><strong>Date:</strong> <?php echo $dt; ?></p>
                            <p>
                                <strong>Status:</strong>
                                <span class="<?php echo $st==='Completed'?'text-secondary':($st==='Pending'?'text-warning':($st==='Ongoing'?'text-info':'text-danger')); ?>">
                                    <?php echo $st; ?>
                                </span>
                            </p>

                            <?php
                            if ($st === 'Completed') {
                                echo '<p class="text-muted mt-2">Treatment already completed.</p>';
                            } else {
                                if ($st === 'Pending') {
                                    echo '<form method="POST" class="mt-2 d-inline">
                                            <input type="hidden" name="ongoing_appointment_id" value="' . $aid . '">
                                            <button type="submit" class="btn btn-info btn-sm">Mark as Ongoing</button>
                                          </form>';
                                }
                                if ($st === 'Ongoing') {
                                    echo '<form method="POST" class="mt-2 d-inline">
                                            <input type="hidden" name="complete_appointment_id" value="' . $aid . '">
                                            <button type="submit" class="btn btn-success btn-sm">Mark as Completed</button>
                                          </form>';
                                }
                                if ($st === 'Cancelled') {
                                    // Inline fetch of cancellation details
                                    $cs = $connect->prepare(
                                        "SELECT cancelled_by, reason, created_at FROM appointment_cancellations WHERE appointment_id = ? ORDER BY created_at DESC LIMIT 1"
                                    );
                                    $cs->bind_param("i", $aid);
                                    $cs->execute();
                                    $cancel = $cs->get_result()->fetch_assoc();

                                    // Only display if we have details
                                    if ($cancel) {
                                        echo '<div class="mt-3 p-3 border rounded bg-light">' .
                                             '<p><strong>Cancelled By:</strong> ' . htmlspecialchars($cancel['cancelled_by']) . '</p>' .
                                             '<p><strong>On:</strong> ' . htmlspecialchars($cancel['created_at']) . '</p>' .
                                             '<p><strong>Reason:</strong><br>' . nl2br(htmlspecialchars($cancel['reason'])) . '</p>' .
                                             '</div>';
                                    } else {
                                        echo '<p class="mt-2 text-muted">No cancellation details available.</p>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center">No appointments yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
