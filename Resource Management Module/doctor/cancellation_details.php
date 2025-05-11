<?php
session_start();
include("../include/connection.php");
include("../include/header.php");

if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../doctorlogin.php");
    exit();
}

if (!isset($_GET['appointment_id'])) {
    echo "Invalid request.";
    exit();
}

$appointment_id = intval($_GET['appointment_id']);
$doctor_id = $_SESSION['doctor_id'];

$query = $connect->prepare("SELECT reason FROM cancellations WHERE appointment_id = ? AND doctor_id = ?");
$query->bind_param("ii", $appointment_id, $doctor_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancellation Details</title>
    <link rel="stylesheet" href="styles.css">  <!-- Link to external CSS file if needed -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .content h3 {
            color: #333;
        }

        .content p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .alert {
            padding: 10px;
            background-color: #f44336;
            color: white;
            margin-top: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Cancellation Details</h1>
    </div>

    <div class="content">
        <?php
        if ($result->num_rows === 0) {
            echo "<div class='alert'>No cancellation details found for this appointment.</div>";
        } else {
            $row = $result->fetch_assoc();
            echo "<h3>Cancellation Reason</h3>";
            echo "<p>" . htmlspecialchars($row['reason']) . "</p>";
        }
        ?>
        
        <a href="appointment.php" class="button">Back to Appointment</a>
    </div>
</div>

</body>
</html>
