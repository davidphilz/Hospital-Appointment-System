<?php
session_start();
include("../include/header.php");
include("../include/connection.php");

// Handle price input, doctor assignment, and status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_price'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $price = (float)$_POST['price'];

        $update_sql = "UPDATE appointments SET price = $price WHERE id = $appointment_id";
        mysqli_query($connect, $update_sql);

        $bursary_report_sql = "
            INSERT INTO bursary_reports (appointment_id, price, report_date)
            VALUES ($appointment_id, $price, NOW())
        ";
        mysqli_query($connect, $bursary_report_sql);

        echo '<div class="alert alert-success">Price has been updated and report sent to bursary.</div>';
    }

    if (isset($_POST['assign_doctor'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $doctor_id = (int)$_POST['doctor_id'];

        $assign_doctor_sql = "UPDATE appointments SET doctor_id = $doctor_id WHERE id = $appointment_id";
        mysqli_query($connect, $assign_doctor_sql);

        echo '<div class="alert alert-success">Doctor has been assigned to the appointment.</div>';
    }

    if (isset($_POST['update_status'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $new_status = mysqli_real_escape_string($connect, $_POST['new_status']);
        $allowed_statuses = ['Pending', 'Ongoing', 'Completed', 'Cancelled'];

        if (in_array($new_status, $allowed_statuses)) {
            $update_status_sql = "UPDATE appointments SET status = '$new_status' WHERE id = $appointment_id";
            mysqli_query($connect, $update_status_sql);
            echo '<div class="alert alert-success">Appointment status updated to ' . htmlspecialchars($new_status) . '.</div>';
        } else {
            echo '<div class="alert alert-danger">Invalid status selected.</div>';
        }
    }
}

if (empty($_GET['patient_id'])) {
    echo '<div class="alert alert-warning">No patient selected.</div>';
    exit();
}

$patient_id = (int) $_GET['patient_id'];

$sql = "
    SELECT
        a.id,
        a.problem_description,
        a.appointment_date,
        a.price,
        a.doctor_id,
        a.status,
        CONCAT(d.firstname, ' ', d.surname) AS doctor_name
    FROM appointments AS a
    LEFT JOIN doctors AS d ON a.doctor_id = d.id
    WHERE a.patient_id = {$patient_id}
    ORDER BY a.appointment_date DESC
";

$doctor_sql = "SELECT id, CONCAT(firstname, ' ', surname) AS doctor_name FROM doctors";
$doctor_res = mysqli_query($connect, $doctor_sql);

$res = mysqli_query($connect, $sql);
if (!$res) {
    echo '<div class="alert alert-danger">DB Error: ' . htmlspecialchars(mysqli_error($connect)) . '</div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Appointments for Patient #<?php echo $patient_id; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
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

    .layout-container {
      display: flex;
      min-height: 100vh;
    }
    .main-content {
      flex-grow: 1;
      padding: 20px;
    }

    .table thead th {
      background-color: #343a40;
      color: #fff;
      border-color: #454d55;
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
    .dashboard-card {
      color: white;
      padding: 20px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, filter 0.3s ease;
      margin-bottom: 20px;
    }
    .dashboard-card:hover {
      transform: scale(1.03);
      filter: brightness(1.1);
    }
    .dashboard-card i {
      font-size: 50px;
    }
  </style>
</head>
<body>

<div class="layout-container">
    <nav class="col-md-2 sidebar p-3">
      <?php include("sidenav.php"); ?>
    </nav>

    <div class="container py-4 main-content">
      <h3 class="mb-4">
        Appointments for Patient #<?php echo $patient_id; ?>
        <a href="view_patients.php" class="btn btn-secondary btn-sm float-end">&laquo; Back</a>
      </h3>

      <?php
      if (mysqli_num_rows($res) === 0) {
          echo '<div class="alert alert-info text-center">No appointments found.</div>';
      } else {
          echo '<div class="table-responsive">';
          echo '<table class="table table-bordered">';
          echo '<thead class="table-light"><tr>
                  <th>ID</th>
                  <th>Problem</th>
                  <th>Date</th>
                  <th>Doctor</th>
                  <th>Status</th>
                  <th>Set Price</th>
                  <th>Assign Doctor</th>
                  <th>Update Status</th>
                </tr></thead><tbody>';

          while ($row = mysqli_fetch_assoc($res)) {
              $status_class = match($row['status']) {
                  'Completed' => 'table-success',
                  'Ongoing' => 'table-info',
                  'Cancelled' => 'table-danger',
                  default => 'table-warning'
              };

              echo '<tr class="' . $status_class . '">
                      <td>' . (int)$row['id'] . '</td>
                      <td>' . htmlspecialchars($row['problem_description']) . '</td>
                      <td>' . htmlspecialchars($row['appointment_date']) . '</td>
                      <td>' . (!empty($row['doctor_name']) ? htmlspecialchars($row['doctor_name']) : '<em>Not Assigned</em>') . '</td>
                      <td>' . htmlspecialchars($row['status']) . '</td>';

              echo '<td>
                      <form method="POST" class="d-flex align-items-center">
                          <input type="hidden" name="appointment_id" value="' . (int)$row['id'] . '">
                          <input type="number" name="price" class="form-control form-control-sm me-2" placeholder="Enter price" required>
                          <button type="submit" name="set_price" class="btn btn-sm btn-primary">Set</button>
                      </form>
                    </td>';

              mysqli_data_seek($doctor_res, 0);
              echo '<td>
                      <form method="POST">
                          <input type="hidden" name="appointment_id" value="' . (int)$row['id'] . '">
                          <select name="doctor_id" class="form-control form-control-sm" required>
                              <option value="">Select Doctor</option>';
              while ($doctor_row = mysqli_fetch_assoc($doctor_res)) {
                  $selected = $row['doctor_id'] == $doctor_row['id'] ? 'selected' : '';
                  echo '<option value="' . $doctor_row['id'] . '" ' . $selected . '>' . htmlspecialchars($doctor_row['doctor_name']) . '</option>';
              }
              echo    '</select>
                          <button type="submit" name="assign_doctor" class="btn btn-sm btn-success mt-2">Assign</button>
                      </form>
                    </td>';

              echo '<td>
                      <form method="POST" class="d-flex align-items-center mb-2">
                          <input type="hidden" name="appointment_id" value="' . (int)$row['id'] . '">
                          <select name="new_status" class="form-select form-select-sm me-2" required>
                              <option value="Pending" ' . ($row['status'] === 'Pending' ? 'selected' : '') . '>Pending</option>
                              <option value="Ongoing" ' . ($row['status'] === 'Ongoing' ? 'selected' : '') . '>Ongoing</option>
                              <option value="Completed" ' . ($row['status'] === 'Completed' ? 'selected' : '') . '>Completed</option>
                              <option value="Cancelled" ' . ($row['status'] === 'Cancelled' ? 'selected' : '') . '>Cancelled</option>
                          </select>
                          <button type="submit" name="update_status" class="btn btn-sm btn-warning">Update</button>
                      </form>

                      <form method="POST" action="admin_cancel_appointment.php">
                          <input type="hidden" name="cancel_appointment_id" value="' . (int)$row['id'] . '">
                          <textarea name="cancellation_reason" class="form-control form-control-sm mb-1" rows="2" placeholder="Reason for cancellation..." required></textarea>
                          <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                      </form>
                    </td>
                    </tr>';
          }
          echo '</tbody></table></div>';
      }
      ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
