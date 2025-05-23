<?php
session_set_cookie_params([
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: /Hospital-Appointment-System/Queuing Module/auth/login.php");
    exit();
}

include __DIR__ . '/../includes/db.php';

$problem_description = '';
$urgency = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_SESSION['id'];
    $problem_description = mysqli_real_escape_string($conn, $_POST['problem_description']);
    $urgency = mysqli_real_escape_string($conn, $_POST['urgency']);

    $unit = '';
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
    } elseif (stripos($problem_description, 'child') !== false || stripos($problem_description, 'kid') !== false) {
        $unit = 'Pediatrics';
    } elseif (stripos($problem_description, 'mental') !== false || stripos($problem_description, 'depression') !== false) {
        $unit = 'Psychiatry';
    }

    if ($unit === '') {
        echo "<script>alert('Unable to identify department. Please provide more specific details.'); history.back();</script>";
        exit();
    }

    // Determine next appointment
    $last_q = "
        SELECT appointment_date 
        FROM appointments 
        WHERE unit = '$unit' AND status = 'pending' 
        ORDER BY appointment_date DESC 
        LIMIT 1
    ";
    $last_r = mysqli_query($conn, $last_q);

    if (mysqli_num_rows($last_r) > 0) {
        $last_row = mysqli_fetch_assoc($last_r);
        $last_dt = new DateTime($last_row['appointment_date']);
        $next_dt = $last_dt->add(new DateInterval('PT30M'));
    } else {
        $next_dt = (new DateTime())->add(new DateInterval('PT5H'));
    }

    $formatted_appointment_date = $next_dt->format('Y-m-d H:i:s');

    $sql = "
      INSERT INTO appointments 
        (patient_id, problem_description, unit, priority_level, status, appointment_date) 
      VALUES 
        ('$patient_id', '$problem_description', '$unit', '$urgency', 'pending', '$formatted_appointment_date')
    ";

    if (mysqli_query($conn, $sql)) {
        // Update the total patient count in session
        $total_patient_query = "SELECT COUNT(*) AS total FROM appointments";
        $total_patient_result = mysqli_query($conn, $total_patient_query);
        $total_patient_data = mysqli_fetch_assoc($total_patient_result);
        $_SESSION['total_patient'] = $total_patient_data['total'];

        echo "<script>alert('Appointment booked successfully! Scheduled for $formatted_appointment_date.'); window.location.href = 'index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
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
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
