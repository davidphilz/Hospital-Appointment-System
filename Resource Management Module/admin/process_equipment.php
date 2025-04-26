<?php
include("../include/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipment_name = $_POST['equipment_name'];
    $status = $_POST['status'];

    try {
        $sql = "INSERT INTO equipment (name, status) VALUES (:equipment_name, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':equipment_name' => $equipment_name,
            ':status' => $status
        ]);
        echo "New equipment added successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
