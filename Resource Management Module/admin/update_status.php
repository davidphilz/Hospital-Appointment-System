<?php
require_once("../include/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $staff_id = $_POST["staff_id"];
    $status = $_POST["status"];

    if ($status === "Active" || $status === "Inactive") {
        $stmt = $pdo->prepare("UPDATE staff SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $staff_id])) {
            echo json_encode(["success" => true, "message" => "Status updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update status."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid status value."]);
    }
}
?>
