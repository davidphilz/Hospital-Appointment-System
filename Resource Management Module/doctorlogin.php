<?php
session_start();
include("include/connection.php");

$show = "";

if(isset($_POST['login'])){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $error = array();

    if(empty($username)){
        $error['login'] = "Enter Username";
    } elseif(empty($password)){
        $error['login'] = "Enter Password";
    }

    if(count($error) == 0){
        $query = "SELECT * FROM doctors WHERE username='$username' AND password='$password'";
        $result = mysqli_query($connect, $query);

        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);

            if($row['status'] == "pending"){
                $error['login'] = "Please wait for the admin to confirm approval";
            } elseif($row['status'] == "Rejected"){
                $error['login'] = "Please Try again Later";
            } else {
                echo "<script>alert('Login Successful');</script>";
                $_SESSION['doctor'] = $username;
                header("Location: doctor/index.php");
                exit();
            }
        } else {
            $error['login'] = "Invalid Username or Password";
        }
    }

    if(isset($error['login'])){
        $l = $error['login'];
        $show = "<h5 class='text-center alert alert-danger'>$l</h5>";
    } else {
        $show = "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Login Page</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh; 
      background: url('img/background.jpg') no-repeat center center fixed; 
      background-size: cover;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-card {
      max-width: 400px;
      width: 100%;
      padding: 2rem;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>
<body>
    <?php include("include/header.php"); ?>

    <div class="container login-container">
      <div class="login-card">
        <h5 class="text-center mb-3">Doctor Login</h5>
        
        <?php echo $show; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" autocomplete="off" placeholder="Enter Username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" autocomplete="off" placeholder="Enter Password" required>
          </div>
          <input type="submit" name="login" value="Login" class="btn btn-success w-100 mt-3">
          <p class="mt-3 text-center">Don't have an account? <a href="doctorsignup.php">Create Now</a></p>
        </form>
      </div>
    </div>
</body>
</html>
