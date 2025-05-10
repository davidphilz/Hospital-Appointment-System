<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: /Hospital-Appointment-System/Queuing Module/auth/login.php");
    exit();
}

include('../includes/db.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $patient_id = $_SESSION['id'];
    $problem_description = mysqli_real_escape_string($conn, $_POST['problem_description']);
    $urgency = mysqli_real_escape_string($conn, $_POST['urgency']);

    // Map problem description to hospital unit (basic keyword matching)
    $unit = 'General'; // Default unit
    if (stripos($problem_description, 'skin') !== false) {
        $unit = 'Dermatology';
    } elseif (stripos($problem_description, 'heart') !== false) {
        $unit = 'Cardiology';
    } elseif (stripos($problem_description, 'bone') !== false) {
        $unit = 'Orthopedics';
    }

    // Insert appointment into the database
    $sql = "INSERT INTO appointments (patient_id, problem_description, unit, priority_level, status) 
            VALUES ('$patient_id', '$problem_description', '$unit', '$urgency', 'pending')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Appointment booked successfully!'); window.location.href = 'index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .form-container {
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
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #2a2b38;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #444;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Book an Appointment</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="problem_description">Describe Your Problem:</label>
            <textarea id="problem_description" name="problem_description" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label for="urgency">Select Urgency Level:</label>
            <select id="urgency" name="urgency" required>
                <option value="Emergency">Emergency</option>
                <option value="High">High</option>
                <option value="Normal">Normal</option>
            </select>
        </div>
        <button type="submit">Book Appointment</button>
    </form>
</div>

</body>
</html>