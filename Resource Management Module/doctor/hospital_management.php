<?php
session_start();
require_once("../include/pdo.php");

$message = '';

if (isset($_POST['allocate_room'])) {
    $room_id = intval($_POST['room_id']);
    $patient_name = trim($_POST['patient_name']);

    $stmt = $pdo->prepare("UPDATE rooms SET is_allocated = 1, patient_name = ? WHERE id = ? AND is_allocated = 0");
    $stmt->execute([$patient_name, $room_id]);
    if ($stmt->rowCount() > 0) {
        $message = "Room allocated to " . htmlspecialchars($patient_name) . " successfully.";
    }
}

$stmt = $pdo->query("SELECT * FROM rooms WHERE is_allocated = 0 ORDER BY room_number ASC");
$available_rooms = $stmt->fetchAll();
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor - Hospital Room Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
       body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
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
<?php 
  include("../include/header.php"); 
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-3">
            <?php include("sidenav.php"); ?>
        </div>
        <div class="col-md-10">
            <h1 class="text-center my-4">Doctor - Hospital Room Management</h1>
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            <div class="card mb-4">
                <div class="card-header">Allocate Room to Patient</div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="room_id" class="form-label">Select Room</label>
                            <select name="room_id" id="room_id" class="form-select" required>
                                <option value="">Select an available room</option>
                                <?php foreach ($available_rooms as $room): ?>
                                    <option value="<?php echo $room['id']; ?>">
                                        Room <?php echo htmlspecialchars($room['room_number']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="patient_name" class="form-label">Patient Name</label>
                            <input type="text" name="patient_name" id="patient_name" 
                                   class="form-control" required>
                        </div>
                        <button type="submit" name="allocate_room" class="btn btn-success">
                            Allocate Room
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Room Status</div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Room Number</th>
                                <th>Status</th>
                                <th>Patient Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rooms) > 0): ?>
                                <?php foreach ($rooms as $room): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                        <td>
                                            <?php echo $room['is_allocated'] 
                                                ? '<span class="badge bg-danger">Allocated</span>' 
                                                : '<span class="badge bg-success">Available</span>'; ?>
                                        </td>
                                        <td>
                                            <?php echo $room['is_allocated']
                                                ? htmlspecialchars($room['patient_name'])
                                                : 'N/A'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No room data available.</td>
                                </tr>
                            <?php endif; ?>
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
