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
    <title>Privacy Policy | <?= $e($restaurantName) ?></title>
    <link rel="icon" type="image/png" href="../../Assets/images/favicon.png">
    <link rel="stylesheet" href="../../Assets/css/style.css">
    <link rel="stylesheet" href="../../Assets/css/auth.css">
</head>
<body class="auth-page">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="auth-main">
    <article class="auth-card privacy-card">
        <header class="auth-brand">
            <img class="auth-brand-logo" src="../../Assets/images/favicon.png" alt="" aria-hidden="true">
            <p class="auth-eyebrow">DineNest Restaurant</p>
        </header>

        <div class="auth-heading">
            <h1>Privacy Policy</h1>
            <p>How DineNest handles information when you use this system.</p>
        </div>

        <div class="policy-content">
            <p>This policy explains what information the DineNest Restaurant system handles and why.</p>

            <h2>Information We Collect</h2>
            <ul>
                <li><strong>Account details:</strong> your name, email address, phone number, and a password hash when you register. Your original password is not stored as readable text.</li>
                <li><strong>Orders:</strong> items ordered, quantities, order type, table selection where applicable, order time, total, and order status.</li>
                <li><strong>Payments:</strong> payment amount, method, and status recorded for an order. The restaurant system does not ask you to enter a card number or banking password.</li>
                <li><strong>Feedback:</strong> the rating and comments you choose to submit, associated with your account.</li>
                <li><strong>Session data:</strong> a session identifier used to keep you signed in and associate actions with your account.</li>
                <li><strong>Password reset data:</strong> a hashed, single-use reset token and its expiry time when you request a password reset.</li>
            </ul>

            <h2>How We Use Information</h2>
            <ul>
                <li>To create and maintain your account and authenticate sign-ins.</li>
                <li>To prepare and manage orders, tables, and payment records.</li>
                <li>To review feedback and operate, support, and protect the restaurant system.</li>
            </ul>

            <h2>Who Can Access Information</h2>
            <p>Authorized restaurant administrators may access account and transaction information when needed to manage customers, orders, payments, reports, or feedback. Information may also be disclosed when required by applicable law.</p>

            <h2>Storage and Security</h2>
            <p>Information is stored in the restaurant system's database. Passwords are hashed before storage. No online system can guarantee absolute security, so please keep your login credentials private and contact the restaurant administrator if you suspect unauthorized access.</p>

            <h2>Retention and Your Choices</h2>
            <p>Account, order, payment, and feedback records may be kept for restaurant operations and record-keeping. To request access to or correction or deletion of your personal information, contact the restaurant administrator. Some transaction records may need to be retained for operational or legal reasons.</p>

            <h2>Changes to This Policy</h2>
            <p>This policy may be updated when the system's data practices change. The latest version will be available on this page.</p>

            <h2>Contact</h2>
            <p>For privacy questions or requests, contact the restaurant administrator.</p>
        </div>

        <a class="auth-submit terms-back" href="register.php">Back to registration</a>
    </article>
</main>

<?php include __DIR__ . '/../includes/site_footer.php'; ?>
</body>
</html>
