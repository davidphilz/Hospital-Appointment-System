<?php
// improved_appointment.php
// Handle form submission
$feedback = '';
$feedbackClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['problem_description'] ?? '');
    $urgency = $_POST['urgency'] ?? '';
    
    if (empty($description) || empty($urgency)) {
        $feedback = 'Error: Please fill in all required fields.';
        $feedbackClass = 'error';
    } else {
        // TODO: Integrate appointment saving logic here

        // Set feedback message for successful booking
        $feedback = 'Your appointment has been successfully booked!';
        $feedbackClass = 'success';

        // Redirect to the dashboard after successful form submission
        header('Location: index.php'); // Adjust the URL to your actual dashboard page
        exit(); // Make sure no further code is executed
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <style>
        :root {
            --font-family: 'Segoe UI', sans-serif;
            --color-bg: #eef1f5;
            --color-card: #ffffff;
            --color-primary: #2a2b38;
            --color-accent: #ffeba7;
            --color-error: #dc3545;
            --color-success: #28a745;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: var(--font-family);
            background-color: var(--color-bg);
            color: var(--color-primary);
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 5rem auto;
            padding: 2rem;
            background-color: var(--color-card);
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }
        .feedback {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-left: 4px solid;
            border-radius: 0.5rem;
        }
        .feedback.success {
            background-color: #e0ffe0;
            border-color: var(--color-success);
            color: var(--color-primary);
        }
        .feedback.error {
            background-color: #ffe0e0;
            border-color: var(--color-error);
            color: var(--color-primary);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.5rem;
            font-size: 1rem;
        }
        button.btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-accent);
            background-color: var(--color-primary);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button.btn:hover {
            background-color: #1f202c;
        }
    </style>
</head>
<body>
    <main class="container" role="main">
        <h1>Book an Appointment</h1>
        <?php if ($feedback): ?>
            <div role="alert" class="feedback <?= htmlspecialchars($feedbackClass, ENT_QUOTES); ?>">
                <?= htmlspecialchars($feedback, ENT_QUOTES); ?>
            </div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="form-group">
                <label for="problem_description">Describe Your Problem</label>
                <textarea id="problem_description" name="problem_description" rows="5" required><?= htmlspecialchars($_POST['problem_description'] ?? '', ENT_QUOTES); ?></textarea>
            </div>
            <div class="form-group">
                <label for="urgency">Urgency Level</label>
                <select id="urgency" name="urgency" required>
                    <option value="" disabled <?= empty($_POST['urgency']) ? 'selected' : ''; ?>>Select urgency level</option>
                    <?php 
                    $levels = ['Emergency', 'High', 'Normal'];
                    foreach ($levels as $level): ?>
                        <option value="<?= $level; ?>" <?= (($_POST['urgency'] ?? '') === $level) ? 'selected' : ''; ?>><?= $level; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">Book Appointment</button>
        </form>
    </main>
</body>
</html>
