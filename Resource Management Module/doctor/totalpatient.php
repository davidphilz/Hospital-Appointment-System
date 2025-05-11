<?php
session_start();
include('includes/header.php');
include('includes/navbar.php');

// Check if doctor is logged in
if (!isset($_SESSION['username'])) {
    echo "<div class='alert alert-danger'>Doctor not logged in.</div>";
    include('includes/footer.php');
    exit();
}

$doctor_username = $_SESSION['username'];

// Fetch total attended patients
$query = "SELECT COUNT(*) as total FROM appointments WHERE doctor_username = '$doctor_username' AND status = 'Completed'";
$result = mysqli_query($connection, $query);

$total_attended_patients = 0;
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_attended_patients = $row['total'];
}

$_SESSION['total_patient'] = $total_attended_patients;
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Doctor Dashboard</h1>

    <div class="row">
        <!-- Total Patients Attended Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Total Patients Attended
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo htmlspecialchars($_SESSION['total_patient']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
