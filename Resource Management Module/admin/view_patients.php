<?php
session_start();
include("../include/header.php");
include("../include/connection.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>View Patients</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
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

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <nav class="col-md-2 sidebar p-3">
      <?php include("sidenav.php"); ?>
    </nav>

    <!-- Main Content -->
    <main class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Registered Patients</h3>
        <a href="view_appointments.php" class="btn btn-primary">
          <i class="fa fa-calendar-check"></i> View Appointments
        </a>
      </div>

      <?php
      $query = "SELECT * FROM patients ORDER BY id DESC";
      $res = mysqli_query($connect, $query);

      if (!$res) {
          echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
      } else {
          if (mysqli_num_rows($res) === 0) {
              echo '<div class="alert alert-info text-center">No patient data found.</div>';
          } else {
              echo '<div class="table-responsive">';
              echo '<table class="table table-bordered table-striped">';
              echo '<thead><tr>
                      <th>ID</th>
                      <th>Patient Name</th>
                      <th>Email</th>
                      <th>View Appointments</th>
                    </tr></thead><tbody>';

              while ($row = mysqli_fetch_assoc($res)) {
                  $id    = (int) $row['id'];
                  $name  = htmlspecialchars($row['name']);
                  $email = htmlspecialchars($row['email']);

                  echo "<tr>
                          <td>{$id}</td>
                          <td>{$name}</td>
                          <td>{$email}</td>
                          <td>
                            <a href=\"admin_view_appointments.php?patient_id={$id}\" class=\"btn btn-info btn-sm\">
                              <i class=\"fa fa-eye\"></i> View
                            </a>
                          </td>
                        </tr>";
              }

              echo '</tbody></table></div>';
          }
          mysqli_free_result($res);
      }
      ?>
    </main>

  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
