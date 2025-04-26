<?php
session_start();
include("../include/header.php");
include("../include/connection.php");

$ad = $_SESSION['admin'];


$query = "SELECT * FROM admin WHERE username = '$ad'";
$res = mysqli_query($connect, $query);

if($row = mysqli_fetch_assoc($res)) {
    $username = $row['username'];
    $profile = $row['profile'];  
}

if(isset($_POST['update'])) {
    $profile_file = $_FILES['profile'];
    if($profile_file['error'] !== UPLOAD_ERR_NO_FILE) {
        $filename = $profile_file['name'];
        $query = "UPDATE admin SET profile = '$filename' WHERE username = '$ad'";
        $result = mysqli_query($connect, $query);
        if($result) {
            move_uploaded_file($profile_file['tmp_name'], "img/" . $filename);
            $profile = $filename;
        }
    }
}


if(isset($_POST['change'])) {
    $uname = trim($_POST['uname']); 
    if(!empty($uname)) {
        $query = "UPDATE admin SET username = '$uname' WHERE username = '$ad'";
        $res = mysqli_query($connect, $query);
        if($res) {
            $_SESSION['admin'] = $uname;
            $username = $uname;
        }
    }
}

$show = "";
if(isset($_POST['changepass'])) {
    $cpass  = $_POST['cpass'];    
    $npass  = $_POST['npass'];    
    $cnpass = $_POST['cnpass'];   

    $error = array();
    $success = "";

    if(empty($cpass) || empty($npass) || empty($cnpass)) {
        $error['empty'] = "All fields are required";
    }
    if(strlen($npass) < 8) {
        $error['length'] = "Password should be at least 8 characters long";
    }
    if($npass !== $cnpass) {
        $error['match'] = "New passwords do not match";
    }


    $query = "SELECT password FROM admin WHERE username = '$ad'";
    $result = mysqli_query($connect, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stored_hash = $row['password'];

        if(!password_verify($cpass, $stored_hash)) {
            $error['current'] = "Current password is incorrect";
        }
    } else {
        $error['db'] = "Error retrieving current password";
    }


    if(count($error) == 0) {
        $new_hashed = password_hash($npass, PASSWORD_BCRYPT);
        $update_query = "UPDATE admin SET password = '$new_hashed' WHERE username = '$ad'";
        $update_result = mysqli_query($connect, $update_query);
        if($update_result) {
            $success = "Password updated successfully";
        } else {
            $error['update'] = "Failed to update password";
        }
    }

    if(!empty($error)) {
        $show = "<h5 class='text-center alert alert-danger'>" . implode("<br>", $error) . "</h5>";
    } else if(!empty($success)) {
        $show = "<h5 class='text-center alert alert-success'>$success</h5>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin Profile</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0; 
      padding: 0;
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
    .profile-img {
      width: 200px;
      height: 200px;
      object-fit: cover;
      border-radius: 50%;
    }
    .section-heading {
      font-weight: 600;
      margin-bottom: 1rem;
      color: #444;
    }
  </style>
</head>
<body>

  <div class="layout-container">
    <nav class="col-md-2 sidebar p-3">
      <?php include("sidenav.php"); ?>
    </nav>


    <div class="main-content">
      <h3 class="section-heading text-center"><?php echo htmlspecialchars($username); ?>'s Profile</h3>

      <div class="row g-4">
        <div class="col-md-6">
          <div class="card p-3 shadow-sm">
            <h5 class="text-center mb-3">Profile Picture</h5>
            <div class="text-center">
              <img src="img/<?php echo htmlspecialchars($profile); ?>" class="profile-img mb-3" alt="Profile Image">
            </div>
            <form method="post" enctype="multipart/form-data">
              <div class="form-group mb-2">
                <label>Update Profile</label>
                <input type="file" name="profile" class="form-control">
              </div>
              <button type="submit" name="update" class="btn btn-success w-100 mt-3">Update</button>
            </form>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card p-3 mb-4 shadow-sm">
            <h5 class="text-center mb-3">Change Username</h5>
            <form method="post">
              <div class="form-group mb-2">
                <label>New Username</label>
                <input type="text" name="uname" class="form-control" placeholder="Enter New Username">
              </div>
              <button type="submit" name="change" class="btn btn-primary w-100 mt-2">Change Username</button>
            </form>
          </div>

          <div class="card p-3 shadow-sm">
            <h5 class="text-center mb-3">Change Password</h5>
            <?php if(!empty($show)) echo $show; ?>
            <form method="post">
              <div class="form-group mb-2">
                <label>Current Password</label>
                <input type="password" name="cpass" class="form-control" placeholder="Enter Current Password" required>
              </div>
              <div class="form-group mb-2">
                <label>New Password</label>
                <input type="password" name="npass" class="form-control" placeholder="Enter New Password" required>
              </div>
              <div class="form-group mb-2">
                <label>Confirm New Password</label>
                <input type="password" name="cnpass" class="form-control" placeholder="Confirm New Password" required>
              </div>
              <button type="submit" name="changepass" class="btn btn-warning w-100 mt-2">Change Password</button>
            </form>
          </div>
        </div>
      </div> 
    </div>
  </div>


  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
