<?php
session_start();
include("../include/connection.php"); 

$total_patients = 7;
$total_appointments_today = 5;
$total_appointments_month = 920;
$missed_appointments = 15;
$cancelled_appointments = 10;

$departments = [
    "Emergency Unit" => 130,
    "ICU" => 90,
    "General Ward" => 280,
    "Pharmacy" => 150,
    "Lab Services" => 110
];

$total_alerts = 2;
$expired_alerts = 43;
$equipment_requests = 7;
$restock_requests = 7;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Total Report - Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .section-title { font-size: 1.5em; margin: 20px 0 10px; }
    </style>
</head>
<body>
    <h1>Total Report</h1>

    <div class="dashboard">
        <div class="card">
            <h3>Total Patients</h3>
            <p><?= $total_patients ?></p>
        </div>
        <div class="card">
            <h3>Appointments Today</h3>
            <p><?= $total_appointments_today ?></p>
        </div>
        <div class="card">
            <h3>Appointments This Month</h3>
            <p><?= $total_appointments_month ?></p>
        </div>
        <div class="card">
            <h3>Missed Appointments</h3>
            <p><?= $missed_appointments ?></p>
        </div>
        <div class="card">
            <h3>Cancelled Appointments</h3>
            <p><?= $cancelled_appointments ?></p>
        </div>
    </div>

    <h2 class="section-title">Department-wise Appointments</h2>
    <canvas id="departmentChart" width="400" height="200"></canvas>

    <h2 class="section-title">Alerts Summary</h2>
    <div class="dashboard">
        <div class="card"><h3>Total Alerts</h3><p><?= $total_alerts ?></p></div>
        <div class="card"><h3>Expired Items</h3><p><?= $expired_alerts ?></p></div>
        <div class="card"><h3>Equipment Requests</h3><p><?= $equipment_requests ?></p></div>
        <div class="card"><h3>Restock Requests</h3><p><?= $restock_requests ?></p></div>
    </div>

    <script>
        const ctx = document.getElementById('departmentChart').getContext('2d');
        const departmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($departments)) ?>,
                datasets: [{
                    label: 'Appointments',
                    data: <?= json_encode(array_values($departments)) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
