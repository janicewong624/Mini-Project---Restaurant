<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['role']) && !empty($_SESSION['userID'])) {
    header('Location: ../../index.php');
    exit;
}

$restaurantName = 'DineNest Restaurant';
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? '';
$base = '../../';
$e = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$postValue = static fn(string $key): string => isset($_POST[$key]) && is_string($_POST[$key])
    ? $_POST[$key]
    : '';
$errors = [
    'invalid_credentials' => 'The email or password is incorrect.',
    'invalid_csrf' => 'Your session expired. Please try again.',
];
$errorCode = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';
$error = $errors[$errorCode] ?? '';
$registered = ($_GET['registered'] ?? '') === 'success';

if (empty($_SESSION['auth_csrf'])) {
    $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null)
        || !hash_equals($_SESSION['auth_csrf'], $_POST['csrf_token'])) {
        header('Location: login.php?error=invalid_csrf');
        exit;
    }

    require_once __DIR__ . '/../../Business/AuthService.php';
    $service = new AuthService();
    $user = $service->authenticate(
        $postValue('email'),
        $postValue('password')
    );

    if (!$user) {
        header('Location: login.php?error=invalid_credentials');
        exit;
    }

    session_regenerate_id(true);
    unset($_SESSION['_dev']);
    $_SESSION['userID'] = $user['userID'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?= $e($restaurantName) ?></title>
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
            <h1 id="form-title">Welcome Back</h1>
            <p>Sign in to continue your dining experience.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= $e($error) ?></div>
        <?php elseif ($registered): ?>
            <div class="alert alert-success" role="status">Your account is ready. Please sign in.</div>
        <?php elseif (($_GET['reset'] ?? '') === 'success'): ?>
            <div class="alert alert-success" role="status">Your password has been reset. Please sign in.</div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['auth_csrf']) ?>">

            <div class="form-group">
                <label class="field" for="email">Email Address</label>
                <input class="form-control" type="email" id="email" name="email"
                       placeholder="you@example.com" autocomplete="email" required>
            </div>

            <div class="form-group">
                <label class="field" for="password">Password</label>
                <div class="auth-password">
                    <input class="form-control" type="password" id="password" name="password"
                           placeholder="Enter your password" autocomplete="current-password" required>
                    <button class="auth-toggle" type="button" data-toggle-password="password"
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
            </div>

            <p class="auth-forgot"><a href="forgot_password.php">Forgot password?</a></p>
            <button type="submit" class="auth-submit">Sign In <span aria-hidden="true">→</span></button>
        </form>

        <p class="auth-switch">Don't have an account? <a href="register.php">Create Account</a></p>
        <a class="auth-home-link" href="../../index.php">Back to home</a>
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
