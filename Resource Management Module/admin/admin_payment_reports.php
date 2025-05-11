<!-- filepath: c:\xampp\htdocs\Hospital-Appointment-System\Resource Management Module\admin\admin_payment_reports.php -->
<?php
session_start();
include("../include/header.php");
include("../include/connection.php");

// Fetch payment reports
$query = "SELECT * FROM payments ORDER BY time_completed DESC";
$result = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center">Payment Reports</h3>

    <!-- Display Success or Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive mt-4">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Unit</th>
                    <th>Problem Description</th>
                    <th>Amount Paid</th>
                    <th>Time Completed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                        <td><?= htmlspecialchars($row['problem_description']) ?></td>
                        <td>₦<?= number_format($row['amount_paid'], 2) ?></td>
                        <td><?= htmlspecialchars($row['time_completed']) ?></td>
                        <td>
                            <form method="POST" action="close_appointment.php">
                                <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">Appointment Closed</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>