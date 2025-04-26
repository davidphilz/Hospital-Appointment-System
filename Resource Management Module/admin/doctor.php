<?php
session_start();
include("../include/header.php");
include("../include/connection.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Total Doctors</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
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


    <div class="main-content">
      <h3 class="mb-4 text-center">Total Doctors</h3>
      <?php
        $query = "SELECT * FROM doctors WHERE status = 'Approved' ORDER BY data_reg ASC";
        $res = mysqli_query($connect, $query);

        $output = "
        <table class='table table-bordered'>
          <thead>
            <tr>
              <th>ID</th>
              <th>Firstname</th>
              <th>Surname</th>
              <th>Email</th>
              <th>Gender</th>
              <th>Phone</th>
              <th>State</th>
              <th>Salary</th>
              <th>Date Registered</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
        ";

        if (mysqli_num_rows($res) < 1) {
            $output .= "
              <tr>
                <td colspan='10' class='text-center'>No Approved Doctors</td>
              </tr>
            ";
        } else {
            while ($row = mysqli_fetch_assoc($res)) {
                $output .= "
                  <tr>
                    <td>".$row['id']."</td>
                    <td>".$row['firstname']."</td>
                    <td>".$row['surname']."</td>
                    <td>".$row['email']."</td>
                    <td>".$row['gender']."</td>
                    <td>".$row['phone']."</td>
                    <td>".$row['state']."</td>
                    <td>".$row['salary']."</td>
                    <td>".$row['data_reg']."</td>
                    <td>
                      <a href='edit.php?id=".$row['id']."'>
                        <button class='btn btn-info btn-sm' style='color: black;'>Edit</button>
                      </a>
                    </td>
                  </tr>
                ";
            }
        }

        $output .= "</tbody></table>";
        echo $output;
      ?>
    </div>
  </div>


  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
