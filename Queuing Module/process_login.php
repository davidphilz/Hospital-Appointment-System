<?php
// Make PHPSESSID available to every path on your site:
session_set_cookie_params([
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require __DIR__ . '/includes/db.php';  // adjust if your includes/ is elsewhere

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: auth/login.php");
    exit();
}

// Sanitize input
$email    = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

// Fetch user
$sql    = "SELECT id, name, email, password FROM patients WHERE email = '$email' LIMIT 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {
        // ——— Set session vars ———
        $_SESSION['patient_id']   = $user['id'];
        $_SESSION['patient_name'] = $user['name'];
        $_SESSION['email'] = $user['email']; // Add this line to set the email in the session

        // ——— Redirect to the dashboard ———
        header("Location: /Hospital-Appointment-System/Queuing%20Module/dashboard/index.php");
        exit();
    } else {
        header("Location: auth/login.php?error=Invalid%20password");
        exit();
    }
} else {
    header("Location: auth/login.php?error=User%20not%20found");
    exit();
}
?>
