<?php
session_start();
include("../include/connection.php");
include("../include/header.php");

if (!isset($_SESSION['admin'])) {
    header("Location: adminlogin.php");
    exit();
}

$query = "SELECT COUNT(*) AS total FROM appointments";
$result = mysqli_query($connect, $query);
$data = mysqli_fetch_assoc($result);

$_SESSION['total_patient'] = $data['total'];

$result = mysqli_query($connect, "SELECT COUNT(*) AS total FROM alerts");
$row = mysqli_fetch_assoc($result);
$total_reports = $row['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    /* Add your custom styles */
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
    #calendar-wrapper {
  max-width: 320px;
  margin: 0 auto;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  overflow: hidden;
}

#calendar-header {
  background: #dc3545;
  color: #fff;
  padding: 10px 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

#calendar-header h2 {
  margin: 0 15px;
  font-size: 1.2rem;
}

#calendar-header .nav-btn {
  cursor: pointer;
  font-weight: bold;
  color: white;
  font-size: 1.2rem;
}

#calendar-days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);  /* 7 columns for the days of the week */
  text-align: center;
  padding: 5px 0;
  background-color: #f7f7f7; /* Light grey background */
}

#calendar-days div {
  font-weight: bold;
  color: #dc3545;
}

#calendar-dates {
  display: grid;
  grid-template-columns: repeat(7, 1fr);  /* 7 columns for the dates */
  text-align: center;
  padding: 5px;
}

#calendar-dates div {
  margin: 5px;
  cursor: pointer;
  border-radius: 4px;
  line-height: 2em;
  transition: background 0.2s;
  display: flex;
  justify-content: center;
  align-items: center;
}

#calendar-dates div:hover {
  background: #f1f1f1;
}

.today {
  background: #dc3545 !important;
  color: #fff !important;
}

#calendar-dates div.empty {
  visibility: hidden; /* Hide the empty grid cells before the start of the month */
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
        <h3 class="dashboard-heading text-center">Admin Dashboard</h3>
        <div class="row">
          <div class="col-md-8">
            <div class="row g-3">
              <!-- Total Admins Card -->
              <div class="col-md-4">
                <?php
                  $ad = mysqli_query($connect, "SELECT * FROM admin");
                  $num = mysqli_num_rows($ad);
                ?>
                <div class="dashboard-card bg-success">
                  <div>
                    <h5>Total Admin</h5>
                    <div class="big-number"><?php echo $num; ?></div>
                  </div>
                  <a href="admin.php" class="text-white">
                    <i class="fas fa-user-shield"></i>
                  </a>
                </div>
              </div>
              <!-- Total Doctors Card -->
              <div class="col-md-4">
                <?php
                  $doctor = mysqli_query($connect, "SELECT * FROM doctors WHERE status='Approved'");
                  $num2 = mysqli_num_rows($doctor);
                ?>
                <div class="dashboard-card bg-info">
                  <div>
                    <h5>Total Doctors</h5>
                    <div class="big-number"><?php echo $num2; ?></div>
                  </div>
                  <a href="doctor.php" class="text-white">
                    <i class="fas fa-user-md"></i>
                  </a>
                </div>
              </div>
              <div class="col-md-4">
                <div class="dashboard-card bg-warning">
                  <div>
                    <h5>Total Patients</h5>
                    <div class="big-number">
                      <?php echo isset($_SESSION['total_patient']) ? $_SESSION['total_patient'] : 0; ?>
                    </div>
                  </div>
                  <a href="view_patients.php" class="text-white">
                    <i class="fas fa-user-shield"></i>
                  </a>
                </div>
              </div>
              <div class="col-md-4">
                <div class="dashboard-card bg-danger">
                  <div>
                    <h5>Total Report</h5>
                    <div class="big-number"><?= $total_reports ?></div>
                  </div>
                  <a href="total_report.php" class="text-white">
                    <i class="fas fa-calendar-check"></i>
                  </a>
                </div>
              </div>
              <div class="col-md-4">
                <?php
                  $job = mysqli_query($connect, "SELECT * FROM doctors WHERE status = 'pending'");
                  $num1 = mysqli_num_rows($job);
                ?>
                <div class="dashboard-card bg-primary">
                  <div>
                    <h5>Unapproved Doctors</h5>
                    <div class="big-number"><?php echo $num1; ?></div>
                  </div>
                  <a href="unapproved_doctors.php" class="text-white">
                    <i class="fas fa-user-times"></i>
                  </a>
                </div>
              </div>
              <!-- Total Income Card -->
              <div class="col-md-4">
                <div class="dashboard-card bg-secondary">
                  <div>
                    <h5>Total Income</h5>
                    <div class="big-number">
                      <?php include 'getTotalIncome.php'; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 mt-3 mt-md-0">
            <!-- Calendar Section -->
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
    // Calendar JavaScript here
    document.addEventListener("DOMContentLoaded", function() {
      const calendarDates = document.getElementById("calendar-dates");
      const monthYear     = document.getElementById("month-year");
      const prevBtn       = document.getElementById("prev-month");
      const nextBtn       = document.getElementById("next-month");
      let currentDate     = new Date();

      function renderCalendar(dateObj) {
        calendarDates.innerHTML = "";
        const year  = dateObj.getFullYear();
        const month = dateObj.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const offset   = firstDay === 0 ? 6 : firstDay - 1;
        const lastDate = new Date(year, month + 1, 0).getDate();

        const names = [
          "January","February","March","April","May","June",
          "July","August","September","October","November","December"
        ];
        monthYear.textContent = `${names[month]} ${year}`;

        for (let i = 0; i < offset; i++) {
          calendarDates.appendChild(document.createElement("div"));
        }

        for (let day = 1; day <= lastDate; day++) {
          const cell = document.createElement("div");
          cell.textContent = day;
          const now = new Date();
          if (
            day === now.getDate() &&
            month === now.getMonth() &&
            year === now.getFullYear()
          ) {
            cell.classList.add("today");
          }
          calendarDates.appendChild(cell);
        }
      }

      prevBtn.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
      });
      nextBtn.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
      });

      renderCalendar(currentDate);
    });
  </script>
</body>
</html>
