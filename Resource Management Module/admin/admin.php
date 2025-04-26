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
  <title>Manage Admins</title>
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
    .dashboard-heading {
      color: #444;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .dashboard-subtext {
      color: #777;
      margin-bottom: 2rem;
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
      <h3 class="dashboard-heading text-center">Manage Admins</h3>
      <div class="row">
        <div class="col-md-6">
          <h5 class="text-center mb-3">All Admin</h5>
          <?php
            $ad = $_SESSION['admin'];
            $query = "SELECT * FROM admin WHERE username != '$ad'";
            $res = mysqli_query($connect, $query);

            $output = "
              <table class='table table-bordered'>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th style='width: 10%;'>Action</th>
                  </tr>
                </thead>
                <tbody>
            ";

            if (mysqli_num_rows($res) < 1) {
              $output .= "
                <tr>
                  <td colspan='3' class='text-center'>No New Admin</td>
                </tr>
              ";
            } else {
              while($row = mysqli_fetch_assoc($res)) {
                $id = $row['id'];
                $username = $row['username'];
                $output .= "
                  <tr>
                    <td>$id</td>
                    <td>$username</td>
                    <td>
                      <a href='admin.php?id=$id'><button id='$id' class='btn btn-danger btn-sm'>Remove</button></a>
                    </td>
                  </tr>
                ";
              }
            }

            $output .= "
                </tbody>
              </table>
            ";

            echo $output;

            if (isset($_GET['id'])) {
              $id = $_GET['id'];
              $query = "DELETE FROM admin WHERE id=$id";
              mysqli_query($connect, $query);
            }
          ?>
        </div>
        <!-- Add Admin Form -->
        <div class="col-md-6">
          <?php
            $show = "";
            $error = array();

            if(isset($_POST['add'])) {
              $uname = $_POST['uname'];
              $pass  = $_POST['pass'];
              $img   = $_FILES['img']['name'];

              if(empty($uname)){
                $error['u'] = "Enter Username";
              } else if(empty($pass)){
                $error['u'] = "Enter Password";
              } else if(empty($img)){
                $error['u'] = "Select Image";
              }

              if(count($error) == 0){
                $check_query = "SELECT * FROM admin WHERE username='$uname' LIMIT 1";
                $check_res = mysqli_query($connect, $check_query);
                if(mysqli_num_rows($check_res) > 0){
                  $error['u'] = "Username already taken.";
                } else {
                  $q = "INSERT INTO admin(username, password, profile) VALUES('$uname', '$pass', '$img')";
                  $result = mysqli_query($connect, $q);

                  if($result){
                    move_uploaded_file($_FILES['img']['tmp_name'], "img/$img");
                  }
                }
              }
            }

            if(isset($error['u'])) {
              $er = $error['u'];
              $show = "<h5 class='text-center alert alert-danger'>$er</h5>";
            }
          ?>
          <h5 class="text-center mb-3">Add Admin</h5>
          <?php echo $show; ?>
          <form method="post" enctype="multipart/form-data">
            <div class="form-group mb-2">
              <label for="username">Username</label>
              <input type="text" name="uname" class="form-control" autocomplete="off">
            </div>
            <div class="form-group mb-2">
              <label>Password</label>
              <input type="password" name="pass" class="form-control">
            </div>
            <div class="form-group mb-2">
              <label>Profile Image</label>
              <input type="file" name="img" class="form-control">
            </div><br>
            <input type="submit" name="add" value="Add Admin" class="btn btn-success w-100">
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
