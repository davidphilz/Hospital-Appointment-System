<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $default_users = [
        'admin' => ['username' => 'admin', 'password' => 'admin123'],
    ];

    if (isset($default_users[$username]) && $default_users[$username]['password'] === $password) {
        $_SESSION['user_id'] = 0;
        $_SESSION['username'] = $username;

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username, password, or role.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
        }

        .hint {
            margin-top: 10px;
            font-size: 13px;
            color: #555;
        }

        .hint span {
            display: block;
        }
    </style>
</head>
<body>

<div class="login-form">
    <form method="post">
        <h2>Login</h2>
        <input type="text" name="username" required placeholder="Username">
        <input type="password" name="password" required placeholder="Password">
        <button type="submit">Login</button>

        <?php if (!empty($error)) echo "<p>$error</p>"; ?>
    </form>
</div>

</body>
</html>
