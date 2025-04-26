<?php
include("header.php");
include("../include/db.php");

if (!isset($_SESSION['staff_name'])) {
    header("Location: login.php");
    exit();
}
$today = date('Y-m-d');

$sqlAllEquip = "SELECT * FROM equipment";
$stmtAllEquip = $pdo->query($sqlAllEquip);
$allEquipment = $stmtAllEquip->fetchAll(PDO::FETCH_ASSOC);

$sqlDamaged = "SELECT * FROM equipment WHERE status = 'damaged'";
$stmtDamaged = $pdo->query($sqlDamaged);
$damagedEquipment = $stmtDamaged->fetchAll(PDO::FETCH_ASSOC);

$sqlExpired = "SELECT * FROM drugs WHERE expiry_date <= :today";
$stmtExpired = $pdo->prepare($sqlExpired);
$stmtExpired->execute([':today' => $today]);
$expiredDrugs = $stmtExpired->fetchAll(PDO::FETCH_ASSOC);

$sqlNeeded = "SELECT * FROM items_needed";
$stmtNeeded = $pdo->query($sqlNeeded);
$itemsNeeded = $stmtNeeded->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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

      <div class="col-md-10">
        <div class="container bg-white p-4 mt-3 rounded shadow-sm">
          <h1>Total Equipment</h1>
          <h2>All Equipment</h2>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Equipment Name</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allEquipment as $equip): ?>
                <tr>
                  <td><?php echo htmlspecialchars($equip['id']); ?></td>
                  <td><?php echo htmlspecialchars($equip['equipment_name']); ?></td>
                  <td><?php echo htmlspecialchars($equip['status']); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <h2>Damaged Equipment</h2>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Equipment Name</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($damagedEquipment as $equip): ?>
                <tr>
                  <td><?php echo htmlspecialchars($equip['id']); ?></td>
                  <td><?php echo htmlspecialchars($equip['equipment_name']); ?></td>
                  <td><?php echo htmlspecialchars($equip['status']); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <h2>Expired Drugs</h2>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Drug Name</th>
                  <th>Expiry Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($expiredDrugs as $drug): ?>
                <tr>
                  <td><?php echo htmlspecialchars($drug['id']); ?></td>
                  <td><?php echo htmlspecialchars($drug['drug_name']); ?></td>
                  <td><?php echo htmlspecialchars($drug['expiry_date']); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <h2>Items Needed</h2>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Item Name</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($itemsNeeded as $item): ?>
                <tr>
                  <td><?php echo htmlspecialchars($item['id']); ?></td>
                  <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
