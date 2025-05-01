<?php
session_start();
require_once("../include/pdo.php");
include("../include/header.php");

$message = '';

// Handle room allocation
if (isset($_POST['allocate_room'])) {
    $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    $patient_name = trim(filter_input(INPUT_POST, 'patient_name', FILTER_SANITIZE_STRING));
    $expiry_date = filter_input(INPUT_POST, 'expiry_date', FILTER_SANITIZE_STRING);

    // Validate expiry date format
    $expiry = DateTime::createFromFormat('Y-m-d', $expiry_date);
    if ($room_id && $patient_name && $expiry) {
        $stmt = $pdo->prepare(
            "UPDATE rooms 
             SET is_allocated = 1, patient_name = :pname, expiry_date = :exp 
             WHERE id = :rid AND is_allocated = 0"
        );
        $stmt->execute([
            'pname' => $patient_name,
            'exp'   => $expiry->format('Y-m-d 23:59:59'),
            'rid'   => $room_id
        ]);
        if ($stmt->rowCount() > 0) {
            $message = "Room allocated to " . htmlspecialchars($patient_name) . " until " . htmlspecialchars($expiry_date) . ".";
        } else {
            $message = "Failed to allocate room. It might already be occupied.";
        }
    }
}

// Handle room deallocation
if (isset($_POST['deallocate_room'])) {
    $room_id = filter_input(INPUT_POST, 'deallocate_room', FILTER_VALIDATE_INT);
    if ($room_id) {
        $stmt = $pdo->prepare(
            "UPDATE rooms 
             SET is_allocated = 0, patient_name = NULL, expiry_date = NULL 
             WHERE id = ? AND is_allocated = 1"
        );
        $stmt->execute([$room_id]);
        if ($stmt->rowCount() > 0) {
            $message = "Room deallocated successfully.";
        } else {
            $message = "Failed to deallocate room. It may already be available.";
        }
    }
}

// Fetch lists
$available_rooms = $pdo->query(
    "SELECT id, room_number FROM rooms WHERE is_allocated = 0 ORDER BY room_number ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$allocated_rooms = $pdo->query(
    "SELECT id, room_number, patient_name FROM rooms WHERE is_allocated = 1 ORDER BY room_number ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$rooms = $pdo->query(
    "SELECT id, room_number, is_allocated, patient_name, expiry_date FROM rooms ORDER BY room_number ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-3">
            <?php include("sidenav.php"); ?>
        </div>
        <div class="col-md-10">
            <h1 class="text-center my-4">Doctor - Hospital Room Management</h1>
            <?php if ($message): ?>
                <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="row mb-4">
                <!-- Allocation Form -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">Allocate Room to Patient</div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Select Room</label>
                                    <select name="room_id" class="form-select" required>
                                        <option value="">Choose an available room</option>
                                        <?php foreach ($available_rooms as $r): ?>
                                            <option value="<?= $r['id'] ?>">Room <?= htmlspecialchars($r['room_number']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Patient Name</label>
                                    <input type="text" name="patient_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" name="expiry_date" class="form-control" required>
                                </div>
                                <button type="submit" name="allocate_room" class="btn btn-primary">
                                    <i class="fas fa-bed"></i> Allocate
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Deallocation Form -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white">Deallocate Room</div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Allocated Rooms</label>
                                    <select name="deallocate_room" class="form-select" required>
                                        <option value="">Choose a room to free</option>
                                        <?php foreach ($allocated_rooms as $r): ?>
                                            <option value="<?= $r['id'] ?>">
                                                Room <?= htmlspecialchars($r['room_number']) ?> - <?= htmlspecialchars($r['patient_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-door-open"></i> Deallocate
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">Room Status</div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Room Number</th>
                                <th>Status</th>
                                <th>Patient Name</th>
                                <th>Expiry Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><?= htmlspecialchars($room['room_number']) ?></td>
                                    <td>
                                        <?php if ($room['is_allocated']): ?>
                                            <span class="badge bg-danger">Allocated</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $room['is_allocated'] ? htmlspecialchars($room['patient_name']) : '—' ?></td>
                                    <td>
                                        <?php
                                        if ($room['is_allocated'] && !empty($room['expiry_date'])) {
                                            $dt = new DateTime($room['expiry_date']);
                                            echo htmlspecialchars($dt->format('Y-m-d'));
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
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
