<?php  
require_once("../include/pdo.php");
include("header.php");
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
$rooms = $stmt->fetchAll();
$stmt2 = $pdo->query("SELECT COUNT(*) FROM rooms WHERE is_allocated = 0");
$available_rooms_count = $stmt2->fetchColumn();

$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = ?");
$stmt->execute(['total_rooms']);
$total_rooms_setting = $stmt->fetchColumn();

$stmt->execute(['total_beds']);
$total_beds_setting = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Medical Staff - Hospital Room Management</title>
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
      <!-- Sidebar Column -->
      <div class="col-md-2 p-0">
        <?php include("sidenav.php"); ?>
      </div>
      <div class="col-md-9">
        <main class="py-4">
          <div class="container">
            <h2 class="text-center mb-4">Medical Staff Portal</h2>
            <div class="card shadow-sm">
              <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Room Status</h5>
              </div>
              <div class="card-body">
                <table class="table table-bordered table-hover text-center">
                  <thead class="table-light">
                    <tr>
                      <th>Room Number</th>
                      <th>Status</th>
                      <th>Patient Name</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($rooms)): ?>
                      <?php foreach ($rooms as $room): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                          <td>
                            <?php echo $room['is_allocated'] 
                              ? '<span class="badge bg-danger">Allocated</span>' 
                              : '<span class="badge bg-success">Available</span>'; ?>
                          </td>
                          <td><?php echo $room['is_allocated'] ? htmlspecialchars($room['patient_name']) : 'N/A'; ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="3">No room data available.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
                
                <?php if ($total_rooms_setting): ?>
                  <p><strong>Total Rooms:</strong> <?php echo $total_rooms_setting; ?></p>
                <?php endif; ?>
                <?php if ($total_beds_setting): ?>
                  <p><strong>Total Bed Spaces:</strong> <?php echo $total_beds_setting; ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
