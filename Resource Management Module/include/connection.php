<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "resource";

$connect = mysqli_connect($host, $user, $password, $dbname);
if (!$connect) {
    die("Database connection error: " . mysqli_connect_error());
}
?>