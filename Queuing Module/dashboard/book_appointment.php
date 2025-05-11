<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['patient_id'])) {
    echo "Session not set. Redirecting to login.";
    header("Location: /Hospital-Appointment-System/Queuing Module/auth/login.php");
    exit();
} else {
    echo "Session is active. Patient ID: " . $_SESSION['patient_id'];
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
    } elseif (stripos($problem_description, 'mental') !== false || stripos($problem_description, 'depression') !== false) {
        $unit = 'Psychiatry';
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
        // Update the total patient count in session
        $total_patient_query = "SELECT COUNT(*) AS total FROM appointments";
        $total_patient_result = mysqli_query($conn, $total_patient_query);
        $total_patient_data = mysqli_fetch_assoc($total_patient_result);
        $_SESSION['total_patient'] = $total_patient_data['total'];

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
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .form-container {
            max-width: 600px;
            margin: 80px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #555;
        }
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            resize: vertical;
            font-size: 15px;
        }
        textarea {
            min-height: 100px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #2a2b38;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #444;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Book an Appointment</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="problem_description">Describe Your Problem:</label>
            <textarea id="problem_description" name="problem_description" required></textarea>
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
