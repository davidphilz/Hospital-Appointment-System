<?php
include("include/connection.php");

if (isset($_POST['register'])) {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    $email     = trim($_POST['email']);
    $gender    = trim($_POST['gender']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];

    $error = [];

    if (empty($full_name)) {
        $error['register'] = "Enter your full name";
    } elseif (empty($phone)) {
        $error['register'] = "Enter your phone number";
    } elseif (empty($email)) {
        $error['register'] = "Enter your email";
    } elseif (empty($gender)) {
        $error['register'] = "Select your gender";
    } elseif (empty($username)) {
        $error['register'] = "Enter your username";
    } elseif (empty($password)) {
        $error['register'] = "Enter your password";
    }

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] == 0) {
        $allowed   = array("jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png");
        $fileName  = $_FILES['profile']['name'];
        $fileType  = $_FILES['profile']['type'];
        $fileSize  = $_FILES['profile']['size'];
        $ext       = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!array_key_exists($ext, $allowed)) {
            $error['register'] = "Invalid file format. Allowed: JPG, JPEG, PNG.";
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $error['register'] = "File size must be less than 2MB.";
        } elseif (!in_array($fileType, $allowed)) {
            $error['register'] = "Invalid file type.";
        } else {
            $newFileName = uniqid() . "." . $ext;
            $uploadPath  = "upload" . $newFileName;
            if (!move_uploaded_file($_FILES['profile']['tmp_name'], $uploadPath)) {
                $error['register'] = "Failed to upload profile picture.";
            }
        }
    } else {
        $newFileName = "";
    }

    if (empty($error)) {

        $query = "SELECT * FROM admin WHERE username = ?";
        $stmt  = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error['register'] = "Username already exists.";
        } else {

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO admin (full_name, username, phone, email, gender, password, status, profile) 
                      VALUES (?, ?, ?, ?, ?, ?, 'active', ?)";
            $stmt  = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, 'sssssss', $full_name, $username, $phone, $email, $gender, $hashed_password, $newFileName);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                echo "<script>alert('Admin registered successfully'); window.location='adminlogin.php';</script>";
                exit();
            } else {
                $error['register'] = "Failed to register admin.";
            }
        }
    }
}

$show_error = isset($error['register']) ? "<div class='alert alert-danger text-center'>{$error['register']}</div>" : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Registration</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/hospital.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar-custom { background-color: #17a2b8; }
    .navbar-brand { color: #fff; font-weight: 700; }
    .card {
      border: none;
      border-radius: 15px;
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }
    .card-header {
      background: transparent;
      color: #333;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      border-bottom: none;
      margin-bottom: 10px;
    }
    .form-label { font-weight: 600; }
    .btn-success {
      background-color: #28a745;
      border: none;
      font-size: 18px;
      padding: 12px;
      border-radius: 50px;
    }
    .btn-success:hover {
      background-color: #218838;
    }
    p {
      text-align: center;
      margin-top: 15px;
    }
  </style>
</head>
<body>
<?php include("include/header.php"); ?>
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card shadow">
          <div class="card-header">Admin Registration</div>
          <div class="card-body">
            <?php echo $show_error; ?>
            <form action="" method="post" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Enter Full Name" required>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="phone" class="form-label">Phone Number</label>
                  <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter Phone Number" required>
                </div>
                <div class="col">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" required>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="gender" class="form-label">Gender</label>
                  <select name="gender" id="gender" class="form-select" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                  </select>
                </div>
                <div class="col">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" name="username" id="username" class="form-control" placeholder="Enter Username" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" required>
              </div>
              <div class="mb-3">
                <label for="profile" class="form-label">Profile Picture (Optional)</label>
                <input type="file" name="profile" id="profile" class="form-control">
              </div>
              <div class="d-grid">
                <button type="submit" name="register" class="btn btn-success btn-lg">Register</button>
              </div>
              <p>Already have an account? <a href="adminlogin.php">Start Now</a></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
