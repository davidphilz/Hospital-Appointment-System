<?php
session_start();
include("../include/header.php");

if (!isset($_SESSION['doctor'])) {
    header("Location: doctorlogin.php");
    exit();
}

$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "hospital_appointment_system";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_id'])) {
    $alertId = intval($_POST['mark_read_id']);
    $stmtMark = $conn->prepare("UPDATE alerts SET is_read = 1 WHERE id = ?");
    $stmtMark->bind_param("i", $alertId);
    $stmtMark->execute();
}

$alerts = $conn->query(
    "SELECT id, title, message, created_at, is_read
     FROM alerts
     ORDER BY created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Doctor's Dashboard</title>
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
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 20px;
      height: 160px;
    }
    .dashboard-card:hover {
      transform: scale(1.03);
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
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
    #calendar-wrapper {
      max-width: 320px;
      margin: 0 auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    #calendar-header {
      background: #495057;
      color: #fff;
      padding: 15px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border-bottom: 1px solid #dee2e6;
    }
    #calendar-header h2 {
      margin: 0 20px;
      font-size: 1.2rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    #calendar-header .nav-btn {
      cursor: pointer;
      font-weight: bold;
      font-size: 1.2rem;
      margin: 0 10px;
      transition: color 0.3s ease;
    }
    #calendar-header .nav-btn:hover {
      color: #ffc107;
    }
    #calendar-days {
      background: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      padding: 10px 0;
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
      font-weight: 600;
      color: #495057;
    }
    #calendar-dates {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
      padding: 10px;
    }
    #calendar-dates div {
      margin: 5px 0;
      cursor: pointer;
      border-radius: 4px;
      line-height: 2em;
      transition: background 0.2s, color 0.2s;
      color: #333;
    }
    #calendar-dates div:hover {
      background: #e9ecef;
    }
    .today {
      background: #dc3545 !important;
      color: #fff !important;
      font-weight: 600;
    }
  </style>
</head>
<body>

  <div class="container-fluid px-0">
    <div class="row gx-0 vh-100">
      <div class="col-md-2 sidebar p-3">
        <?php include("sidenav.php"); ?>
      </div>
      <div class="col-md-10 p-4">
        <h3 class="dashboard-heading text-center">Doctor's Dashboard</h3>
        <div class="row">
          <div class="col-md-8">
            <div class="row g-3">
              <div class="col-md-4">
                <div class="dashboard-card bg-info">
                  <div>
                    <h5>My Profile</h5>
                  </div>
                  <div>
                    <a href="profile.php" class="text-white">
                      <i class="fa fa-user-circle"></i>
                    </a>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="dashboard-card bg-warning">
                  <div>
                    <div class="big-number">0</div>
                    <h5>Total Patients</h5>
                  </div>
                  <div>
                    <a href="#" class="text-white">
                      <i class="fa fa-procedures"></i>
                    </a>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="dashboard-card bg-success">
                  <div>
                    <div class="big-number">0</div>
                    <h5>Total Appointments</h5>
                  </div>
                  <div>
                    <a href="#" class="text-white">
                      <i class="fa fa-calendar"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div id="calendar-wrapper">
              <div id="calendar-header">
                <span class="nav-btn" id="prev-month">&#10094;</span>
                <h2 id="month-year"></h2>
                <span class="nav-btn" id="next-month">&#10095;</span>
              </div>
              <div id="calendar-days">
                <div>MON</div><div>TUE</div><div>WED</div>
                <div>THU</div><div>FRI</div><div>SAT</div><div>SUN</div>
              </div>
              <div id="calendar-dates"></div>
            </div>
          </div>
        </div>

        <!-- Notifications Section -->
        <div class="mt-4">
          <h5>Notifications</h5>
          <?php if ($alerts && $alerts->num_rows > 0): ?>
            <?php while ($row = $alerts->fetch_assoc()): ?>
              <form method="post" class="mb-3">
                <div class="alert <?php echo $row['is_read'] ? 'alert-secondary' : 'alert-info'; ?> d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                    <small class="text-muted">at <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                  </div>
                  <?php if (!$row['is_read']): ?>
                    <button type="submit" name="mark_read_id" value="<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-light">Mark as Read</button>
                  <?php endif; ?>
                </div>
              </form>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted">No notifications to show.</p>
          <?php endif; ?>
        </div>

      </div>

    </div>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
  <script>
    let currentDate = new Date();
    function renderCalendar(dateObj) {
      const datesEl = document.getElementById("calendar-dates");
      const monthYearEl = document.getElementById("month-year");
      datesEl.innerHTML = "";

      const year = dateObj.getFullYear();
      const month = dateObj.getMonth();
      const firstDay = new Date(year, month, 1).getDay();
      const lastDate = new Date(year, month + 1, 0).getDate();
      const offset = (firstDay === 0) ? 6 : firstDay - 1;

      const names = ["January","February","March","April","May","June","July","August","September","October","November","December"];
      monthYearEl.textContent = names[month] + " " + year;

      for (let i=0; i<offset; i++) datesEl.appendChild(document.createElement("div"));
      for (let d=1; d<=lastDate; d++) {
        const cell = document.createElement("div");
        cell.textContent = d;
        const today = new Date();
        if (d===today.getDate() && month===today.getMonth() && year===today.getFullYear()) {
          cell.classList.add("today");
        }
        datesEl.appendChild(cell);
      }
    }
    document.getElementById("prev-month").onclick = () => { currentDate.setMonth(currentDate.getMonth()-1); renderCalendar(currentDate); };
    document.getElementById("next-month").onclick = () => { currentDate.setMonth(currentDate.getMonth()+1); renderCalendar(currentDate); };
    renderCalendar(currentDate);
  </script>
</body>
</html>
