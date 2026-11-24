<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$restaurantName = 'DineNest Restaurant';
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? '';
$base = '../../';
$e = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms &amp; Conditions | <?= $e($restaurantName) ?></title>
    <link rel="icon" type="image/png" href="../../Assets/images/favicon.png">
    <link rel="stylesheet" href="../../Assets/css/style.css">
    <link rel="stylesheet" href="../../Assets/css/auth.css">
</head>
<body class="auth-page">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="auth-main terms-main">
    <article class="auth-card terms-card">
        <header class="auth-brand">
            <img class="auth-brand-logo" src="../../Assets/images/favicon.png" alt="" aria-hidden="true">
            <p class="auth-eyebrow">DineNest Restaurant</p>
        </header>

        <div class="auth-heading">
            <h1>Terms &amp; Conditions</h1>
            <p>Please review these terms before creating an account.</p>
        </div>

        <div class="policy-content">
            <p>By creating an account and using the DineNest Restaurant system, you agree to the following terms.</p>

            <h2>1. Account Registration</h2>
            <ul>
                <li>Provide accurate and complete information when registering.</li>
                <li>Each email address may be registered only once.</li>
                <li>You are responsible for keeping your account credentials secure.</li>
            </ul>

            <h2>2. Account Security</h2>
            <ul>
                <li>Keep your login information confidential and do not share your password.</li>
                <li>Notify the restaurant administrator if you notice suspicious activity on your account.</li>
            </ul>

            <h2>3. System Use</h2>
            <ul>
                <li>Use the system for its intended restaurant-related purposes.</li>
                <li>Do not damage, disrupt, or misuse the system.</li>
                <li>Provide truthful information when using system features.</li>
            </ul>

            <h2>4. Account Suspension</h2>
            <p>DineNest Restaurant may suspend or terminate accounts that violate these terms or misuse the system.</p>

            <h2>5. Changes to These Terms</h2>
            <p>These terms may be updated when necessary. Please review this page periodically.</p>

            <h2>6. Contact</h2>
            <p>For questions about these terms, contact the restaurant administrator.</p>
        </div>

        <a class="auth-submit terms-back" href="register.php">Back to registration</a>
    </article>
</main>

<footer class="auth-footer">
    <div class="container">
        <div class="serif"><span class="gold">◆</span> <?= $e($restaurantName) ?> <span class="gold">◆</span></div>
        <p class="copy">© <?= date('Y') ?> · AMCS2194 Mini Project</p>
    </div>
</footer>
</body>
</html>
