<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #2a2b38;
            --accent-color: #ffeba7;
            --background-color: #f9f9f9;
            --box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 12px;
            --border-radius: 10px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 30px 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 360px;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        input[type="email"],
        input[type="password"],
        button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        button {
            background-color: var(--primary-color);
            color: var(--accent-color);
            border: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #1f202c;
        }

        .message {
            text-align: center;
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        .signup-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 400px) {
            .login-container {
                padding: 20px;
            }
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

        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </div>
    </div>

</body>
</html>
