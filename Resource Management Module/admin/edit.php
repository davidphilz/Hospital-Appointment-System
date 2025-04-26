<?php
session_start();
include("../include/connection.php");

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = mysqli_prepare($connect, "SELECT * FROM doctors WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        echo "Doctor not found.";
        exit();
    }
} else {
    echo "No doctor ID provided.";
    exit();
}

$updateMsg = '';
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['update'])) {
    if (isset($_POST['salary']) && is_numeric($_POST['salary'])) {
        $salary = $_POST['salary'];
        $updateStmt = mysqli_prepare($connect, "UPDATE doctors SET salary = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "di", $salary, $id);
        if (mysqli_stmt_execute($updateStmt)) {
            $updateMsg = '<div class="alert alert-success">Salary updated successfully.</div>';
            $row['salary'] = $salary;
        } else {
            $updateMsg = '<div class="alert alert-danger">Error updating salary. Please try again later.</div>';
        }
        mysqli_stmt_close($updateStmt);
    } else {
        $updateMsg = '<div class="alert alert-warning">Please enter a valid salary.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Doctors</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    h5 {
      margin-bottom: 5px;
    }
    h3 {
      margin: 0;
      font-weight: bold;
    }
  </style>
</head>
<body>
    <?php include("../include/header.php"); ?>

    <div class="container-fluid">
      <div class="row">
        <div class="col-md-2 bg-dark p-3">
          <?php include("sidenav.php"); ?>
        </div>
        <div class="col-md-10">
          <h5 class="text-center mb-4">Edit Doctor</h5>
          <?php echo $updateMsg; ?>
          <div class="row">
            <div class="col-md-8">
              <h5 class="text-center mb-3">Doctor Details</h5>
              <div class="card mb-3">
                <div class="card-body">
                  <p><strong>ID:</strong> <?php echo htmlspecialchars($row['id']); ?></p>
                  <p><strong>Firstname:</strong> <?php echo htmlspecialchars($row['firstname']); ?></p>
                  <p><strong>Surname:</strong> <?php echo htmlspecialchars($row['surname']); ?></p>
                  <p><strong>Username:</strong> <?php echo htmlspecialchars($row['username']); ?></p>
                  <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                  <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
                  <p><strong>Gender:</strong> <?php echo htmlspecialchars($row['gender']); ?></p>
                  <p><strong>State:</strong> <?php echo htmlspecialchars($row['state']); ?></p>
                  <p><strong>Date Registered:</strong> <?php echo htmlspecialchars($row['data_reg']); ?></p>
                  <p><strong>Salary:</strong> NGN <?php echo htmlspecialchars($row['salary']); ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <h5 class="text-center mb-3">Update Salary</h5>
              <div class="card">
                <div class="card-body">
                  <form method="post">
                    <div class="mb-3">
                      <label for="salary" class="form-label">Enter Doctor's Salary</label>
                      <input type="number" name="salary" id="salary" class="form-control" autocomplete="off" placeholder="Enter Doctor's Salary" value="<?php echo htmlspecialchars($row['salary']); ?>">
                    </div>
                    <button type="submit" name="update" class="btn btn-info w-100">Update Salary</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</body>
</html>
