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
    'invalid_csrf' => 'Your session expired. Please try again.',
    'empty_fields' => 'Please fill in all required fields.',
    'invalid_name' => 'Please enter a name with at least 2 characters.',
    'invalid_email' => 'Please enter a valid email address.',
    'invalid_phone' => 'Please enter a valid phone number.',
    'weak_password' => 'Password must be at least 8 characters and contain letters and numbers.',
    'password_mismatch' => 'Your passwords do not match.',
    'terms_required' => 'Please accept the Terms & Conditions and Privacy Policy.',
    'email_exists' => 'This email is already registered. Please sign in or use another email.',
];
$errorCode = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';
$error = $errors[$errorCode] ?? '';

if (empty($_SESSION['auth_csrf'])) {
    $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null)
        || !hash_equals($_SESSION['auth_csrf'], $_POST['csrf_token'])) {
        header('Location: register.php?error=invalid_csrf');
        exit;
    }

    require_once __DIR__ . '/../../Business/AuthService.php';
    $service = new AuthService();
    $result = $service->register(
        $postValue('full_name'),
        $postValue('email'),
        $postValue('phone'),
        $postValue('password'),
        $postValue('confirm_password'),
        $postValue('terms') === 'accepted'
    );

    if (!$result['success']) {
        header('Location: register.php?error=' . rawurlencode($result['error']));
        exit;
    }

    header('Location: login.php?registered=success');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account | <?= $e($restaurantName) ?></title>
    <link rel="icon" type="image/png" href="../../Assets/images/favicon.png">
    <link rel="stylesheet" href="../../Assets/css/style.css">
    <link rel="stylesheet" href="../../Assets/css/auth.css">
</head>
<body class="auth-page">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="auth-main">
    <section class="auth-card auth-card-register" aria-labelledby="form-title">
        <header class="auth-brand">
            <img class="auth-brand-logo" src="../../Assets/images/favicon.png" alt="" aria-hidden="true">
            <p class="auth-eyebrow">DineNest Restaurant</p>
        </header>

        <div class="auth-heading">
            <h1 id="form-title">Create Your Account</h1>
            <p>Join us and enjoy a delightful dining experience.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= $e($error) ?></div>
        <?php endif; ?>

        <form action="register.php" method="post" id="register-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['auth_csrf']) ?>">

            <div class="form-group">
                <label class="field" for="full-name">Full Name</label>
                <input class="form-control" type="text" id="full-name" name="full_name"
                       placeholder="Enter your full name" autocomplete="name" minlength="2" required>
                <small class="auth-field-error" data-error-for="full-name"></small>
            </div>

            <div class="form-group">
                <label class="field" for="email">Email Address</label>
                <input class="form-control" type="email" id="email" name="email"
                       placeholder="you@example.com" autocomplete="email" required>
                <small class="auth-field-error" data-error-for="email"></small>
            </div>

            <div class="form-group">
                <label class="field" for="phone">Phone Number</label>
                <input class="form-control" type="tel" id="phone" name="phone"
                       placeholder="e.g. 0123456789" autocomplete="tel" required>
                <small class="auth-field-error" data-error-for="phone"></small>
            </div>

            <div class="form-group">
                <label class="field" for="password">Password</label>
                <div class="auth-password">
                    <input class="form-control" type="password" id="password" name="password"
                           placeholder="Create a password" autocomplete="new-password" required>
                    <button class="auth-toggle" type="button" data-toggle-password="password"
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <small class="auth-hint">At least 8 characters, including letters and numbers.</small>
                <small class="auth-field-error" data-error-for="password"></small>
            </div>

            <div class="form-group">
                <label class="field" for="confirm-password">Confirm Password</label>
                <div class="auth-password">
                    <input class="form-control" type="password" id="confirm-password" name="confirm_password"
                           placeholder="Re-enter your password" autocomplete="new-password" required>
                    <button class="auth-toggle" type="button" data-toggle-password="confirm-password"
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <small class="auth-field-error" data-error-for="confirm-password"></small>
            </div>

            <div class="auth-terms">
                <input type="checkbox" id="terms" name="terms" value="accepted" required>
                <label for="terms">I agree to the</label>
                <a href="terms.php" target="_blank" rel="noopener">Terms &amp; Conditions</a>
                <span>and</span>
                <a href="privacy.php" target="_blank" rel="noopener">Privacy Policy</a>
                <span>.</span>
            </div>
            <small class="auth-field-error" data-error-for="terms"></small>

            <button type="submit" class="auth-submit">Create Account <span aria-hidden="true">→</span></button>
        </form>

        <p class="auth-switch">Already have an account? <a href="login.php">Sign In</a></p>
        <a class="auth-home-link" href="../../index.php">Back to home</a>
    </section>
</main>

<?php include __DIR__ . '/../includes/site_footer.php'; ?>
<script src="../../Assets/js/auth.js"></script>
</body>
</html>
