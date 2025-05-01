<?php
session_start();
require_once __DIR__ . '/../include/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Sanitize output
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert System</title>
    <style>
        :root {
            --primary: #0066cc;
            --primary-dark: #004999;
            --bg: #f0f2f5;
            --card: #fff;
            --text-dark: #333;
            --text-light: #666;
            --radius: 12px;
            --transition: 0.3s;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        header {
            background: var(--primary);
            padding: 1rem;
            text-align: center;
            color: #fff;
            font-size: 1.5rem;
        }
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            text-align: center;
        }
        .card h2 {
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }
        .card p {
            margin-bottom: 1.5rem;
            color: var(--text-light);
            font-size: 1rem;
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        .actions a {
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: background var(--transition);
        }
        .actions a:hover {
            background: var(--primary-dark);
        }
        footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.85rem;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <header>
    Alert System
    </header>
    <main>
        <div class="card">
            <h2>Welcome</h2>
            <p>Hello <strong><?php echo $username; ?></strong>, you have full administrative privileges.</p>
            <div class="actions">
                <a href="send_alert.php">Send Alert</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </main>
    <footer>
        &copy; <?php echo date('Y'); ?> Our Hospital
    </footer>
</body>
</html>