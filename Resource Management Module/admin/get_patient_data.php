<?php
session_start();
include("../include/connection.php");// Include your DB connection script

// Fetch patient details and payment info
$query = "SELECT patients.name, patients.patient_id, patient_treatment.treatment_type, patient_treatment.amount_due, patient_treatment.payment_status
          FROM patients
          INNER JOIN patient_treatment ON patients.id = patient_treatment.patient_id";
$result = mysqli_query($conn, $query);

$patients = [];
while ($row = mysqli_fetch_assoc($result)) {
    $patients[] = $row;
}

// Return the data as JSON
echo json_encode($patients);
?>
