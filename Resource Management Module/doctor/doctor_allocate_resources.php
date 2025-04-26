<?php
session_start();
include("../include/config.php");
include("../include/header.php");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_id'])) {
    $resource_id = $_POST['resource_id'];
    $allocated_quantity = intval($_POST['allocated_quantity']);
    $remarks = $_POST['remarks'] ?? '';

    $stmt = $pdo->prepare("SELECT quantity FROM resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $resource = $stmt->fetch();

    if ($resource && $resource['quantity'] >= $allocated_quantity) {
        $new_quantity = $resource['quantity'] - $allocated_quantity;
        $updateStmt = $pdo->prepare("UPDATE resources SET quantity = ? WHERE id = ?");
        $updateStmt->execute([$new_quantity, $resource_id]);

        $insertStmt = $pdo->prepare("INSERT INTO allocations (resource_id, allocated_quantity, remarks) VALUES (?, ?, ?)");
        if ($insertStmt->execute([$resource_id, $allocated_quantity, $remarks])) {
            $message = "Resource allocated successfully.";
        } else {
            $message = "Failed to record allocation.";
        }
    } else {
        $message = "Insufficient quantity available or resource not found.";
    }
}

$stmt = $pdo->query("SELECT * FROM resources ORDER BY category, name");
$resources = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Allocate Hospital Resources</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    /* Remove margin-left for main-content and use Bootstrap column instead */
    .main-content {
      padding: 20px;
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
            <h2 class="mb-4">Allocate Hospital Resources</h2>

            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    Available Resources
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Available Quantity</th>
                                <th>Allocate</th>
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
                                    <form method="POST" class="form-inline">
                                        <input type="hidden" name="resource_id" value="<?= $res['id'] ?>">
                                        <div class="form-group mb-2">
                                            <input type="number" name="allocated_quantity" class="form-control mr-2" 
                                                   placeholder="Qty" min="1" max="<?= $res['quantity'] ?>" required>
                                        </div>
                                        <div class="form-group mb-2 mx-sm-2">
                                            <input type="text" name="remarks" class="form-control" placeholder="Remarks (optional)">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Allocate</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($resources)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No resources available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
