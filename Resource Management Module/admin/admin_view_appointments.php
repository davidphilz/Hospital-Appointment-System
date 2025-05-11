<?php
session_start();
include("../include/header.php");
include("../include/connection.php");

// Handle all POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set Price
    if (isset($_POST['set_price'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $price = (float)$_POST['price'];
        $stmt = $connect->prepare("UPDATE appointments SET price = ? WHERE id = ?");
        $stmt->bind_param("di", $price, $appointment_id);
        $stmt->execute();

        $stmt = $connect->prepare("INSERT INTO bursary_reports (appointment_id, price, report_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("id", $appointment_id, $price);
        $stmt->execute();

        echo '<div class="alert alert-success">Price has been updated and report sent to bursary.</div>';
    }

    // Assign Doctor
    if (isset($_POST['assign_doctor'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $doctor_id = (int)$_POST['doctor_id'];
        $stmt = $connect->prepare("UPDATE appointments SET doctor_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $doctor_id, $appointment_id);
        $stmt->execute();
        echo '<div class="alert alert-success">Doctor has been assigned to the appointment.</div>';
    }

    // Update Status
    if (isset($_POST['update_status'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $new_status = mysqli_real_escape_string($connect, $_POST['new_status']);
        $allowed = ['Pending','Ongoing','Completed','Cancelled'];
        if (in_array($new_status, $allowed)) {
            $stmt = $connect->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $appointment_id);
            $stmt->execute();
            echo '<div class="alert alert-success">Appointment status updated to '.htmlspecialchars($new_status).'.</div>';
        } else {
            echo '<div class="alert alert-danger">Invalid status selected.</div>';
        }
    }

    // Cancellation by Admin
    if (isset($_POST['cancel_appointment_id'])) {
        $appointment_id = (int)$_POST['cancel_appointment_id'];
        $reason = trim($_POST['cancellation_reason']);
        if ($reason === '') {
            echo '<div class="alert alert-danger">Cancellation reason is required.</div>';
        } else {
            // Record cancellation
            $stmt = $connect->prepare("INSERT INTO appointment_cancellations (appointment_id, cancelled_by, reason) VALUES (?, 'admin', ?)");
            $stmt->bind_param("is", $appointment_id, $reason);
            $stmt->execute();
            $cancellation_id = $stmt->insert_id;

            // Update appointment status
            $stmt = $connect->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();

            // Notify doctor if assigned
            $stmt = $connect->prepare("SELECT doctor_id FROM appointments WHERE id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $doc_id = $stmt->get_result()->fetch_assoc()['doctor_id'] ?? null;
            if ($doc_id) {
                $stmt = $connect->prepare("INSERT INTO doctor_notifications (doctor_id, cancellation_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $doc_id, $cancellation_id);
                $stmt->execute();
            }

            echo '<div class="alert alert-danger">Appointment #'. $appointment_id .' cancelled and doctor notified.</div>';
        }
    }
}

// Ensure patient selected
if (empty($_GET['patient_id'])) {
    echo '<div class="alert alert-warning">No patient selected.</div>';
    exit();
}
$patient_id = (int)$_GET['patient_id'];

// Fetch appointments
$stmt = $connect->prepare(
    "SELECT a.id,
            a.problem_description,
            a.appointment_date,
            a.price,
            a.doctor_id,
            a.status,
            CONCAT(d.firstname,' ',d.surname) AS doctor_name
     FROM appointments a
     LEFT JOIN doctors d ON a.doctor_id=d.id
     WHERE a.patient_id=?
     ORDER BY a.appointment_date DESC"
);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$res = $stmt->get_result();

// Fetch doctors list
$doctor_res = mysqli_query($connect, "SELECT id, CONCAT(firstname,' ',surname) AS doctor_name FROM doctors");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointments for Patient #<?= $patient_id ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .main-content {
        margin: 0 auto;
        max-width: 1200px;
        padding: 20px;
    }

    /* Header */
    .main-content h3 {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #2c3e50;
    }

    /* Table styling */
    .table-responsive {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    .table thead th {
        background-color: #2980b9;
        color: #ecf0f1;
        border: none;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
    }
    .table tbody tr td {
        vertical-align: middle;
        font-size: 0.9rem;
        color: #34495e;
    }
    .table tbody tr:nth-child(even) { background-color: #f9f9f9; }

    /* Button styles */
    .btn-sm {
        border-radius: 0.3rem;
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
    }
    .btn-primary { background-color: #2980b9; border-color: #2980b9; }
    .btn-primary:hover { background-color: #217dbb; }
    .btn-success { background-color: #27ae60; border-color: #27ae60; }
    .btn-success:hover { background-color: #229954; }
    .btn-warning { background-color: #f39c12; border-color: #f39c12; color: #fff; }
    .btn-warning:hover { background-color: #d68910; }
    .btn-danger { background-color: #e74c3c; border-color: #e74c3c; }
    .btn-danger:hover { background-color: #cf4436; }
    .btn-secondary { background-color: #7f8c8d; border-color: #7f8c8d; }
    .btn-secondary:hover { background-color: #707b7c; }

    /* Status row colors */
    .table-success td { background-color: #e9f7ef; }
    .table-info td { background-color: #e8f8ff; }
    .table-warning td { background-color: #fcf3cf; }
    .table-danger td { background-color: #fdecea; }

    /* Form elements */
    .form-control-sm, .form-select-sm {
        height: calc(1.5em + 0.6rem + 2px);
        padding: 0.4rem 0.6rem;
    }
     body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
    }
    .sidebar {
      background-color: #343a40;
      min-height: 100vh;
      color: #fff;
    }
    .sidebar a {
      color: #ddd;
      text-decoration: none;
      display: block;
      padding: 10px 15px;
      transition: 0.3s;
    }
    .sidebar a:hover {
      background-color: #495057;
      color: #fff;
      text-decoration: none;
    }
    .dashboard-card {
      color: #fff;
      padding: 20px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 20px;
      height: 160px;
    }
    .dashboard-card:hover {
      transform: scale(1.03);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .dashboard-card i {
      font-size: 40px;
    }
    .bg-success {
      background: linear-gradient(45deg, #28a745, #218838);
    }
    .bg-info {
      background: linear-gradient(45deg, #17a2b8, #117a8b);
    }
    .bg-warning {
      background: linear-gradient(45deg, #ffc107, #e0a800);
    }
    .bg-danger {
      background: linear-gradient(45deg, #dc3545, #c82333);
    }
    .bg-primary {
      background: linear-gradient(45deg, #007bff, #0069d9);
    }
    .bg-secondary {
      background: linear-gradient(45deg, #6c757d, #5a6268);
    }
    .dashboard-heading {
      color: #444;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .dashboard-subtext {
      color: #777;
      margin-bottom: 2rem;
    }
    .dashboard-card h5 {
      margin-bottom: 8px;
      font-size: 1.1rem;
      font-weight: 600;
    }
    .big-number {
      font-size: 1.7rem;
      font-weight: bold;
      margin-bottom: 6px;
    }
    #calendar-wrapper {
  max-width: 320px;
  margin: 0 auto;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  overflow: hidden;
}

#calendar-header {
  background: #dc3545;
  color: #fff;
  padding: 10px 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

#calendar-header h2 {
  margin: 0 15px;
  font-size: 1.2rem;
}

#calendar-header .nav-btn {
  cursor: pointer;
  font-weight: bold;
  color: white;
  font-size: 1.2rem;
}

#calendar-days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);  /* 7 columns for the days of the week */
  text-align: center;
  padding: 5px 0;
  background-color: #f7f7f7; /* Light grey background */
}

#calendar-days div {
  font-weight: bold;
  color: #dc3545;
}

#calendar-dates {
  display: grid;
  grid-template-columns: repeat(7, 1fr);  /* 7 columns for the dates */
  text-align: center;
  padding: 5px;
}

#calendar-dates div {
  margin: 5px;
  cursor: pointer;
  border-radius: 4px;
  line-height: 2em;
  transition: background 0.2s;
  display: flex;
  justify-content: center;
  align-items: center;
}

#calendar-dates div:hover {
  background: #f1f1f1;
}

.today {
  background: #dc3545 !important;
  color: #fff !important;
}

#calendar-dates div.empty {
  visibility: hidden; /* Hide the empty grid cells before the start of the month */
}
</style>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar col-md-2 p-3">
        <?php include("sidenav.php"); ?>
    </nav>
    <div class="main-content">
        <h3>Appointments for Patient #<?= $patient_id ?>
            <a href="view_patients.php" class="btn btn-secondary btn-sm float-end">« Back</a>
        </h3>

        <?php if ($res->num_rows === 0): ?>
            <div class="alert alert-info text-center">No appointments found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead><tr>
                        <th>ID</th><th>Problem</th><th>Date</th><th>Doctor</th>
                        <th>Status</th><th>Price Paid</th><th>Set Price</th>
                        <th>Assign Doctor</th><th>Update/Cancel</th>
                    </tr></thead>
                    <tbody>
                    <?php while ($row = $res->fetch_assoc()):
                        $status_class = match($row['status']){
                            'Completed'=>'table-success','Ongoing'=>'table-info',
                            'Cancelled'=>'table-danger',default=>'table-warning'};
                    ?>
                        <tr class="<?= $status_class ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['problem_description']) ?></td>
                            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                            <td><?= $row['doctor_name']?htmlspecialchars($row['doctor_name']):'<em>Not Assigned</em>' ?></td>
                            <td><?= $row['status'] ?></td>
                            <td><?= is_null($row['price'])?'<em>Not Set</em>':'₦'.number_format($row['price'],2) ?></td>
                            <td>
                                <form method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                    <input type="number" name="price" class="form-control form-control-sm me-2" placeholder="Enter price" required>
                                    <button type="submit" name="set_price" class="btn btn-sm btn-primary">Set</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                    <select name="doctor_id" class="form-select form-select-sm mb-2" required>
                                        <option value="">Select Doctor</option>
                                        <?php mysqli_data_seek($doctor_res,0); while($d=mysqli_fetch_assoc($doctor_res)): ?>
                                            <option value="<?= $d['id'] ?>" <?= $row['doctor_id']==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['doctor_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" name="assign_doctor" class="btn btn-sm btn-success">Assign</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" class="d-flex align-items-center mb-2">
                                    <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                    <select name="new_status" class="form-select form-select-sm me-2" required>
                                        <option value="Pending" <?= $row['status']==='Pending'?'selected':'' ?>>Pending</option>
                                        <option value="Ongoing" <?= $row['status']==='Ongoing'?'selected':'' ?>>Ongoing</option>
                                        <option value="Completed" <?= $row['status']==='Completed'?'selected':'' ?>>Completed</option>
                                        <option value="Cancelled" <?= $row['status']==='Cancelled'?'selected':'' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-warning">Update</button>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="cancel_appointment_id" value="<?= $row['id'] ?>">
                                    <textarea name="cancellation_reason" class="form-control form-control-sm mb-1" rows="2" placeholder="Reason for cancellation..." required></textarea>
                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
