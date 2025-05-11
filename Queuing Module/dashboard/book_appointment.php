<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['patient_id'])) {
    echo "Session not set. Redirecting to login.";
    header("Location: /Hospital-Appointment-System/Queuing Module/auth/login.php");
    exit();
}

include('../includes/db.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_SESSION['patient_id'];
    $problem_description = mysqli_real_escape_string($conn, $_POST['problem_description']);
    $urgency = mysqli_real_escape_string($conn, $_POST['urgency']);

    // Match keywords to department
    $unit = ''; // No default, ensure a match
    if (stripos($problem_description, 'skin') !== false) {
        $unit = 'Dermatology';
    } elseif (stripos($problem_description, 'heart') !== false) {
        $unit = 'Cardiology';
    } elseif (stripos($problem_description, 'bone') !== false) {
        $unit = 'Orthopedics';
    } elseif (stripos($problem_description, 'eye') !== false) {
        $unit = 'Ophthalmology';
    } elseif (stripos($problem_description, 'ear') !== false) {
        $unit = 'ENT';
    } elseif (stripos($problem_description, 'stomach') !== false) {
        $unit = 'Gastroenterology';
    } elseif (stripos($problem_description, 'headache') !== false) {
        $unit = 'Neurology';
    } elseif (stripos($problem_description, 'cough') !== false) {
        $unit = 'Pulmonology';
    } elseif (stripos($problem_description, 'diabetes') !== false) {
        $unit = 'Endocrinology';
    } elseif (stripos($problem_description, 'blood') !== false) {
        $unit = 'Hematology';
    } elseif (stripos($problem_description, 'cancer') !== false) {
        $unit = 'Oncology';
    } elseif (stripos($problem_description, 'pregnancy') !== false) {
        $unit = 'Obstetrics';
    } elseif (stripos($problem_description, 'child') !== false) {
        $unit = 'Pediatrics';
    }

    if ($unit === '') {
        echo "<script>alert('Unable to identify department. Please provide more specific details.'); history.back();</script>";
        exit();
    }

    // Determine next appointment slot
    $last_appointment_query = "SELECT appointment_date FROM appointments 
                               WHERE unit = '$unit' AND status = 'pending' 
                               ORDER BY appointment_date DESC LIMIT 1";
    $last_appointment_result = mysqli_query($conn, $last_appointment_query);

    if (mysqli_num_rows($last_appointment_result) > 0) {
        $last_appointment = mysqli_fetch_assoc($last_appointment_result);
        $last_appointment_date = new DateTime($last_appointment['appointment_date']);
        $next_appointment_date = $last_appointment_date->add(new DateInterval('PT30M'));
    } else {
        $next_appointment_date = new DateTime();
        $next_appointment_date->add(new DateInterval('PT5H'));
    }

    $formatted_appointment_date = $next_appointment_date->format('Y-m-d H:i:s');

    // Insert into DB
    $sql = "INSERT INTO appointments (patient_id, problem_description, unit, priority_level, status, appointment_date) 
            VALUES ('$patient_id', '$problem_description', '$unit', '$urgency', 'pending', '$formatted_appointment_date')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Appointment booked successfully! Scheduled for $formatted_appointment_date.'); window.location.href = 'index.php';</script>";
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
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Book an Appointment</h2>
        <form method="POST" action="">
            <label for="problem_description">Describe Your Problem:</label>
            <input type="text" id="problem_description" name="problem_description" required>

            <label for="urgency">Select Urgency Level:</label>
            <select id="urgency" name="urgency" required>
                <option value="Normal">Normal</option>
                <option value="Urgent">Urgent</option>
                <option value="Critical">Critical</option>
            </select>

            <button type="submit">Book Appointment</button>
        </form>
    </div>
</body>
</html>
