<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Income</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file if needed -->
</head>
<body>
    <h1>Total Income</h1>
    <?php
    // Database connection
    $conn = new mysqli("localhost", "root", "", "hospital_db");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to calculate total income
    $sql = "SELECT SUM(amount) AS total_income FROM payments";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<p>Total Income: $" . number_format($row['total_income'], 2) . "</p>";
    } else {
        echo "<p>No income data available.</p>";
    }

    $conn->close();
    ?>
</body>
</html>