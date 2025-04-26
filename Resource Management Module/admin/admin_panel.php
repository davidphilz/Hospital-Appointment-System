<?php
session_start();
include("../include/header.php"); 
$pdo = new PDO('mysql:host=localhost;dbname=resource;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$rows = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin: Messages</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
    }
    .layout-container {
      display: flex;
      min-height: 100vh;
    }
    .sidebar {
      background-color: #343a40;
      color: #fff;
      min-height: 100vh;
    }
    .sidebar a {
      color: #ccc;
      padding: 12px 20px;
      display: block;
      text-decoration: none;
      transition: 0.3s;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #495057;
      color: #fff;
    }
    .main-content {
      flex: 1;
      padding: 40px 60px;
      background-color: #f4f6f9;
    }
    h1 {
      font-size: 2rem;
      margin-bottom: 30px;
      font-weight: bold;
    }
    .table thead th {
      vertical-align: middle;
      text-align: center;
    }
    .table td, .table th {
      vertical-align: middle;
    }
    .badge-status {
      font-size: 0.9rem;
      padding: 5px 10px;
      border-radius: 12px;
    }
    .badge-yes {
      background-color: #28a745;
      color: white;
    }
    .badge-no {
      background-color: #dc3545;
      color: white;
    }
    .btn-view {
      padding: 5px 12px;
      font-size: 0.85rem;
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
    .dashboard-heading {
      color: #444;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .dashboard-subtext {
      color: #777;
      margin-bottom: 2rem;
    }
    .table thead th {
      background-color: #343a40;
      color: #fff;
      border-color: #454d55;
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
    .dashboard-card {
      color: white;
      padding: 20px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, filter 0.3s ease;
      margin-bottom: 20px;
    }
    .dashboard-card:hover {
      transform: scale(1.03);
      filter: brightness(1.1);
    }
    .dashboard-card i {
      font-size: 50px;
    }
  </style>
</head>
<body>
  <div class="layout-container">
    <nav class="col-md-2 sidebar p-3">
      <?php include("sidenav.php"); ?>
    </nav>

    <div class="main-content">
      <h1>Inbox</h1>
      <div class="table-responsive">
        <table class="table table-bordered table-hover shadow-sm bg-white">
          <thead class="table-dark text-center">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Sent At</th>
              <th>Replied?</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($rows as $m): ?>
              <tr>
                <td class="text-center"><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td><?= $m['created_at'] ?></td>
                <td class="text-center">
                  <span class="badge-status <?= $m['admin_reply'] ? 'badge-yes' : 'badge-no' ?>">
                    <?= $m['admin_reply'] ? 'Yes' : 'No' ?>
                  </span>
                </td>
                <td class="text-center">
                  <a class="btn btn-sm btn-primary btn-view" href="view_message.php?id=<?= $m['id'] ?>">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted">No messages found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
