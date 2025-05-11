<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['patient_id'])) { // Check for 'patient_id' instead of 'id'
    header("Location: /Hospital-Appointment-System/Queuing Module/auth/login.php");
    exit();
}

if (!isset($_SESSION["email"])) {
    echo "<script>console.error('Session email is not set');</script>";
} else {
    echo "<script>console.log('Session email: " . htmlspecialchars($_SESSION["email"], ENT_QUOTES, "UTF-8") . "');</script>";
}

include('../includes/db.php'); // Database connection
$id = $_SESSION['patient_id']; // Use 'patient_id' instead of 'id'

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

// Fetch completed appointments with prices from bursary_reports
$completed_appointments_query = "
    SELECT a.id AS appointment_id, a.problem_description, a.unit, b.price, b.report_date 
    FROM appointments AS a
    JOIN bursary_reports AS b ON a.id = b.appointment_id
    WHERE a.patient_id = $id AND a.status = 'Completed'
";
$completed_appointments_result = mysqli_query($conn, $completed_appointments_query);

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
        .pay-button {
            background: #5cb85c;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .pay-button:hover {
            background: #4cae4c;
        }
        .logout-button {
            background: #d9534f;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .logout-button:hover {
            background: #c9302c;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
        <a href="/Hospital-Appointment-System/Queuing Module/auth/logout.php" class="logout-button">Logout</a>
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
                            <button class="pay-button" onclick="showPaymentOptions(<?php echo $appointment['id']; ?>)">Pay Now</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No upcoming appointments.</p>
    <?php } ?>

    <h3>Completed Appointments</h3>
    <?php if (mysqli_num_rows($completed_appointments_result) > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Problem Description</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Report Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($completed_appointment = mysqli_fetch_assoc($completed_appointments_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($completed_appointment['problem_description']); ?></td>
                        <td><?php echo htmlspecialchars($completed_appointment['unit']); ?></td>
                        <td>₦<?php echo htmlspecialchars($completed_appointment['price']); ?></td>
                        <td><?php echo htmlspecialchars($completed_appointment['report_date']); ?></td>
                        <td>
                            <button class="pay-button" onclick="showPaymentOptions(<?php echo $completed_appointment['appointment_id']; ?>)">Pay Now</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No completed appointments available for payment.</p>
    <?php } ?>
</div>

<script>
    let selectedAppointmentId = null;

    function showPaymentOptions(appointmentId) {
        selectedAppointmentId = appointmentId; // Store the selected appointment ID
        const modal = document.getElementById('paymentModal');
        modal.style.display = 'block';
    }

    function closeModal() {
        const modal = document.getElementById('paymentModal');
        modal.style.display = 'none';
    }

    function redirectToPayment(appointmentId, paymentMethod) {
        <?php if (isset($_SESSION["email"])) { ?>
            const userEmail = '<?php echo htmlspecialchars($_SESSION["email"], ENT_QUOTES, "UTF-8"); ?>'; // Safely escape the email
        <?php } else { ?>
            alert('User email is not available. Please log in again.');
            return;
        <?php } ?>

        // Construct the payment URL based on the selected payment method
        let paymentUrl = '';
        switch (paymentMethod) {
            case 'flutterwave':
                paymentUrl = `http://localhost:3000/user-dashboard/payment?email=${userEmail}&appointmentId=${appointmentId}`;
                break;
            case 'bank-transfer':
                paymentUrl = `http://localhost:3000/user-dashboard/bank-transfer?email=${userEmail}&appointmentId=${appointmentId}`;
                break;
            case 'cash-payment':
                paymentUrl = `http://localhost:3000/user-dashboard/cash-payment?email=${userEmail}&appointmentId=${appointmentId}`;
                break;
            case 'hmo-claim':
                paymentUrl = `http://localhost:3000/user-dashboard/hmo-payment?email=${userEmail}&appointmentId=${appointmentId}`;
                break;
            default:
                alert('Invalid payment method selected.');
                return;
        }

        // Redirect to the payment module
        window.location.href = paymentUrl;
    }
</script>

<!-- Payment Options Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Select Payment Option</h3>
        <ul>
            <li><button onclick="redirectToPayment(selectedAppointmentId, 'flutterwave')">Flutterwave</button></li>
            <li><button onclick="redirectToPayment(selectedAppointmentId, 'bank-transfer')">Offline Bank Transfer</button></li>
            <li><button onclick="redirectToPayment(selectedAppointmentId, 'cash-payment')">Offline Cash Payment</button></li>
            <li><button onclick="redirectToPayment(selectedAppointmentId, 'hmo-claim')">HMO Claim</button></li>
        </ul>
    </div>
</div>

</body>
</html>
