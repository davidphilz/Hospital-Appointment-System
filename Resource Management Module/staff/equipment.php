<?php
include("header.php");
include("../include/db.php");

if (!isset($_SESSION['staff_name'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "resource";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sqlEquip = "SELECT COUNT(*) AS total FROM equipment";
$resultEquip = $conn->query($sqlEquip);
$totalEquip = ($resultEquip && $row = $resultEquip->fetch_assoc()) ? $row['total'] : 0;

$sqlDamaged = "SELECT COUNT(*) AS damaged FROM equipment WHERE status = 'damaged'";
$resultDamaged = $conn->query($sqlDamaged);
$damagedEquip = ($resultDamaged && $row = $resultDamaged->fetch_assoc()) ? $row['damaged'] : 0;

$today = date('Y-m-d');
$sqlExpired = "SELECT COUNT(*) AS expired FROM drugs WHERE expiry_date <= '$today'";
$resultExpired = $conn->query($sqlExpired);
$expiredDrugs = ($resultExpired && $row = $resultExpired->fetch_assoc()) ? $row['expired'] : 0;

$sqlNeeded = "SELECT COUNT(*) AS needed FROM items_needed";
$resultNeeded = $conn->query($sqlNeeded);
$neededItems = ($resultNeeded && $row = $resultNeeded->fetch_assoc()) ? $row['needed'] : 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Staff Equipment Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
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

    /* Main content area */
    .container.bg-white {
      max-width: 1100px;
      margin-top: 20px;
      border-radius: 4px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      background: #fff;
    }
    .stats {
      display: flex;
      justify-content: space-around;
      margin: 20px 0;
    }
    .stat {
      padding: 10px 20px;
      border: 1px solid #ddd;
      border-radius: 4px;
      width: 22%;
      text-align: center;
      /* Remove default background so that Bootstrap classes show */
      background: transparent;
    }
    textarea {
      width: 80%;
      height: 100px;
      margin-bottom: 10px;
      padding: 10px;
      font-size: 1em;
    }
  </style>
</head>
<body>
  <!-- Container-Fluid for the entire layout -->
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar Column (narrower) -->
      <div class="col-md-2 p-0">
        <?php include("sidenav.php"); ?>
      </div>

      <!-- Main Content Column -->
      <div class="col-md-10">
        <div class="container bg-white p-4">
          <h1 class="text-center mb-4">Hospital Equipment Dashboard</h1>

          <div class="stats">
            <div class="stat bg-primary text-white">
              <h3>Total Equipment</h3>
              <p><?php echo $totalEquip; ?></p>
            </div>
            <div class="stat bg-danger text-white">
              <h3>Damaged Equipment</h3>
              <p><?php echo $damagedEquip; ?></p>
            </div>
            <div class="stat bg-warning text-dark">
              <h3>Expired Drugs</h3>
              <p><?php echo $expiredDrugs; ?></p>
            </div>
            <div class="stat bg-success text-white">
              <h3>Items Needed</h3>
              <p><?php echo $neededItems; ?></p>
            </div>
          </div>

          <h2 class="text-center mt-5">Send Notice to Doctor</h2>
          <form action="send_notice.php" method="post" class="text-center">
            <textarea name="message" placeholder="Enter your notice here..." required></textarea><br>
            <input type="hidden" name="sender" value="staff">
            <input type="hidden" name="receiver" value="doctor">
            <input type="submit" class="btn btn-success" value="Send Notice">
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
