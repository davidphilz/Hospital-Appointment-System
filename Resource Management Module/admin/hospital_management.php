<?php 
session_start();

include __DIR__ . '/../include/header.php';
require __DIR__ . '/../include/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo->prepare(
    "UPDATE rooms 
     SET is_allocated = 0, patient_name = NULL, expiry_date = NULL 
     WHERE is_allocated = 1 AND expiry_date < NOW()"
)->execute();

$message_capacity = '';
if (isset($_POST['update_capacity'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('CSRF validation failed');
    }
    $total_rooms = filter_input(INPUT_POST, 'total_rooms', FILTER_VALIDATE_INT);
    $total_beds  = filter_input(INPUT_POST, 'total_beds', FILTER_VALIDATE_INT);

    if ($total_rooms !== false && $total_beds !== false) {
        $pdo->prepare("REPLACE INTO settings (name, value) VALUES ('total_rooms', :v)")->execute(['v'=>$total_rooms]);
        $pdo->prepare("REPLACE INTO settings (name, value) VALUES ('total_beds', :v)")->execute(['v'=>$total_beds]);
        $message_capacity = "Hospital capacity updated successfully.";
    }
}
$total_rooms_setting = (int)($pdo->query("SELECT value FROM settings WHERE name='total_rooms'")->fetchColumn() ?: 0);
$total_beds_setting  = (int)($pdo->query("SELECT value FROM settings WHERE name='total_beds'")->fetchColumn() ?: 0);

$message_room = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['allocate','deallocate'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('CSRF validation failed');
    }
    $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    if ($_POST['action'] === 'allocate') {
        $patient_name = trim(filter_input(INPUT_POST, 'patient_name', FILTER_SANITIZE_STRING));
        $expiry_date  = filter_input(INPUT_POST, 'expiry_date', FILTER_SANITIZE_STRING);
        $expiry = DateTime::createFromFormat('Y-m-d', $expiry_date);
        if ($room_id && $patient_name && $expiry) {
            $stmt = $pdo->prepare(
                "UPDATE rooms SET is_allocated=1, patient_name=:p, expiry_date=:e WHERE id=:id AND is_allocated=0"
            );
            $stmt->execute([
                'p'=>$patient_name,
                'e'=>$expiry->format('Y-m-d 23:59:59'),
                'id'=>$room_id
            ]);
            $message_room = $stmt->rowCount()
                ? "Room allocated to {$patient_name} until {$expiry_date}."
                : "Allocation failed. Room may be occupied.";
        }
    } else {
        if ($room_id) {
            $stmt = $pdo->prepare(
                "UPDATE rooms SET is_allocated=0, patient_name=NULL, expiry_date=NULL WHERE id=:id AND is_allocated=1"
            );
            $stmt->execute(['id'=>$room_id]);
            $message_room = $stmt->rowCount()
                ? "Room deallocated successfully."
                : "Deallocation failed. Room may already be available.";
        }
    }
}

$rawFrom = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_STRING);
$rawTo   = filter_input(INPUT_GET, 'to',   FILTER_SANITIZE_STRING);
$from = DateTime::createFromFormat('Y-m-d', $rawFrom) ? $rawFrom : date('Y-m-01');
$to   = DateTime::createFromFormat('Y-m-d', $rawTo)   ? $rawTo   : date('Y-m-d');
$bed_id = filter_input(INPUT_GET, 'bed_id', FILTER_VALIDATE_INT);
$sql = "SELECT DATE(created_at) day, SUM(status='occupied') occupied, COUNT(*) total
        FROM beds_history WHERE created_at BETWEEN :s AND :e";
if ($bed_id) $sql .= " AND bed_id=:bid";
$sql .= " GROUP BY day ORDER BY day";
$params=['s'=>"{$from} 00:00:00",'e'=>"{$to} 23:59:59"]; if ($bed_id) $params['bid']=$bed_id;
$stmt=$pdo->prepare($sql); $stmt->execute($params); $beds_history=$stmt->fetchAll(PDO::FETCH_ASSOC);

$rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC")->fetchAll(PDO::FETCH_ASSOC);
$available_rooms = array_filter($rooms, fn($r)=>!$r['is_allocated']);
$allocated_rooms = array_filter($rooms, fn($r)=> $r['is_allocated']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Hospital Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
  body{
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
  <div class="d-flex">
    <nav class="col-md-2 sidebar p-3">
      <?php include("sidenav.php"); ?>
    </nav>
    <main class="main-content flex-grow-1">
      <h2></h2>
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">Update Capacity</div>
        <div class="card-body">
          <?php if($message_capacity):?><div class="alert alert-success"><?=htmlspecialchars($message_capacity)?></div><?php endif;?>
          <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']?>">
            <div class="col-md-4"><label class="form-label">Total Rooms</label><input type="number" name="total_rooms" class="form-control" value="<?=$total_rooms_setting?>" required></div>
            <div class="col-md-4"><label class="form-label">Total Beds</label><input type="number" name="total_beds" class="form-control" value="<?=$total_beds_setting?>" required></div>
            <div class="col-md-4 d-flex align-items-end"><button name="update_capacity" class="btn btn-primary">Update</button></div>
          </form>
        </div>
      </div>
      <!-- Room Allocation -->
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">Room Management</div>
        <div class="card-body">
          <?php if($message_room):?><div class="alert alert-info"><?=htmlspecialchars($message_room)?></div><?php endif;?>
          <form method="post" class="row g-3 mb-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']?>">
            <div class="col-md-2"><label class="form-label">Room ID</label><select name="room_id" class="form-select" required><option value="">Select</option><?php foreach($rooms as$r):?><option value="<?=$r['id']?>"><?=htmlspecialchars($r['room_number'])?></option><?php endforeach;?></select></div>
            <div class="col-md-4"><label class="form-label">Patient Name</label><input name="patient_name" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
            <div class="col-md-3 d-flex align-items-end">
              <button name="action" value="allocate" class="btn btn-success me-2">Allocate</button>
              <button name="action" value="deallocate" class="btn btn-danger">Deallocate</button>
            </div>
          </form>
          <!-- Status Table -->
          <table class="table table-bordered text-center">
            <thead class="table-dark"><tr><th>Room #</th><th>Status</th><th>Patient</th><th>Expires</th></tr></thead>
            <tbody>
              <?php foreach($rooms as$r):?><tr>
                <td><?=htmlspecialchars($r['room_number'])?></td>
                <td><?= $r['is_allocated']?'<span class="badge bg-danger">Allocated</span>':'<span class="badge bg-success">Available</span>'?></td>
                <td><?= $r['is_allocated']?htmlspecialchars($r['patient_name']):'—'?></td>
                <td><?= $r['is_allocated']&&!empty($r['expiry_date'])?htmlspecialchars((new DateTime($r['expiry_date']))->format('Y-m-d')):'—'?></td>
              </tr><?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">Bed Usage Reports</div>
        <div class="card-body">
          <form method="get" class="row g-3 mb-3">
            <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" value="<?=htmlspecialchars($from)?>" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" value="<?=htmlspecialchars($to)?>" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Bed ID</label><input type="number" name="bed_id" value="<?=htmlspecialchars($bed_id)?>" class="form-control"></div>
            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary">Run Report</button><a href="export.php?from=<?=urlencode($from)?>&to=<?=urlencode($to)?>&bed_id=<?=urlencode($bed_id)?>" class="btn btn-outline-secondary ms-2">Download CSV</a></div>
          </form>
          <canvas id="bedsChart" height="100"></canvas>
        </div>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const labels = <?= json_encode(array_column($beds_history,'day'))?>;
    const occ = <?= json_encode(array_column($beds_history,'occupied'))?>;
    const tot = <?= json_encode(array_column($beds_history,'total'))?>;
    new Chart(document.getElementById('bedsChart').getContext('2d'),{type:'line',data:{labels, datasets:[{label:'Occupied',data:occ,borderColor:'rgb(255,99,132)',tension:0.2},{label:'Total',data:tot,borderColor:'rgb(54,162,235)',borderDash:[5,5],tension:0.2}]},options:{responsive:true,plugins:{legend:{position:'top'},title:{display:true,text:'Bed Usage Over Time'}}}});
  </script>
</body>
</html>
