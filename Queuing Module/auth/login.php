<!DOCTYPE html>
<html>
<head>
    <title>Patient Login</title>
    <style>
        body {
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
        }
        .login-container {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px #aaa;
        }
        .login-container h2 {
            text-align: center;
            color: #2a2b38;
        }
        input[type="email"],
        input[type="password"],
        button {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #2a2b38;
            color: #ffeba7;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #1f202c;
        }
        .message {
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Patient Login</h2>
        <form action="../process_login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p class="message">
            <?php
                if (isset($_GET['error'])) {
                    echo htmlspecialchars($_GET['error']);
                }
            ?>
        </p>
    </div>
</body>
</html>
