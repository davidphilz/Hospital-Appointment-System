<?php
session_start();
include("../include/header.php");
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hospital_appointment_system;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$id = (int)($_GET['id'] ?? 0);
$m  = $pdo->prepare("SELECT * FROM messages WHERE id=?");
$m->execute([$id]);
$msg = $m->fetch(PDO::FETCH_ASSOC);
if (!$msg) {
    exit('Message not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = trim($_POST['reply'] ?? '');
    if ($reply) {
        $stmt = $pdo->prepare("
            UPDATE messages
            SET admin_reply = :reply,
                replied_at = NOW(),
                is_read = 1
            WHERE id = :id
        ");
        $stmt->execute([
            ':reply' => $reply,
            ':id' => $id
        ]);
        header("Location: view_message.php?id=$id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reply to <?= htmlspecialchars($msg['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-papbA+5LNTMKLaZaU8A9e9YbxXG1q3p2WxYTxm9tytUAI7JPI4fKq7F+RbzfHRnJvOvNUmcbZnA0VWjFvYFJtw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
        }
        .sidebar a {
            color: #ccc;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: white;
        }
        .main-content {
            padding: 30px;
        }
        .card-header {
            background: #007bff;
            color: white;
        }
        textarea.form-control {
            resize: vertical;
        }
    }
    .layout-container {
      display: flex;
      min-height: 100vh;
    }
    .sidebar {
      background-color: #343a40;
      color: #fff;
      padding: 20px;
    }
    .sidebar a {
      display: block;
      color: #ddd;
      padding: 10px;
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #495057;
      color: #fff;
    }
    .main-content {
      flex: 1;
      padding: 30px;
    }
    table {
      background-color: white;
    }
    .bg-success {
      background: linear-gradient(45deg, #28a745, #218838);
    }
    .bg-info {
      background: linear-gradient(45deg, #17a2b8, #117a8b);
    }
    .bg-warning {
      background: linear-gradient(45deg, #ffc107, #e0a800);
    }
    .bg-danger {
      background: linear-gradient(45deg, #dc3545, #c82333);
    }
    .bg-primary {
      background: linear-gradient(45deg, #007bff, #0069d9);
    }
    .bg-secondary {
      background: linear-gradient(45deg, #6c757d, #5a6268);
    }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar">
                <?php include("sidenav.php"); ?>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto main-content">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4><i class="fas fa-envelope"></i> Message from <?= htmlspecialchars($msg['name']) ?></h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Date:</strong> <?= htmlspecialchars($msg['created_at']) ?></p>
                        <p class="border rounded p-3 bg-light"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>

                        <hr>
                        <h5><i class="fas fa-reply"></i> Admin Reply</h5>

                        <?php if ($msg['admin_reply']): ?>
                            <div class="alert alert-success">
                                <strong>Replied on:</strong> <?= htmlspecialchars($msg['replied_at']) ?>
                            </div>
                            <div class="border rounded p-3 bg-white">
                                <?= nl2br(htmlspecialchars($msg['admin_reply'])) ?>
                            </div>
                        <?php else: ?>
                            <form method="post" class="mt-3">
                                <div class="mb-3">
                                    <label for="reply" class="form-label">Your Reply:</label>
                                    <textarea name="reply" id="reply" class="form-control" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button>
                            </form>
                        <?php endif; ?>

                        <a href="admin_panel.php" class="btn btn-link mt-4"><i class="fas fa-arrow-left"></i>Back to Inbox</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
