<?php
session_start();
require_once("../include/configure.php");

$stmt = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC");
$notices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notices</title>
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
    <?php include("../include/header.php"); ?>

    <div class="container-fluid">
        <div class="row">

            <div class="col-md-2 sidebar p-3">
                <?php include("sidenav.php"); ?>
            </div>

            <div class="col-md-9 offset-md-1">
                <h2 class="mb-4">Notices</h2>
                
                <div class="card shadow-sm">
                    <div class="card-header">
                        Current Notices
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped mt-3">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Doctor Name</th>
                                    <th>Notice Type</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($notices) > 0): ?>
                                    <?php foreach ($notices as $notice): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($notice['id']); ?></td>
                                            <td><?php echo htmlspecialchars($notice['doctor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($notice['notice_type']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($notice['description'])); ?></td>
                                            <td><?php echo htmlspecialchars($notice['status']); ?></td>
                                            <td><?php echo htmlspecialchars($notice['created_at']); ?></td>
                                            <td>
                                                <?php if ($notice['status'] == 'pending'): ?>
                                                    <form action="mark_resolved.php" method="post" style="display:inline;">
                                                        <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">Mark as Resolved</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Resolved</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No notices found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> 
        </div> 
    </div> 

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
