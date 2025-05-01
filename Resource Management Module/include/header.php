<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['doctor'])) {
    unset($_SESSION['admin']);
    unset($_SESSION['patient']);
} elseif (isset($_SESSION['admin'])) {
    unset($_SESSION['doctor']);
    unset($_SESSION['patient']);
} //elseif (isset($_SESSION['patient'])) {
   // unset($_SESSION['admin']);
    //unset($_SESSION['doctor']);
//}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Allocation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .navbar-nav .nav-item {
            margin-left: 15px;
        }
        .navbar-nav .nav-link {
            font-size: 16px;
        }
        .navbar-custom {
            background: linear-gradient(45deg, #17a2b8, #117a8b) !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom p-3">
        <div class="container">
            <h5 class="text-white mb-0">Hospital Managment System</h5>
            <div class="ms-auto">
                <ul class="navbar-nav">
                    <?php
                        if (isset($_SESSION['admin'])) {
                            $user = $_SESSION['admin'];
                            echo '
                            <li class="nav-item">
                                <a href="profile.php" class="nav-link text-white"><i class="fas fa-user"></i> ' . $user . '</a>
                            </li>
                            <li class="nav-item">
                                <a href="logout.php" class="nav-link text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </li>';
                        } else if (isset($_SESSION['doctor'])) {
                            $user = $_SESSION['doctor'];
                            echo '
                            <li class="nav-item">
                                <a href="profile.php" class="nav-link text-white"><i class="fas fa-user-md"></i> ' . $user . '</a>
                            </li>
                            <li class="nav-item">
                                <a href="logout.php" class="nav-link text-white"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </li>';
                        } else {
                            echo '
                            <li class="nav-item"><a href="index.php" class="nav-link text-white"><i class="fas fa-home"></i> Home</a></li>
                            <li class="nav-item"><a href="adminlogin.php" class="nav-link text-white"><i class="fas fa-user-shield"></i> Admin</a></li>
                            <li class="nav-item"><a href="doctorlogin.php" class="nav-link text-white"><i class="fas fa-user-md"></i> Doctor</a></li>';
                        }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>