<?php

include("header.php");
include("../include/db.php");


if (!isset($_SESSION['staff_name'])) {
    header("Location: login.php");
    exit();
}

$assets = [
  [ 'id' => 1, 'asset_type' => 'Room 101' ],
  [ 'id' => 2, 'asset_type' => 'Room 102' ],
  [ 'id' => 3, 'asset_type' => 'Room 103' ],
  [ 'id' => 4, 'asset_type' => 'Equipment: X-Ray Machine' ],
  [ 'id' => 5, 'asset_type' => 'Equipment: Ultrasound' ],
  [ 'id' => 6, 'asset_type' => 'Equipment: ECG Machine' ],
  [ 'id' => 7, 'asset_type' => 'Equipment: Ventilator' ],
  [ 'id' => 8, 'asset_type' => 'Equipment: Defibrillator' ],
  [ 'id' => 9, 'asset_type' => 'Equipment: Pacemaker' ],
  [ 'id' => 10, 'asset_type' => 'Equipment: Stress Tester' ],
  [ 'id' => 11, 'asset_type' => 'Room 104' ],
  [ 'id' => 12, 'asset_type' => 'Room 105' ],
  [ 'id' => 13, 'asset_type' => 'Room 106' ],
  [ 'id' => 14, 'asset_type' => 'Room 107' ],
  [ 'id' => 15, 'asset_type' => 'Room 108' ],
  [ 'id' => 16, 'asset_type' => 'Room 109' ],
  [ 'id' => 17, 'asset_type' => 'Room 110' ],
  [ 'id' => 18, 'asset_type' => 'Room 111' ],
  [ 'id' => 19, 'asset_type' => 'Room 112' ],
  [ 'id' => 20, 'asset_type' => 'Room 113' ],
  [ 'id' => 21, 'asset_type' => 'Room 114' ],
  [ 'id' => 22, 'asset_type' => 'Drug: Acetaminophen (Tylenol)' ],
  [ 'id' => 23, 'asset_type' => 'Drug: Aspirin (Advil)' ],
  [ 'id' => 24, 'asset_type' => 'Drug: ibuprofen (Advil)' ],
  [ 'id' => 25, 'asset_type' => 'Drug: Metformin (HCT)' ],
  [ 'id' => 26, 'asset_type' => 'Drug: Prednisone (Prednisolone)' ],
  [ 'id' => 27, 'asset_type' => 'Drug: Aspirin (Aspirin)' ],
  [ 'id' => 28, 'asset_type' => 'Drug: ibuprofen (Ibuprofen)' ],
  [ 'id' => 29, 'asset_type' => 'Drug: Metformin (Metformin)' ],
  [ 'id' => 30, 'asset_type' => 'Drug: Prednisone (Prednisone)' ],
  [ 'id' => 31, 'asset_type' => 'Drug: Acetaminophen (Tylenol)' ],
  [ 'id' => 32, 'asset_type' => 'Drug: Ibuprofen (Advil)' ],
  [ 'id' => 33, 'asset_type' => 'Drug: Amoxicillin' ],
  [ 'id' => 34, 'asset_type' => 'Drug: Ciprofloxacin' ],
  [ 'id' => 35, 'asset_type' => 'Drug: Metformin' ],
  [ 'id' => 36, 'asset_type' => 'Drug: Atorvastatin' ],
  [ 'id' => 37, 'asset_type' => 'Drug: Omeprazole' ],
  [ 'id' => 38, 'asset_type' => 'Drug: Hydrochlorothiazide' ],
  [ 'id' => 39, 'asset_type' => 'Drug: Lisinopril' ],
  [ 'id' => 40, 'asset_type' => 'Drug: Amlodipine' ],
  [ 'id' => 41, 'asset_type' => 'Drug: Warfarin' ],
  [ 'id' => 42, 'asset_type' => 'Drug: Lisinopril' ],
  [ 'id' => 43, 'asset_type' => 'Drug: Amlodipine' ],
  [ 'id' => 44, 'asset_type' => 'Drug: Amlodipine' ],
  [ 'id' => 45, 'asset_type' => 'Drug: Warfarin' ],
  [ 'id' => 46, 'asset_type' => 'Drug: Lisinopril' ],
  [ 'id' => 47, 'asset_type' => 'Room 115' ],
  [ 'id' => 48, 'asset_type' => 'Room 116' ],
  [ 'id' => 49, 'asset_type' => 'Room 117' ],
  [ 'id' => 50, 'asset_type' => 'Room 118' ],
  [ 'id' => 51, 'asset_type' => 'Room 119' ],
  [ 'id' => 52, 'asset_type' => 'Room 120' ],
  [ 'id' => 53, 'asset_type' => 'Room 121' ],
  [ 'id' => 54, 'asset_type' => 'Room 122' ],
  [ 'id' => 55, 'asset_type' => 'Room 123' ],
  [ 'id' => 56, 'asset_type' => 'Room 124' ],
  [ 'id' => 57, 'asset_type' => 'Room 125' ],
  [ 'id' => 58, 'asset_type' => 'Room 126' ],
  [ 'id' => 59, 'asset_type' => 'Room 127' ],
  [ 'id' => 60, 'asset_type' => 'Room 128' ],
  [ 'id' => 61, 'asset_type' => 'Room 129' ],
  [ 'id' => 62, 'asset_type' => 'Room 130' ],
  [ 'id' => 63, 'asset_type' => 'Room 131' ],
  [ 'id' => 64, 'asset_type' => 'Room 132' ],

];


