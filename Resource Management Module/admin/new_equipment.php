<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: adminlogin.php");
    exit();
}
include("../include/header.php");
include("../include/db.php");

if (isset($_POST['add_equipment'])) {
    $equipment_name = $_POST['equipment_name'];
    $status         = $_POST['status'];

    $sqlInsert = "INSERT INTO equipment (equipment_name, status) 
                  VALUES (:ename, :stat)";
    $stmt = $pdo->prepare($sqlInsert);
    $stmt->execute([
        ':ename' => $equipment_name,
        ':stat'  => $status
    ]);
}

if (isset($_POST['add_drug'])) {
    $selected_drug = $_POST['drug_name'];
    $expiry_date   = $_POST['expiry_date'];
    $sqlInsertDrug = "INSERT INTO drugs (drug_name, expiry_date) 
                      VALUES (:dname, :edate)";
    $stmt = $pdo->prepare($sqlInsertDrug);
    $stmt->execute([
        ':dname' => $selected_drug,
        ':edate' => $expiry_date
    ]);
}

if (isset($_POST['add_item_needed'])) {
    $item_name = $_POST['item_name'];

    $sqlInsertItem = "INSERT INTO items_needed (item_name) 
                      VALUES (:iname)";
    $stmt = $pdo->prepare($sqlInsertItem);
    $stmt->execute([
        ':iname' => $item_name
    ]);
}

$sqlEquip = "SELECT COUNT(*) AS total FROM equipment";
$stmt = $pdo->query($sqlEquip);
$row = $stmt->fetch(\PDO::FETCH_ASSOC);
$totalEquip = $row['total'] ?? 0;

$sqlDamaged = "SELECT COUNT(*) AS damaged 
               FROM equipment 
               WHERE status = 'damaged'";
$stmt = $pdo->query($sqlDamaged);
$row = $stmt->fetch(\PDO::FETCH_ASSOC);
$damagedEquip = $row['damaged'] ?? 0;

$today = date('Y-m-d');
$sqlExpired = "SELECT COUNT(*) AS expired 
               FROM drugs 
               WHERE expiry_date <= :today";
$stmt = $pdo->prepare($sqlExpired);
$stmt->execute([':today' => $today]);
$row = $stmt->fetch(\PDO::FETCH_ASSOC);
$expiredDrugs = $row['expired'] ?? 0;

$sqlNeeded = "SELECT COUNT(*) AS needed FROM items_needed";
$stmt = $pdo->query($sqlNeeded);
$row = $stmt->fetch(\PDO::FETCH_ASSOC);
$neededItems = $row['needed'] ?? 0;

$sqlFetchDrugs = "SELECT DISTINCT drug_name 
                  FROM drugs
                  WHERE drug_name IS NOT NULL 
                  ORDER BY drug_name ASC";
$stmt = $pdo->query($sqlFetchDrugs);
$availableDrugs = $stmt->fetchAll(\PDO::FETCH_COLUMN);

$equipmentTypes = [
    'Stethoscope',
    'Blood Pressure Monitor',
    'Thermometer',
    'Syringe Pump',
    'ECG Machine',
    'Ultrasound Machine',
    'Ventilator',
    'Defibrillator',
    'Pacemaker',
    'Stress Tester',
    'Oxygen Tank',
    'Medical Supplies',
    'Surgical Supplies',
    'Lab Equipment',
    'Pharmacy Supplies',
    'Cleaning Supplies',
    'Disinfectants',
    'Antiseptics',
    'Antibiotics',
    'Antifungals',
];

$drugList = [
  'Aspirin',
  'Ibuprofen',
  'Paracetamol',
  'Amoxicillin',
  'Metronidazole',
  'Penicillin',
  'Ciprofloxacin',
  'Acetaminophen',
  'Omeprazole',
  'Loratadine',
  'Warfarin',
  'Lisinopril',
  'Amlodipine',
  'Metformin',
  'Atorvastatin',
  'Hydrochlorothiazide',
  'Furosemide',
  'Benzodiazepines',
  'Antidepressants',
  'Antihistamines',
  'Antimalarials',
  'Anticoagulants',
  'Metronidazole',
  'Diazepines',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Equipment & Resources</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
    }
    /* Sidebar */
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
    /* Dashboard Cards */
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
<div class="container-fluid px-0">
    <div class="row gx-0 vh-100">
        <div class="col-md-2 sidebar p-3">
            <?php include("sidenav.php"); ?>
        </div>
        <div class="col-md-10 main-content">
            <div class="container-fluid">
                <h1 class="dashboard-heading">Manage Equipment & Resources</h1>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Total Equipment</h5>
                                <p class="card-text display-6"><?php echo $totalEquip; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Damaged Equipment</h5>
                                <p class="card-text display-6"><?php echo $damagedEquip; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Expired Drugs</h5>
                                <p class="card-text display-6"><?php echo $expiredDrugs; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Items Needed</h5>
                                <p class="card-text display-6"><?php echo $neededItems; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Add New Equipment</h5>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="equipment_name" class="form-label">Equipment Name</label>
                                <select name="equipment_name" id="equipment_name" class="form-select" required>
                                    <option value="">-- Select Equipment --</option>
                                    <?php foreach ($equipmentTypes as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>">
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="good">Good</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                            <button type="submit" name="add_equipment" class="btn btn-primary">Add Equipment</button>
                        </form>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Add Drug</h5>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="drug_name" class="form-label">Select Drug Name</label>
                                <select name="drug_name" id="drug_name" class="form-select" required>
                                    <option value="">-- Select a Drug --</option>
                                    <?php foreach ($drugList as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>">
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
                            </div>
                            <button type="submit" name="add_drug" class="btn btn-danger">Add Drug</button>
                        </form>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Add Item Needed</h5>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="item_name" class="form-label">Item Name</label>
                                <input type="text" name="item_name" id="item_name" class="form-control" required>
                            </div>
                            <button type="submit" name="add_item_needed" class="btn btn-success">Add Needed Item</button>
                        </form>
                    </div>
                </div>
            </div><!-- End Container Fluid -->
        </div><!-- End Main Content Column -->
    </div><!-- End Row -->
</div><!-- End Container Fluid -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
