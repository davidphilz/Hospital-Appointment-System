<?php
session_start();
include("include/connection.php");

if(isset($_POST['login'])){
  $uname = $_POST['uname'];
  $pass = $_POST['pass'];

  if(empty($uname)){
    echo "<script>alert('Please enter your username.')</script>";
  } else if(empty($pass)){
    echo "<script>alert('Please enter your password.')</script>";
  } else {
    $query = "SELECT * FROM patient WHERE username='$uname' AND password='$pass'";
    $res = mysqli_query($connect, $query);

    if(mysqli_num_rows($res) == 1){
      $_SESSION['patient'] = $uname;
      header("Location: patient/index.php");
      exit();
    } else {
      echo "<script>alert('Invalid username or password.')</script>";
    }
  }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Login</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .login-card {
      background: rgba(255, 255, 255, 0.9);
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      padding: 2rem;
      max-width: 400px;
      width: 100%;
    }
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
  </style>
</head>
<body style="background-image: url(img/back.jpg); background-repeat: no-repeat; background-size: cover;">
  <?php include("include/header.php"); ?>

  <div class="login-container">
    <div class="login-card">
      <h5 class="text-center mb-4">Patient Login</h5>
      <form method="post">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="uname" class="form-control" autocomplete="off" placeholder="Enter Username" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="pass" class="form-control" autocomplete="off" placeholder="Enter Password" required>
        </div>
        <input type="submit" name="login" value="Login" class="btn btn-success w-100 mt-3">
        <p class="mt-2 text-center">Don't have an account? <a href="patientsignup.php">Create Now</a></p>
      </form>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
