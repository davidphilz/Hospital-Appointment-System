<?php
session_start();
if (!isset($_SESSION['doctor'])) {
    header("Location: doctorlogin.php");
    exit();
}

include("../include/header.php");
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
                <div>MON</div>
                <div>TUE</div>
                <div>WED</div>
                <div>THU</div>
                <div>FRI</div>
                <div>SAT</div>
                <div>SUN</div>
              </div>
              <div id="calendar-dates"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

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

      // Convert Sunday=0 to Monday-based layout
      const offset = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1;

      const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
      ];
      monthYear.textContent = monthNames[month] + " " + year;

      for (let i = 0; i < offset; i++) {
        const emptyCell = document.createElement("div");
        calendarDates.appendChild(emptyCell);
      }

      for (let day = 1; day <= lastDateOfMonth; day++) {
        const dayCell = document.createElement("div");
        dayCell.textContent = day;

        const isToday = (
          day === new Date().getDate() &&
          month === new Date().getMonth() &&
          year === new Date().getFullYear()
        );
        if (isToday) {
          dayCell.classList.add("today");
        }
        calendarDates.appendChild(dayCell);
      }
    };

    document.getElementById("prev-month").addEventListener("click", () => {
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderCalendar(currentDate);
    });
    document.getElementById("next-month").addEventListener("click", () => {
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderCalendar(currentDate);
    });

    renderCalendar(currentDate);
  </script>
</body>
</html>
