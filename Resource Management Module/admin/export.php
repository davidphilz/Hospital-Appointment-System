<?php
require("../include/db.php");


$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');

header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=report_{$from}_to_{$to}.csv");

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Resource', 'Metric', 'Value']);


$bedQ = $pdo->prepare("SELECT DATE(created_at) AS day, 'Beds' AS resource, 'Occupied' AS metric, SUM(status='occupied') AS value FROM beds_history WHERE created_at BETWEEN ? AND ? GROUP BY day");
$bedQ->execute([$from, "$to 23:59:59"]);
foreach ($bedQ->fetchAll(PDO::FETCH_ASSOC) as $row) {
    fputcsv($out, [$row['day'], $row['resource'], $row['metric'], $row['value']]);
}

$bedQ = $pdo->prepare("SELECT DATE(created_at) AS day, 'Beds' AS resource, 'Total' AS metric, COUNT(*) AS value FROM beds_history WHERE created_at BETWEEN ? AND ? GROUP BY day");
$bedQ->execute([$from, "$to 23:59:59"]);
foreach ($bedQ->fetchAll(PDO::FETCH_ASSOC) as $row) {
    fputcsv($out, [$row['day'], $row['resource'], $row['metric'], $row['value']]);
}

fclose($out);
