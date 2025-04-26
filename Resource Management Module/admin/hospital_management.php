<?php  
session_start();
include("../include/header.php");
require_once("../include/pdo.php");

$message = '';
if (isset($_POST['update_capacity'])) {
    $total_rooms = intval($_POST['total_rooms']);
    $total_beds  = intval($_POST['total_beds']);

    $stmt = $pdo->prepare("REPLACE INTO settings (name, value) VALUES ('total_rooms', ?)");
    $stmt->execute([$total_rooms]);

    $stmt = $pdo->prepare("REPLACE INTO settings (name, value) VALUES ('total_beds', ?)");
    $stmt->execute([$total_beds]);

    $message = "Hospital capacity updated successfully.";
}

$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'total_rooms'");
$stmt->execute();
$total_rooms_setting = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'total_beds'");
$stmt->execute();
$total_beds_setting = $stmt->fetchColumn() ?? 0;

// Fetch room status
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Hospital Room Management</title>
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
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 sidebar p-3">
            <?php include("sidenav.php"); ?>
        </nav>
        <main class="col-md-10 main-content">
            <h2 class="text-center mb-4">Update</h2>

            <?php if ($message): ?>
                <div class="alert alert-success text-center"><?php echo $message; ?></div>
            <?php endif; ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Update Hospital Capacity</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="total_rooms" class="form-label">Total Rooms Available</label>
                            <input type="number" name="total_rooms" id="total_rooms" class="form-control" required value="<?php echo $total_rooms_setting; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="total_beds" class="form-label">Total Bed Spaces Available</label>
                            <input type="number" name="total_beds" id="total_beds" class="form-control" required value="<?php echo $total_beds_setting; ?>">
                        </div>
                        <button type="submit" name="update_capacity" class="btn btn-primary">Update Capacity</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Room Status</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered text-center">
                        <thead>
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
                                                ? '<span class="badge badge-danger">Allocated</span>' 
                                                : '<span class="badge badge-success">Available</span>'; ?>
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

                    <div class="text-end mt-3">
                        <p><strong>Total Rooms:</strong> <?php echo $total_rooms_setting; ?></p>
                        <p><strong>Total Bed Spaces:</strong> <?php echo $total_beds_setting; ?></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
