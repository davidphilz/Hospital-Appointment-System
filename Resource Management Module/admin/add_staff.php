<?php
session_start();
require_once("../include/config.php");

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $role       = trim($_POST['role'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if ($name && $email && $role) {
        $stmt = $pdo->prepare("INSERT INTO staff (name, email, phone, role, department, password, status) VALUES (?, ?, ?, ?, ?, ?, 'Inactive')");
        if ($stmt->execute([$name, $email, $phone, $role, $department, $password])) {
            $message = 'Staff member added successfully.';
        } else {
            $message = 'Failed to add staff member.';
        }
    } else {
        $message = 'Please fill in all required fields (Name, Email, Role).';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin - Add Staff</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        /* Removed display:flex from body so header is at top */
        body {
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
        .layout-container {
            display: flex;
            min-height: 100vh; /* Fill the entire viewport height */
        }
        .sidenav {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            min-height: 100vh;
            flex-shrink: 0;
        }
        .sidenav a {
            color: #ddd;
        }
        .sidenav a:hover {
            color: #fff;
            text-decoration: none;
        }
        .sidenav .nav-link.active {
            background-color: #495057;
            color: #fff;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>
<body>
    <?php include("../include/header.php"); ?>

    <div class="layout-container">
        <nav class="sidenav d-flex flex-column p-3">
            <a href="#" class="d-flex align-items-center mb-3 text-white text-decoration-none">
                <span class="fs-4">Hospital Admin</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="index.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="add_staff.php" class="nav-link text-white">
                        Add Staff
                    </a>
                </li>
                <li>
                    <a href="view_staff.php" class="nav-link text-white">
                        View Staff
                    </a>
                </li>
            </ul>
            <hr>
        </nav>

        <div class="main-content">
            <div class="container">
                <h1 class="mb-4">Add Staff Member</h1>
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <form method="POST" action="add_staff.php">
                    <div class="form-group">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter email address" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter phone number">
                    </div>
                    <div class="form-group">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="">Select role</option>
                            <option value="Nurse">Nurse</option>
                            <option value="Technician">Technician</option>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Pharmacist">Pharmacist</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" class="form-control" name="department" id="department" placeholder="Enter department">
                    </div>
                    <div class="form-group">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Staff</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
