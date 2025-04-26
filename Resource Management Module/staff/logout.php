<?php
session_start();
require_once("../include/config.php");

if (isset($_SESSION['staff_id'])) {
    $stmt = $pdo->prepare("UPDATE staff SET status = 'Inactive' WHERE id = ?");
    $stmt->execute([$_SESSION['staff_id']]);
}
if (isset($_SESSION['staff_id'])) {
    $stmt = $pdo->prepare("UPDATE staff SET status = 'Inactive', last_active = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['staff_id']]);
}


session_destroy();
header("Location: ../index.php");
exit;
?>
