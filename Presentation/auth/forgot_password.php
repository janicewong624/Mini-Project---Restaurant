<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$restaurantName = 'DineNest Restaurant';
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? '';
$base = '../../';
$e = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
$error = '';
$sent = ($_GET['sent'] ?? '') === '1';

if (empty($_SESSION['auth_csrf'])) {
    $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null)
        || !hash_equals($_SESSION['auth_csrf'], $_POST['csrf_token'])) {
        $error = 'Your session expired. Please try again.';
    } else {
        require_once __DIR__ . '/../../Business/PasswordResetService.php';
        $result = (new PasswordResetService())->requestReset($email);
        if ($result['success']) {
            header('Location: forgot_password.php?sent=1');
            exit;
        }
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | <?= $e($restaurantName) ?></title>
    <link rel="icon" type="image/png" href="../../Assets/images/favicon.png">
    <link rel="stylesheet" href="../../Assets/css/style.css">
    <link rel="stylesheet" href="../../Assets/css/auth.css">
</head>
<body class="auth-page">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="auth-main">
    <section class="auth-card" aria-labelledby="form-title">
        <header class="auth-brand">
            <img class="auth-brand-logo" src="../../Assets/images/favicon.png" alt="" aria-hidden="true">
            <p class="auth-eyebrow">DineNest Restaurant</p>
        </header>

        <div class="auth-heading">
            <h1 id="form-title">Forgot Password?</h1>
            <p>Enter your account email and we will send a secure reset link.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= $e($error) ?></div>
        <?php elseif ($sent): ?>
            <div class="alert alert-success" role="status">
                If an account uses that email, a password reset link has been sent. The link expires in one hour.
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['auth_csrf']) ?>">
            <div class="form-group">
                <label class="field" for="email">Email Address</label>
                <input class="form-control" type="email" id="email" name="email"
                       value="<?= $e($email) ?>" maxlength="150" autocomplete="email"
                       placeholder="you@example.com" required>
            </div>
            <button type="submit" class="auth-submit">Send Reset Link <span aria-hidden="true">→</span></button>
        </form>

        <p class="auth-switch"><a href="login.php">Back to Sign In</a></p>
    </section>
</main>

<footer class="auth-footer">
    <div class="container">
        <div class="serif"><span class="gold">◆</span> <?= $e($restaurantName) ?> <span class="gold">◆</span></div>
        <p class="copy">© <?= date('Y') ?> · AMCS2194 Mini Project</p>
    </div>
</footer>
</body>
</html>
