<!DOCTYPE html>
<html>
<head>
    <title>Patient Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #2a2b38;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .signup-box {
            background: #3e3f4e;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: none;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #ffeba7;
            color: #2a2b38;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ffe27a;
        }
    </style>
</head>
<body>

<div class="signup-box">
    <h2>Sign Up</h2>
    <form action="../process_signup.php" method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email Address" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>
