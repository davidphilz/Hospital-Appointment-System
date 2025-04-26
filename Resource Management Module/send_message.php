<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hospital_appointment_system;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$message) {
    die('Please fill out all fields with valid data.');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (name, email, message) 
        VALUES (:name, :email, :message)
    ");
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':message' => $message
    ]);

    // Send notification to admin
    $to      = 'admin@hospital.com';
    $subject = 'New inquiry from ' . $name;
    $body    = "You have a new message:\n\n"
             . "Name: $name\n"
             . "Email: $email\n"
             . "Message:\n$message\n";
    mail($to, $subject, $body);

    // Redirect after success
    header('Location: index.php?sent=1');
    exit;
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
