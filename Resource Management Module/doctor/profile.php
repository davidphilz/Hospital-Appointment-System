<?php
session_start();
include("../include/header.php");
include("../include/connection.php");

$doc = $_SESSION['doctor'];

if(isset($_POST['upload'])) {
    $img_name = $_FILES['img']['name'];
    $img_tmp  = $_FILES['img']['tmp_name'];
    
    $ext = pathinfo($img_name, PATHINFO_EXTENSION);
    $new_name = uniqid("profile_", true) . "." . $ext;
    
    if(move_uploaded_file($img_tmp, "img/" . $new_name)) {
        $update_query = "UPDATE doctors SET profile='$new_name' WHERE username='$doc'";
        if(mysqli_query($connect, $update_query)) {
            echo "<script>alert('Profile Picture Updated');</script>";
            echo "<script>window.location.href = window.location.href;</script>";
        } else {
            echo "<script>alert('Error updating profile in database');</script>";
        }
    } else {
        echo "<script>alert('Error uploading file');</script>";
    }
}

if(isset($_POST['change'])) {
    $new_username = mysqli_real_escape_string($connect, $_POST['change_name']);
    if(empty($new_username)) {
        echo "<script>alert('Please enter a new username');</script>";
    } else {
        $update_query = "UPDATE doctors SET username='$new_username' WHERE username='$doc'";
        if(mysqli_query($connect, $update_query)) {
            $_SESSION['doctor'] = $new_username;
            echo "<script>alert('Username updated successfully');</script>";
            echo "<script>window.location.href = window.location.href;</script>";
        } else {
            echo "<script>alert('Error updating username in database');</script>";
        }
    }
}

if(isset($_POST['change_pass'])) {
    $old_pass = mysqli_real_escape_string($connect, $_POST['old_pass']);
    $new_pass = mysqli_real_escape_string($connect, $_POST['new_pass']);
    $con_pass = mysqli_real_escape_string($connect, $_POST['con_pass']);
    
    if(empty($old_pass) || empty($new_pass) || empty($con_pass)) {
        echo "<script>alert('Please fill in all password fields');</script>";
    } else {
        $query = "SELECT password FROM doctors WHERE username='$doc'";
        $result = mysqli_query($connect, $query);
        $row = mysqli_fetch_assoc($result);
        $current_password = $row['password'];

        if($old_pass !== $current_password) {
            echo "<script>alert('Old password is incorrect');</script>";
        } elseif($new_pass !== $con_pass) {
            echo "<script>alert('New password and confirm password do not match');</script>";
        } else {
            $update_query = "UPDATE doctors SET password='$new_pass' WHERE username='$doc'";
            if(mysqli_query($connect, $update_query)) {
                echo "<script>alert('Password updated successfully');</script>";
                echo "<script>window.location.href = window.location.href;</script>";
            } else {
                echo "<script>alert('Error updating password in database');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Doctors Profile</title>
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
    .profile-img {
      width: 200px;
      height: 200px;
      object-fit: cover;
      border-radius: 50%;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 sidebar p-3">
        <?php include("sidenav.php"); ?>
      </div>

      <div class="col-md-10 p-4">
        <div class="container-fluid">
          <?php
          $query = "SELECT * FROM doctors WHERE username='" . $_SESSION['doctor'] . "'";
          $res = mysqli_query($connect, $query);
          $row = mysqli_fetch_array($res);
          ?>
          
          <h3 class="text-center mb-4"><?php echo htmlspecialchars($row['username']); ?>'s Profile</h3>
          
          <div class="row">
            <div class="col-md-6">
              <div class="card p-3 shadow-sm">
                <h5 class="text-center mb-3">Profile Picture</h5>
                <div class="text-center">
                  <img src="img/<?php echo htmlspecialchars($row['profile']); ?>" class="profile-img mb-3" alt="Doctor Profile">
                </div>
                <form method="post" enctype="multipart/form-data" class="mt-3">
                  <input type="file" name="img" class="form-control">
                  <input type="submit" name="upload" class="btn btn-success w-100 mt-2" value="Upload Profile Picture">
                </form>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card p-3 shadow-sm mb-4">
                <h5 class="text-center my-2">Change Username</h5>
                <form method="post">
                  <label>New Username</label>
                  <input type="text" name="change_name" class="form-control" autocomplete="off" placeholder="Enter New Username">
                  <input type="submit" name="change" class="btn btn-primary w-100 mt-3" value="Change Username">
                </form>
              </div>
  
              <div class="card p-3 shadow-sm">
                <h5 class="text-center my-2">Change Password</h5>
                <form method="post">
                  <div class="form-group mb-2">
                    <label>Old Password</label>
                    <input type="password" name="old_pass" class="form-control" autocomplete="off" placeholder="Enter Old Password">
                  </div>
                  <div class="form-group mb-2">
                    <label>New Password</label>
                    <input type="password" name="new_pass" class="form-control" autocomplete="off" placeholder="Enter New Password">
                  </div>
                  <div class="form-group mb-2">
                    <label>Confirm Password</label>
                    <input type="password" name="con_pass" class="form-control" autocomplete="off" placeholder="Enter Confirm Password">
                  </div>
                  <input type="submit" name="change_pass" class="btn btn-warning w-100 mt-2" value="Change Password">
                </form>
              </div>
            </div>
  
          </div> 
          
          <div class="row mt-4">
    <div class="col-md-12">
        <div class="card p-3 shadow-sm">
            <h5 class="text-center mb-3">My Details</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>ID:</strong> <?php echo htmlspecialchars($row['id']); ?></li>
                <li class="list-group-item"><strong>First Name:</strong> <?php echo htmlspecialchars($row['firstname']); ?></li>
                <li class="list-group-item"><strong>Surname:</strong> <?php echo htmlspecialchars($row['surname']); ?></li>
                <li class="list-group-item"><strong>Username:</strong> <?php echo htmlspecialchars($row['username']); ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></li>
                <li class="list-group-item"><strong>Phone Number:</strong> <?php echo htmlspecialchars($row['phone']); ?></li>
                <li class="list-group-item"><strong>Gender:</strong> <?php echo htmlspecialchars($row['gender']); ?></li>
                <li class="list-group-item"><strong>State:</strong> <?php echo htmlspecialchars($row['state']); ?></li>
            </ul>
        </div>
    </div>
</div>

          
        </div>
      </div>
    </div> 
  </div>
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
