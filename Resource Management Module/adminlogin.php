<?php
session_start();
include("include/connection.php");

$show = "";
$error = ""; 

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)) {
        $error = "Enter Username";
    } elseif (empty($password)) {
        $error = "Enter Password";
    } else {
        $query = "SELECT * FROM admin WHERE username = ?";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password'])) {
                unset($_SESSION['doctor']); 
                $_SESSION['admin'] = $username; 
                header("Location: admin/index.php");
                exit();
            } else {
                $error = "Invalid Username or Password";
            }
        } else {
            $error = "Invalid Username or Password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh; 
      background: url('img/hospital.jpg') no-repeat center center fixed; 
      background-size: cover;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      max-width: 400px;
      width: 100%;
      padding: 2rem;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.9);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .btn-custom {
      width: 100%;
      padding: 10px;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <?php include("include/header.php"); ?>

  <div class="container login-container">
    <div class="login-box">
      <h4 class="text-center mb-3">Admin Login</h4>
      <form method="post" action="">
        <?php 
          if (!empty($error)) {
              echo "<div class='alert alert-danger'>$error</div>";
          }
        ?>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" placeholder="Enter Username" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
        </div>
        <button type="submit" name="login" class="btn btn-success btn-custom">Login</button>
        <p class="mt-3 text-center">Don't have an account? <a href="adminsignup.php">Create Now</a></p>
      </form>
    </div>
  </div>
</body>
</html>
