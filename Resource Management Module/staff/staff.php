<?php 
require_once("../include/config.php");
include("header.php");

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}

$staff_id = $_SESSION['staff_id'];
$pictureMessage = '';
$passwordMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_tmp  = $_FILES['profile_picture']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_extensions)) {
            $pictureMessage = "Invalid file type. Allowed types: jpg, jpeg, png.";
        } elseif ($file_size > 2000000) {
            $pictureMessage = "File too large. Maximum file size is 2MB.";
        } else {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            // Generate a unique file name
            $new_filename = time() . "_" . basename($file_name);
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $stmt = $pdo->prepare("UPDATE staff SET profile_picture = ? WHERE id = ?");
                if ($stmt->execute([$new_filename, $staff_id])) {
                    $pictureMessage = "Profile picture updated successfully.";
                } else {
                    $pictureMessage = "Database error: Could not update profile picture.";
                }
            } else {
                $pictureMessage = "Error uploading file.";
            }
        }
    } else {
        $pictureMessage = "Please select a valid file.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $passwordMessage = "New passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM staff WHERE id = ?");
        $stmt->execute([$staff_id]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff && password_verify($current_password, $staff['password'])) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE staff SET password = ? WHERE id = ?");
            if ($stmt->execute([$new_password_hash, $staff_id])) {
                $passwordMessage = "Password updated successfully.";
            } else {
                $passwordMessage = "Error updating password.";
            }
        } else {
            $passwordMessage = "Current password is incorrect.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->execute([$staff_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 2px solid #dee2e6;
        }
        .profile-details h2 {
            margin: 0;
        }
        body {
      font-family: 'Poppins', sans-serif;
      background-color: #eef2f7;
      margin: 0;
      padding: 0;
    }
    #sidebar {
      height: 100vh;
      background: linear-gradient(180deg, #2c3e50, #34495e);
      color: #fff;
      padding-top: 20px;
    }
    #sidebar ul {
      list-style: none;
      padding: 0;
    }
    #sidebar ul li {
      padding: 10px;
      text-align: center;
    }
    #sidebar ul li a {
      color: #ddd;
      text-decoration: none;
      display: block;
      transition: background 0.3s, color 0.3s;
    }
    #sidebar ul li a:hover {
      background-color: #495057;
      color: #fff;
      text-decoration: none;
    }
    .navbar {
      background-color: #2c3e50;
    }
    .navbar-brand, .navbar-nav .nav-link {
      color: #fff !important;
    }
    .content {
      padding: 20px;
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
    .bg-primary {
      background: linear-gradient(45deg, #007bff, #0069d9);
    }
    .dashboard-card h5 {
      margin-bottom: 8px;
      font-size: 1.1rem;
      font-weight: 600;
    }
    .big-number {
      font-size: 1.7rem;
      font-weight: bold;
      margin-bottom: 6px;
    }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
      <div class="col-md-2 p-0">
        <?php include("sidenav.php"); ?>
      </div>
<div class="container profile-container">
    <div class="profile-header">
        <img src="<?php echo !empty($staff['profile_picture']) ? 'uploads/' . htmlspecialchars($staff['profile_picture']) : 'default.png'; ?>" alt="Profile Picture">
        <div class="profile-details">
            <h2><?php echo htmlspecialchars($staff['name']); ?></h2>
            <p>Email: <?php echo htmlspecialchars($staff['email']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($staff['phone']); ?></p>
            <p>Role: <?php echo htmlspecialchars($staff['role']); ?></p>
            <p>Department: <?php echo htmlspecialchars($staff['department']); ?></p>
        </div>
    </div>
    <?php if ($pictureMessage): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($pictureMessage); ?></div>
    <?php endif; ?>
    <form method="post" action="staff.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="profile_picture">Update Profile Picture</label>
            <input type="file" name="profile_picture" id="profile_picture" class="form-control-file" required>
        </div>
        <button type="submit" name="update_picture" class="btn btn-primary">Update Picture</button>
    </form>
    
    <hr>
    <?php if ($passwordMessage): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($passwordMessage); ?></div>
    <?php endif; ?>
    <form method="post" action="staff.php">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>