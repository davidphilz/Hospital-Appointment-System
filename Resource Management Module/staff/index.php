<?php
include("../include/db.php");
include("header.php");


if (!isset($_SESSION['staff_name'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Staff Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
    #calendar-wrapper {
      max-width: 320px;
      margin: 20px auto;
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
<div class="container-fluid">
  <div class="row">
    <div class="col-md-2 p-0">
      <?php include("sidenav.php"); ?>
    </div>
    <div class="col-md-10 content">
      <h2 class="mb-4">Staff Dashboard</h2>
      <div class="row">
        <div class="col-md-8">
          <div class="row">
            <div class="col-md-4">
              <div class="dashboard-card bg-success">
                <div>
                  <h5>Available Rooms</h5>
                  <div class="big-number"><?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE is_allocated = 0");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                  ?></div>
                </div>
                <i class="fas fa-bed"></i>
              </div>
            </div>
            <div class="col-md-4">
              <div class="dashboard-card bg-info">
                <div>
                  <h5>Available Equipment</h5>
                  <div class="big-number"><?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment WHERE status = 0");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                  ?></div>
                </div>
                <i class="fas fa-stethoscope"></i>
              </div>
            </div>
            <div class="col-md-4">
              <div class="dashboard-card bg-primary">
                <div>
                  <h5>Staff On Duty</h5>
                  <div class="big-number"><?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                  ?></div>
                </div>
                <i class="fas fa-user-nurse"></i>
              </div>
            </div>
          </div>

          <!-- Original Recent Notifications -->
          <h3>Recent Notifications</h3>
          <ul class="list-group mb-4">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM hospital_alerts ORDER BY created_at DESC LIMIT 5");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">'
                     . htmlspecialchars($row['message'])
                     . '<span class="badge badge-secondary badge-pill">'
                     . htmlspecialchars($row['created_at'])
                     . '</span></li>';
            }
            ?>
          </ul>

          <!-- New Alert System Notifications -->
          <h3>Alert</h3>
          <ul class="list-group mb-4">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM alerts ORDER BY created_at DESC LIMIT 5");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">'
                     . htmlspecialchars($row['message'])
                     . '<span class="badge badge-secondary badge-pill">'
                     . htmlspecialchars($row['created_at'])
                     . '</span></li>';
            }
            ?>
          </ul>

        </div>

        <div class="col-md-4">
          <div id="calendar-wrapper">
            <div id="calendar-header">
              <span class="nav-btn" id="prev-month">&#10094;</span>
              <h2 id="month-year"></h2>
              <span class="nav-btn" id="next-month">&#10095;</span>
            </div>
            <div id="calendar-days">
              <div>MON</div><div>TUE</div><div>WED</div><div>THU</div><div>FRI</div><div>SAT</div><div>SUN</div>
            </div>
            <div id="calendar-dates"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
  let currentDate = new Date();
  const renderCalendar = (dateObj) => {
    const calendarDates = document.getElementById("calendar-dates");
    const monthYear = document.getElementById("month-year");
    calendarDates.innerHTML = "";

    const year = dateObj.getFullYear();
    const month = dateObj.getMonth();
    const firstDayOfMonth = new Date(year, month, 1).getDay();
    const lastDateOfMonth = new Date(year, month + 1, 0).getDate();

    const offset = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1;
    const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    monthYear.textContent = monthNames[month] + " " + year;

    for (let i = 0; i < offset; i++) { calendarDates.appendChild(document.createElement("div")); }
    for (let day = 1; day <= lastDateOfMonth; day++) {
      const dayCell = document.createElement("div");
      dayCell.textContent = day;
      if (day === new Date().getDate() && month === new Date().getMonth() && year === new Date().getFullYear()) {
        dayCell.classList.add("today");
      }
      calendarDates.appendChild(dayCell);
    }
  };
  document.getElementById("prev-month").addEventListener("click", () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(currentDate); });
  document.getElementById("next-month").addEventListener("click", () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(currentDate); });
  renderCalendar(currentDate);
</script>
</body>
</html>