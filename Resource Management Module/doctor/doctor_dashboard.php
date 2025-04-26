<?php
session_start();
include("../include/header.php");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "resource";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve notices sent to the doctor from staff
$sqlNotices = "SELECT * FROM hospital_notices WHERE receiver = 'doctor' ORDER BY created_at DESC";
$resultNotices = $conn->query($sqlNotices);
$notices = [];
if ($resultNotices && $resultNotices->num_rows > 0) {
    while($row = $resultNotices->fetch_assoc()){
        $notices[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
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
    .notice {
      background: #fff;
      padding: 15px;
      margin-bottom: 10px;
      border-radius: 5px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .notice p {
      margin: 0 0 5px 0;
    }
    textarea {
      width: 100%;
      height: 120px;
      margin-bottom: 10px;
      padding: 10px;
      font-size: 1em;
    }
    input[type="submit"] {
      background: #007bff;
      color: #fff;
      padding: 8px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1em;
    }
    input[type="submit"]:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>

<!-- Container for entire layout -->
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 sidebar p-3">
      <?php include("sidenav.php"); ?>
    </nav>

    <!-- Main Content -->
    <main class="col-md-10 py-4">
      <div class="container">


        <h2>Notices from Staff</h2>
        <?php if(empty($notices)): ?>
          <p>No notices from staff.</p>
        <?php else: ?>
          <?php foreach($notices as $notice): ?>
            <div class="notice">
              <p><?php echo htmlspecialchars($notice['message']); ?></p>
              <small>Sent on: <?php echo htmlspecialchars($notice['created_at']); ?></small>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
