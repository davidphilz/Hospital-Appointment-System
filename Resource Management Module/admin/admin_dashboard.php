<?php
session_start();
include("../include/connection.php");

// Fetch patient details and payment info
$query = "SELECT patients.name, patients.id AS patient_id, patient_treatment.treatment_type, patient_treatment.amount_due, patient_treatment.payment_status
          FROM patients
          INNER JOIN patient_treatment ON patients.id = patient_treatment.patient_id";
$result = mysqli_query($connect, $query);

$patients = [];
while ($row = mysqli_fetch_assoc($result)) {
    $patients[] = $row;
}

// Store patients' data in session
$_SESSION['patients'] = $patients;

// Redirect to the admin page
header("Location: view_patients.php");
exit();
?>
