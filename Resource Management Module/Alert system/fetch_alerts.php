<?php
include '../include/db.php';

$result = $conn->query("SELECT * FROM alerts ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()) {
    echo "<div><b>{$row['sender_role']}</b>: <strong>{$row['title']}</strong><br>{$row['message']}<br><small>{$row['created_at']}</small></div><hr>";
}
?>
