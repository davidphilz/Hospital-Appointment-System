<?php
session_start();
include_once("../include/configure.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor Notice Form</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    .card {
      border-radius: 8px;
      margin-top: 20px; 
    }
    .card-header {
      background-color: #007bff; 
      color: #fff;
      border-bottom: none;
      font-weight: 500;
    }
    .card-body {
      padding: 1.5rem;
    }
    .card h2 {
      margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>
  <?php 
    include("../include/header.php"); 
  ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 sidebar p-3">
        <?php include("sidenav.php"); ?>
      </div>

      <div class="col-md-9">
        <div class="card shadow-sm">
          <div class="card-header">
            <h4 class="mb-0">Doctor Notice Form</h4>
          </div>
          <div class="card-body">
            <h2 class="mb-4">Send Notice to Admin</h2>
            <form action="process_notice.php" method="post">
              <div class="form-group mb-3">
                <label for="doctor_name">Doctor's Name</label>
                <input type="text" name="doctor_name" id="doctor_name" class="form-control" required>
              </div>
              <div class="form-group mb-3">
                <label for="notice_type">Notice Type</label>
                <select name="notice_type" id="notice_type" class="form-control" required>
                  <option value="">Select Notice Type</option>
                  <option value="fix">Fix Issue</option>
                  <option value="expired">Expired Product</option>
                  <option value="restock">Restock Required</option>
                  <option value="equipment">Medical Equipment Needed</option>
                  <option value="bed_space">Bed Space Allocation</option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label for="description">Notice Description</label>
                <textarea name="description" id="description" class="form-control" rows="5" required></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Send Notice</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
