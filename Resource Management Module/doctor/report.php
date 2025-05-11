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

// Handle redirection to cancellation details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_cancellation_details_id'])) {
    $cancellation_id = intval($_POST['view_cancellation_details_id']);
    header("Location: cancellation_details.php?appointment_id=" . $cancellation_id);
    exit();
}

// Fetch upcoming appointments
$query_upcoming = $connect->prepare("SELECT * FROM appointments WHERE doctor_id = ? AND appointment_date > NOW() ORDER BY appointment_date ASC LIMIT 3");
$query_upcoming->bind_param("i", $doctor_id);
$query_upcoming->execute();
$upcoming_appointments = $query_upcoming->get_result();

// Fetch performance stats
$completed_count = $connect->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctor_id AND status = 'Completed'")->fetch_row()[0];
$pending_count = $connect->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctor_id AND status = 'Pending'")->fetch_row()[0];
$ongoing_count = $connect->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctor_id AND status = 'Ongoing'")->fetch_row()[0];

// Fetch recent feedback
$query_feedback = $connect->prepare("SELECT feedback, rating FROM feedback WHERE doctor_id = ? ORDER BY created_at DESC LIMIT 3");
$query_feedback->bind_param("i", $doctor_id);
$query_feedback->execute();
$feedback_result = $query_feedback->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    </style>

    <script>
        const completed = Number(<?php echo isset($completed_count) ? $completed_count : 0; ?>);
    const pending = Number(<?php echo isset($pending_count) ? $pending_count : 0; ?>);
    const ongoing = Number(<?php echo isset($ongoing_count) ? $ongoing_count : 0; ?>);

    document.addEventListener("DOMContentLoaded", function () {
        const chartCanvas = document.getElementById('appointmentChart');
        if (chartCanvas) {
            const ctx = chartCanvas.getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Completed', 'Pending', 'Ongoing'],
                    datasets: [{
                        label: 'Appointments',
                        data: [completed, pending, ongoing],
                        backgroundColor: ['#28a745', '#ffc107', '#17a2b8'],
                        borderColor: ['#1e7e34', '#d39e00', '#117a8b'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Appointment Status Overview'
                        }
                    }
                }
            });
        }
    });

    function downloadChart() {
        const chartCanvas = document.getElementById('appointmentChart');
        if (chartCanvas) {
            const link = document.createElement('a');
            link.href = chartCanvas.toDataURL('image/png');
            link.download = 'appointment_chart.png';
            link.click();
        }
    }

    function downloadCSV() {
        const csv = `Status,Count\nCompleted,${completed}\nPending,${pending}\nOngoing,${ongoing}`;
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'appointment_stats.csv';
        link.click();
    }
    </script>
</head>
<body>

<div class="container-fluid px-0">
    <div class="row gx-0 vh-100">

        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3">
            <?php include("sidenav.php"); ?>
        </div>

        <!-- Main content -->
        <div class="col-md-10">
            <div class="container mt-5">
                <!-- <h3 class="text-center">Welcome, Dr. <?php echo htmlspecialchars($_SESSION['doctor_name']); ?></h3> -->

                <!-- Upcoming Appointments -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        Upcoming Appointments
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php while ($upcoming = $upcoming_appointments->fetch_assoc()): ?>
                                <li class="mb-2">
                                    <strong><?php echo htmlspecialchars($upcoming['appointment_date']); ?></strong><br>
                                    Patient: <?php echo htmlspecialchars($upcoming['patient_id']); ?> <br>
                                    Status: <?php echo htmlspecialchars($upcoming['status']); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <a href="appointment.php" class="btn btn-info btn-sm">View All Appointments</a>
                    </div>
                </div>

                <!-- Performance Stats -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        Performance Overview
                    </div>
                    <div class="card-body">
                        <p><strong>Appointments Completed:</strong> <?php echo $completed_count; ?></p>
                        <p><strong>Appointments Pending:</strong> <?php echo $pending_count; ?></p>
                        <p><strong>Appointments Ongoing:</strong> <?php echo $ongoing_count; ?></p>
                    </div>
                </div>

                <!-- Chart + Download -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        Visual Report & Download
                    </div>
                    <div class="card-body">
                        <canvas id="appointmentChart" height="100"></canvas>
                        <button onclick="downloadChart()" class="btn btn-primary mt-3">Download Chart as Image</button>
                        <button onclick="downloadCSV()" class="btn btn-secondary mt-3">Download Stats as CSV</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
