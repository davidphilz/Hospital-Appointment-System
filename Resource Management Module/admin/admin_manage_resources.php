<?php
session_start();
require_once("../include/config.php");
include("../include/header.php");

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $category = $_POST['category'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $quantity = intval($_POST['quantity']);
        $stmt = $pdo->prepare("INSERT INTO resources (category, name, description, quantity) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$category, $name, $description, $quantity])) {
            $message = "Resource added successfully.";
        } else {
            $message = "Failed to add resource.";
        }
    } elseif ($action == 'update') {
        $id = $_POST['id'];
        $category = $_POST['category'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $quantity = intval($_POST['quantity']);
        $stmt = $pdo->prepare("UPDATE resources SET category = ?, name = ?, description = ?, quantity = ? WHERE id = ?");
        if ($stmt->execute([$category, $name, $description, $quantity, $id])) {
            $message = "Resource updated successfully.";
        } else {
            $message = "Failed to update resource.";
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "Resource deleted successfully.";
        } else {
            $message = "Failed to delete resource.";
        }
    }
}

$stmt = $pdo->query("SELECT * FROM resources ORDER BY created_at DESC");
$resources = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Hospital Resources</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
     body {
       background-color: #f4f6f9;
       font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
       margin: 0;
       padding: 0;
     }
     /* Sidebar */
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
     /* Dashboard Cards */
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
     .dashboard-heading {
       color: #444;
       font-weight: 600;
       margin-bottom: 1rem;
     }
     .dashboard-subtext {
       color: #777;
       margin-bottom: 2rem;
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
<div class="container-fluid px-0">
    <div class="row gx-0 vh-100">
        <!-- Sidebar Column -->
        <div class="col-md-2 sidebar p-3">
            <?php include("sidenav.php"); ?>
        </div>
        <!-- Main Content Column -->
        <div class="col-md-10 main-content">
            <div class="container">
                <h2 class="mb-4">Resource Management</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header">Add New Resource</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="form-group mb-2">
                                <label for="category">Category</label>
                                <select name="category" id="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="bed">Bed Space</option>
                                    <option value="equipment">Equipment</option>
                                    <option value="drug">Drug</option>
                                    <option value="chemical">Chemical</option>
                                    <option value="doctor">Doctor</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label for="name">Resource Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="form-control"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label for="quantity">Quantity Available</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
                            </div>
                            <button type="submit" class="btn btn-success">Add Resource</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Current Resources</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Category</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resources as $res): ?>
                                <tr>
                                    <td><?= htmlspecialchars($res['id']) ?></td>
                                    <td><?= htmlspecialchars($res['category']) ?></td>
                                    <td><?= htmlspecialchars($res['name']) ?></td>
                                    <td><?= htmlspecialchars($res['description']) ?></td>
                                    <td><?= htmlspecialchars($res['quantity']) ?></td>
                                    <td>
                                        <!-- Use Bootstrap 5 modal toggles -->
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $res['id'] ?>">
                                          Edit
                                        </button>
                                        <!-- Delete Form -->
                                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this resource?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?= $res['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $res['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel<?= $res['id'] ?>">Edit Resource</h5>
                                                    <!-- Bootstrap 5 close button -->
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="id" value="<?= $res['id'] ?>">

                                                    <div class="form-group mb-2">
                                                        <label for="category<?= $res['id'] ?>">Category</label>
                                                        <select name="category" id="category<?= $res['id'] ?>" class="form-control" required>
                                                            <option value="bed" <?= $res['category'] == 'bed' ? 'selected' : '' ?>>Bed Space</option>
                                                            <option value="equipment" <?= $res['category'] == 'equipment' ? 'selected' : '' ?>>Equipment</option>
                                                            <option value="drug" <?= $res['category'] == 'drug' ? 'selected' : '' ?>>Drug</option>
                                                            <option value="chemical" <?= $res['category'] == 'chemical' ? 'selected' : '' ?>>Chemical</option>
                                                            <option value="doctor" <?= $res['category'] == 'doctor' ? 'selected' : '' ?>>Doctor</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label for="name<?= $res['id'] ?>">Resource Name</label>
                                                        <input type="text" name="name" id="name<?= $res['id'] ?>" class="form-control" value="<?= htmlspecialchars($res['name']) ?>" required>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label for="description<?= $res['id'] ?>">Description</label>
                                                        <textarea name="description" id="description<?= $res['id'] ?>" class="form-control"><?= htmlspecialchars($res['description']) ?></textarea>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label for="quantity<?= $res['id'] ?>">Quantity Available</label>
                                                        <input type="number" name="quantity" id="quantity<?= $res['id'] ?>" class="form-control" min="0" value="<?= htmlspecialchars($res['quantity']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <!-- Use data-bs-dismiss for closing the modal -->
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Edit Modal -->

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- End Container -->
        </div><!-- End Main Content Column -->
    </div><!-- End Row -->
</div><!-- End Container Fluid -->

<!-- Bootstrap 5 Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
