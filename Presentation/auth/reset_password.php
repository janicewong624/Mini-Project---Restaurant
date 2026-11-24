<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$restaurantName = 'DineNest Restaurant';
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? '';
$base = '../../';
$e = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$token = isset($_GET['token']) && is_string($_GET['token'])
    ? $_GET['token']
    : (isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '');
$password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
$confirmPassword = isset($_POST['confirm_password']) && is_string($_POST['confirm_password'])
    ? $_POST['confirm_password']
    : '';
$error = '';
$service = null;

if (empty($_SESSION['auth_csrf'])) {
    $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
}

if (preg_match('/^[a-f0-9]{64}$/i', $token)) {
    require_once __DIR__ . '/../../Business/PasswordResetService.php';
    $service = new PasswordResetService();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null)
        || !hash_equals($_SESSION['auth_csrf'], $_POST['csrf_token'])) {
        $error = 'Your session expired. Please try again.';
    } elseif ($service === null) {
        $error = 'This reset link is invalid or has expired.';
    } else {
        $result = $service->resetPassword($token, $password, $confirmPassword);
        if ($result['success']) {
            header('Location: login.php?reset=success');
            exit;
        }
        $error = $result['error'];
    }
}

$tokenValid = $service !== null && $service->isTokenValid($token);
if (!$tokenValid && $error === '') {
    $error = 'This reset link is invalid or has expired. Request a new one to continue.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | <?= $e($restaurantName) ?></title>
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
            <h1 id="form-title">Reset Password</h1>
            <p>Choose a new password for your account.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= $e($error) ?></div>
        <?php endif; ?>

        <?php if ($tokenValid): ?>
            <form action="reset_password.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['auth_csrf']) ?>">
                <input type="hidden" name="token" value="<?= $e($token) ?>">
                <div class="form-group">
                    <label class="field" for="password">New Password</label>
                    <div class="auth-password">
                        <input class="form-control" type="password" id="password" name="password"
                               minlength="8" autocomplete="new-password" required>
                        <button class="auth-toggle" type="button" data-toggle-password="password"
                                aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                    <small class="auth-hint">At least 8 characters, including letters and numbers.</small>
                </div>
                <div class="form-group">
                    <label class="field" for="confirm-password">Confirm New Password</label>
                    <input class="form-control" type="password" id="confirm-password" name="confirm_password"
                           minlength="8" autocomplete="new-password" required>
                </div>
                <button type="submit" class="auth-submit">Save New Password <span aria-hidden="true">→</span></button>
            </form>
        <?php else: ?>
            <a class="auth-submit terms-back" href="forgot_password.php">Request a New Reset Link</a>
        <?php endif; ?>

        <p class="auth-switch"><a href="login.php">Back to Sign In</a></p>
    </section>
</main>

<footer class="auth-footer">
    <div class="container">
        <div class="serif"><span class="gold">◆</span> <?= $e($restaurantName) ?> <span class="gold">◆</span></div>
        <p class="copy">© <?= date('Y') ?> · AMCS2194 Mini Project</p>
    </div>
</footer>
<script src="../../Assets/js/auth.js"></script>
</body>
</html>