$staff = $pdo->query("SELECT id, name FROM staff")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asset_id = $_POST['asset_id'];
    $staff_id = $_POST['staff_id'];

    $stmt = $pdo->prepare("UPDATE hospital_assets SET status='occupied' WHERE id = :asset_id");
    $stmt->execute([':asset_id' => $asset_id]);


    $message = "Asset ID $asset_id has been assigned to Staff ID $staff_id.";
    $stmt = $pdo->prepare("INSERT INTO hospital_alerts (message) VALUES (:message)");
    $stmt->execute([':message' => $message]);

    $_SESSION['success'] = "Asset assigned successfully!";
    header("Location: assign_resource.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Assign Asset</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #e2e2e2, #c9d6ff);
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 550px;
      margin-top: 50px;
    }
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      padding: 25px;
      background-color: #fff;
    }
    .card h3 {
      font-weight: 600;
      font-size: 1.5rem;
    }
    .btn-primary {
      background: linear-gradient(45deg, #007bff, #0062cc);
      border: none;
      transition: all 0.3s ease-in-out;
      font-weight: 600;
    }
    .btn-primary:hover {
      background: linear-gradient(45deg, #0056b3, #004085);
    }
    .form-control {
      border-radius: 8px;
    }
    .alert {
      border-radius: 8px;
    }
    .mb-4 {
      margin-bottom: 1.5rem !important;
    }
    .mt-3 {
      margin-top: 1rem !important;
    }
    .mt-4 {
      margin-top: 1.5rem !important;
    }
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

<div class="container">
  <div class="card">
    <h3 class="text-center mb-4">Assign Asset</h3>

    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <form method="post" action="assign_resource.php">
      <div class="form-group">
        <label for="asset_id"><strong>Select Asset:</strong></label>
        <select class="form-control" id="asset_id" name="asset_id" required>
          <option value="">-- Choose Asset --</option>
          <?php foreach ($assets as $asset): ?>
            <option value="<?= htmlspecialchars($asset['id']) ?>">
              <?= htmlspecialchars($asset['asset_type']) ?> (ID: <?= $asset['id'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group mt-3">
        <label for="staff_id"><strong>Select Staff:</strong></label>
        <select class="form-control" id="staff_id" name="staff_id" required>
          <option value="">-- Choose Staff --</option>
          <?php foreach ($staff as $member): ?>
            <option value="<?= htmlspecialchars($member['id']) ?>">
              <?= htmlspecialchars($member['name']) ?> (ID: <?= $member['id'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-block mt-4">
        <i class="fas fa-plus-circle"></i> Assign Asset
      </button>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
