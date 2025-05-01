<?php
session_start();
include __DIR__ . '/../include/header.php';
require __DIR__ . '/../include/db.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rawFrom = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_STRING);
$rawTo   = filter_input(INPUT_GET, 'to',   FILTER_SANITIZE_STRING);

$from = DateTime::createFromFormat('Y-m-d', $rawFrom)
    ? $rawFrom
    : date('Y-m-01');
$to = DateTime::createFromFormat('Y-m-d', $rawTo)
    ? $rawTo
    : date('Y-m-d');
$bed_id = filter_input(INPUT_GET, 'bed_id', FILTER_VALIDATE_INT);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bed_id'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $newBedId = filter_input(INPUT_POST, 'bed_id', FILTER_VALIDATE_INT);
    if ($newBedId) {
        $stmt = $pdo->prepare(
            "INSERT INTO beds_history (bed_id, status) VALUES (:bed_id, 'occupied')"
        );
        $stmt->execute(['bed_id' => $newBedId]);
        header("Location: ?from={$from}&to={$to}&bed_id={$bed_id}");
        exit;
    }
}

$sql = <<<SQL
SELECT
    DATE(created_at) AS day,
    SUM(status = 'occupied') AS occupied,
    COUNT(*) AS total
FROM beds_history
WHERE created_at BETWEEN :start AND :end
SQL;
if ($bed_id) {
    $sql .= " AND bed_id = :bed_id";
}
$sql .= " GROUP BY day ORDER BY day";

$params = [
    ':start' => "{$from} 00:00:00",
    ':end'   => "{$to} 23:59:59",
];
if ($bed_id) {
    $params[':bed_id'] = $bed_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$beds_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hospital Resource Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <main class="main-content">
      <h2 class="mb-4">Reports &amp; Analytics</h2>

      <form class="row g-3 mb-5" method="get">
        <div class="col-md-3">
          <label for="fromDate" class="form-label">From</label>
          <input type="date" id="fromDate" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control">
        </div>
        <div class="col-md-3">
          <label for="toDate" class="form-label">To</label>
          <input type="date" id="toDate" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control">
        </div>
        <div class="col-md-3">
          <label for="bedId" class="form-label">Bed ID (optional)</label>
          <input type="number" id="bedId" name="bed_id" value="<?= htmlspecialchars($bed_id) ?>" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary me-2">Run Report</button>
          <a href="export.php?from=<?=urlencode($from)?>&to=<?=urlencode($to)?>&bed_id=<?=urlencode($bed_id)?>" class="btn btn-outline-secondary">
            <i class="fas fa-download"></i> Download CSV
          </a>
        </div>
      </form>

      <section>
        <canvas id="bedsChart" height="100"></canvas>
      </section>

      <!-- <section class="mt-5">
        <h3 class="mb-3">Mark Bed as Occupied</h3>
        <form method="post" class="row g-3 align-items-end">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <div class="col-md-3">
            <label for="newBedId" class="form-label">Bed ID</label>
            <input type="number" id="newBedId" name="bed_id" class="form-control" required>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-success">Mark Occupied</button>
          </div>
        </form>
      </section> -->
    </main>
  </div>

  <script>
  const dataLabels = <?= json_encode(array_column($beds_history, 'day')) ?>;
  const occupiedData = <?= json_encode(array_column($beds_history, 'occupied')) ?>;
  const totalData = <?= json_encode(array_column($beds_history, 'total')) ?>;

  new Chart(
    document.getElementById('bedsChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: dataLabels,
        datasets: [
          {
            label: 'Occupied Beds',
            data: occupiedData,
            backgroundColor: 'rgba(255, 99, 132, 0.7)',
            borderColor: 'rgb(255, 99, 132)',
            borderWidth: 1
          },
          {
            label: 'Total Records',
            data: totalData,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'Bed Usage Over Time (Bar Chart)'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    }
  );
</script>
