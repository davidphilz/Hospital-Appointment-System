<?php
include("../include/db.php");

$room_threshold = 5;
$equipment_threshold = 10;


$stmt = $pdo->prepare("SELECT COUNT(*) FROM hospital_assets WHERE asset_type='room' AND status='available'");
$stmt->execute();
$available_rooms = $stmt->fetchColumn();

if ($available_rooms < $room_threshold) {
    $message = "Warning: Only $available_rooms rooms available.";
    $stmt = $pdo->prepare("INSERT INTO hospital_alerts (message) VALUES (:message)");
    $stmt->execute([':message' => $message]);
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM hospital_assets WHERE asset_type='equipment' AND status='available'");
$stmt->execute();
$available_equipment = $stmt->fetchColumn();

if ($available_equipment < $equipment_threshold) {
    $message = "Warning: Only $available_equipment equipment items available.";
    $stmt = $pdo->prepare("INSERT INTO hospital_alerts (message) VALUES (:message)");
    $stmt->execute([':message' => $message]);
}

echo "Resource check completed.";
?>
