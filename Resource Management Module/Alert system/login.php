<?php
ob_start();
session_start();


$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));

    $default_users = [
        'admin' => 'admin123',
    ];

    if (isset($default_users[$username]) && $default_users[$username] === $password) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = 0;
        $_SESSION['username']  = $username;

        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alert System Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('../img/background1.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-form {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 300px;
            text-align: center;
        }
        .login-form h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .login-form input,
        .login-form button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .login-form button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border: none;
        }
        .login-form button:hover {
            background-color: #45a049;
        }
        .login-form p {
            color: red;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="login-form">
    <form method="post" action="">
        <h2>Alert System Login</h2>
        <input type="text" name="username" required placeholder="Username">
        <input type="password" name="password" required placeholder="Password">
        <button type="submit">Login</button>
        <?php if ($error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
