<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: /Hospital-Appointment-System/Queuing Module/auth/login.php");
    exit();
}

include('../includes/db.php'); // Database connection
$id = $_SESSION['id'];

// Fetch patient details
$query = "SELECT name, email FROM patients WHERE id = $id";
$result = mysqli_query($conn, $query);
$patient = mysqli_fetch_assoc($result);

// Fetch upcoming appointments for the logged-in user
$appointments_query = "SELECT id, problem_description, unit, priority_level, status, appointment_date 
                       FROM appointments 
                       WHERE patient_id = $id AND status = 'pending'
                       ORDER BY appointment_date ASC";
$appointments_result = mysqli_query($conn, $appointments_query);

// Handle appointment cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_appointment_id'])) {
    $appointment_id = intval($_POST['cancel_appointment_id']);
    $cancel_query = "UPDATE appointments SET status = 'canceled' WHERE id = $appointment_id AND patient_id = $id";
    if (mysqli_query($conn, $cancel_query)) {
        echo "<script>alert('Appointment canceled successfully.'); window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Failed to cancel the appointment. Please try again.');</script>";
    }
}
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background: #2a2b38;
            color: white;
        }
        .cancel-button {
            background: #d9534f;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .cancel-button:hover {
            background: #c9302c;
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

    <h3>Upcoming Appointments</h3>
    <?php if (mysqli_num_rows($appointments_result) > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Problem Description</th>
                    <th>Unit</th>
                    <th>Priority</th>
                    <th>Appointment Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($appointment = mysqli_fetch_assoc($appointments_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['problem_description']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['unit']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['priority_level']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="cancel_appointment_id" value="<?php echo $appointment['id']; ?>">
                                <button type="submit" class="cancel-button">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No upcoming appointments.</p>
    <?php } ?>
</div>

</body>
</html>
