<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: /Hospital-Appointment-System/Queuing Module/auth/login.php");
    exit();
}

include('../includes/db.php'); // adjust if needed
$id = $_SESSION['id'];

$query = "SELECT name, email FROM patients WHERE id = $id";
$result = mysqli_query($conn, $query);
$patient = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .dashboard {
            max-width: 600px;
            margin: 80px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2a2b38;
        }
        .info {
            margin: 15px 0;
        }
        .actions {
            margin-top: 20px;
        }
        .actions a {
            display: inline-block;
            text-decoration: none;
            background: #2a2b38;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-right: 10px;
        }
        .actions a:hover {
            background: #444;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h2>Welcome, <?php echo htmlspecialchars($patient['name']); ?>!</h2>

    <div class="info">
        <strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?>
    </div>

    <div class="info">
        <strong>Your Patient ID:</strong> <?php echo $id; ?>
    </div>

    <div class="actions">
        <a href="book_appointment.php">Book Appointment</a>
        <a href="/Hospital-Appointment-System/Queuing Module/auth/logout.php">Logout</a>
    </div>
</div>

</body>
</html>
