<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hospital_appointment_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $message = trim($_POST["message"]);
    $role = 'admin';

    $stmt = $conn->prepare("INSERT INTO alerts (sender_role, title, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $role, $title, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Alert sent successfully!'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to send alert.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Alert</title>
    <style>
        :root {
            --primary-color: #0066cc;
            --background: #f0f2f5;
            --card-bg: #ffffff;
            --text-dark: #333;
            --text-light: #666;
            --border-radius: 12px;
            --button-hover-color: #004999;
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
        }

        .alert-form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .alert-form-container h2 {
            margin-bottom: 20px;
            color: var(--text-dark);
            font-size: 22px;
            text-align: center;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: var(--border-radius);
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: var(--button-hover-color);
        }
    </style>
</head>
<body>

<header>
    Send Alert
</header>

<div class="alert-form-container">
    <h2>Alert Form</h2>
    <form method="post">
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="message" placeholder="Message" required></textarea>
        <button type="submit">Send Alert</button>
    </form>
</div>

</body>
</html>
