<?php
session_start();

// Database configuration
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "hospital_appointment_system";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title   = trim($_POST["title"]);
    $message = trim($_POST["message"]);

    $stmt = $conn->prepare("INSERT INTO alert (title, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $message);

    if ($stmt->execute()) {
        // Redirect to dashboard.php after successful alert submission
        header("Location: dashboard.php");
        exit;
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
            --hover-color: #004999;
            --background-color: #f0f2f5;
            --card-color: #ffffff;
            --text-color: #333;
            --border-radius: 12px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: var(--card-color);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: var(--border-radius);
            font-size: 16px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            border: none;
            border-radius: var(--border-radius);
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: var(--hover-color);
        }
    </style>
</head>
<body>

<header>Send Alert</header>

<main class="container">
    <h2>Alert Form</h2>
    <form method="post">
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="message" placeholder="Message" rows="5" required></textarea>
        <button type="submit">Send Alert</button>
    </form>
</main>

</body>
</html>
