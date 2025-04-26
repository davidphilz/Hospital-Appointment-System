<?php
session_start();
require_once("../include/configure.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notice_id'])) {
    $notice_id = intval($_POST['notice_id']);

    $stmt = $pdo->prepare("UPDATE notices SET status = 'resolved' WHERE id = ?");
    if ($stmt->execute([$notice_id])) {
        header("Location: notice.php");
        exit;
    } else {
        die("Error updating notice status.");
    }
} else {
    header("Location: notice.php");
    exit;
}
?>
