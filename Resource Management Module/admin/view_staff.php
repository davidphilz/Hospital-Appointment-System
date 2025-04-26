<?php
session_start();
require_once("../include/config.php");

$filterRole = $_GET['role'] ?? '';
$filterDepartment = $_GET['department'] ?? '';

$query = "SELECT * FROM staff WHERE 1=1";
$params = [];

if (!empty($filterRole)) {
    $query .= " AND role = ?";
    $params[] = $filterRole;
}
if (!empty($filterDepartment)) {
    $query .= " AND department = ?";
    $params[] = $filterDepartment;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$staffList = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>View Staff</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
        .sidenav {
            width: 250px;
            background-color: #343a40;
            color: white;
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
        }
        .layout-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }
        .thead-dark th {
            background-color: #343a40;
            color: #fff;
            border-color: #454d55;
        }
    </style>
</head>
<body>

    <?php include("../include/header.php"); ?>

    <div class="layout-container">
        <nav class="sidenav p-3">
            <a href="#" class="d-flex align-items-center mb-3 text-white text-decoration-none">
                <span class="fs-4">Hospital Admin</span>
            </a>
            <hr>
            <ul class="nav flex-column">
                <li><a href="index.php" class="nav-link text-white">Dashboard</a></li>
                <li><a href="add_staff.php" class="nav-link text-white">Add Staff</a></li>
                <li><a href="view_staff.php" class="nav-link active">View Staff</a></li>
            </ul>
        </nav>

        <div class="main-content">
            <h1 class="mb-4">View Staff</h1>

            <form method="GET" action="view_staff.php" class="mb-3">
                <div class="form-row">
                    <div class="col">
                        <input type="text" name="role" class="form-control" placeholder="Filter by Role"
                               value="<?= htmlspecialchars($filterRole) ?>">
                    </div>
                    <div class="col">
                        <input type="text" name="department" class="form-control" placeholder="Filter by Department"
                               value="<?= htmlspecialchars($filterDepartment) ?>">
                    </div>
                    <div class="col">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staffList as $staff): ?>
                        <tr>
                            <td><?= htmlspecialchars($staff['name']) ?></td>
                            <td><?= htmlspecialchars($staff['email']) ?></td>
                            <td><?= htmlspecialchars($staff['phone']) ?></td>
                            <td><?= htmlspecialchars($staff['role']) ?></td>
                            <td><?= htmlspecialchars($staff['department']) ?></td>
                            <td>
                                <span class="badge <?= $staff['status'] === 'Active' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= htmlspecialchars($staff['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="update_status.php?id=<?= $staff['id'] ?>&status=Active"
                                   class="btn btn-sm btn-success update-status"
                                   data-id="<?= $staff['id'] ?>" data-status="Active">Set Active</a>
                                <a href="update_status.php?id=<?= $staff['id'] ?>&status=Inactive"
                                   class="btn btn-sm btn-warning update-status"
                                   data-id="<?= $staff['id'] ?>" data-status="Inactive">Set Inactive</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>


            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <script>
                $(document).ready(function () {
                    $(".update-status").click(function (e) {
                        e.preventDefault(); 
                        var staff_id = $(this).data("id");
                        var status = $(this).data("status");

                        $.post("update_status.php", { staff_id: staff_id, status: status }, function (response) {
                            var data = JSON.parse(response);
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.message);
                            }
                        });
                    });
                });
            </script>
        </div>
    </div>
</body>
</html>
