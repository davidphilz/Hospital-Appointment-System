<?php
session_start();
include("../include/db.php");

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        :root {
            --primary-color: #0066cc;
            --background: #f0f2f5;
            --card-bg: #ffffff;
            --text-dark: #333;
            --text-light: #666;
            --border-radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background);
            color: var(--text-dark);
        }

        header {
            background-color: var(--primary-color);
            padding: 20px;
            color: white;
            text-align: center;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .dashboard {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .dashboard h2 {
            margin-bottom: 15px;
            font-size: 22px;
            color: var(--text-dark);
        }

        .dashboard p {
            color: var(--text-light);
            font-size: 16px;
            margin-bottom: 30px;
        }

        .buttons a {
            display: inline-block;
            margin: 10px;
            padding: 12px 24px;
            background-color: var(--primary-color);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            transition: background-color 0.3s ease;
        }

        .buttons a:hover {
            background-color: #004999;
        }
    </style>
</head>
<body>

<header>
    Hospital Management Dashboard
</header>

<div class="dashboard">
    <?php if ($role === 'admin'): ?>
        <h2>Welcome, Admin</h2>
        <p><?= $username ?>, you have full administrative access.</p>
    <?php else: ?>
        <h2>Welcome, Doctor</h2>
        <p><?= $username ?>, here is your quick access panel.</p>
    <?php endif; ?>

    <div class="buttons">
        <a href="send_alert.php">Send Alert</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

</body>
</html>

