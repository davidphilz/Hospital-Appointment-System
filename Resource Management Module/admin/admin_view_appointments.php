<?php
session_start();
include('../includes/db.php');

// Optional: Protect admin route
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit();
// }

$query = "SELECT a.*, p.name AS patient_name 
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.id 
          ORDER BY appointment_date ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - View Appointments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: fadeIn 0.6s ease-in-out;
        }
        th, td {
            padding: 14px;
            text-align: left;
        }
        th {
            background: #2a2b38;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .unit-General { background-color: #e0e0e0; }
        .unit-Cardiology { background-color: #ffb3b3; }
        .unit-Dermatology { background-color: #ffe0b3; }
        .unit-Orthopedics { background-color: #d1c4e9; }
        .unit-Ophthalmology { background-color: #b3e5fc; }
        .unit-ENT { background-color: #f8bbd0; }
        .unit-Gastroenterology { background-color: #dcedc8; }
        .unit-Neurology { background-color: #f0f4c3; }
        .unit-Pulmonology { background-color: #c8e6c9; }
        .unit-Endocrinology { background-color: #ffecb3; }
        .unit-Hematology { background-color: #f48fb1; }
        .unit-Oncology { background-color: #ce93d8; }
        .unit-Obstetrics { background-color: #ffe082; }
        .unit-Pediatrics { background-color: #80deea; }
        .unit-Psychiatry { background-color: #cfd8dc; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #2a2b38;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
    <a class="back-btn" href="admin_dashboard.php">← Back to Dashboard</a>
    <h2>All Appointments</h2>
    <table>
        <thead>
            <tr>
                <th>Patient Name</th>
                <th>Problem</th>
                <th>Department</th>
                <th>Urgency</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr class="unit-<?php echo htmlspecialchars($row['unit']); ?>">
                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['problem_description']); ?></td>
                    <td><?php echo htmlspecialchars($row['unit']); ?></td>
                    <td><?php echo htmlspecialchars($row['priority_level']); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>