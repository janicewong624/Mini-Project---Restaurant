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
    'empty_fields' => 'Please enter both your email address and password.',
    'invalid_email' => 'Please enter a valid email address.',
    'email_not_found' => 'No account was found with this email address.',
    'wrong_password' => 'The password is incorrect. Please try again.',
    'database_unavailable' => 'Database is unavailable. Please start MySQL and try again.',
];
$errorCode = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';
$error = $errors[$errorCode] ?? '';
$loginNotice = is_string($_SESSION['login_notice'] ?? null) ? $_SESSION['login_notice'] : '';
unset($_SESSION['login_notice']);
$registered = ($_GET['registered'] ?? '') === 'success';
$loginEmail = '';
$emailFieldError = '';
$passwordFieldError = '';

if (empty($_SESSION['auth_csrf'])) {
    $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null)
        || !hash_equals($_SESSION['auth_csrf'], $_POST['csrf_token'])) {
        $error = $errors['invalid_csrf'];
    } else {
        try {
            require_once __DIR__ . '/../../Business/AuthService.php';
            $service = new AuthService();
            $result = $service->authenticateWithResult(
                $postValue('email'),
                $postValue('password')
            );
        } catch (PDOException $exception) {
            $result = ['success' => false, 'error' => 'database_unavailable'];
        } catch (Throwable $exception) {
            $result = ['success' => false, 'error' => 'database_unavailable'];
        }

        if (!$result['success']) {
            $errorCode = $result['error'];
            $error = $errors[$errorCode] ?? $errors['invalid_credentials'];
            $loginEmail = trim($postValue('email'));
            if ($errorCode === 'invalid_email') {
                $emailFieldError = $error;
            } elseif ($errorCode === 'email_not_found') {
                $emailFieldError = $error;
            } elseif ($errorCode === 'wrong_password') {
                $passwordFieldError = $error;
            } elseif ($errorCode === 'empty_fields') {
                if ($postValue('email') === '') {
                    $emailFieldError = 'Please enter your email address.';
                }
                if ($postValue('password') === '') {
                    $passwordFieldError = 'Please enter your password.';
                }
            }
        } else {
            $user = $result['user'];
            session_regenerate_id(true);
            unset($_SESSION['_dev']);
                        $_SESSION['userID'] = $user['userID'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // 訪客下單時被請來登入：回到訂單摘要（購物車還在 session 裡）
            $to = $_SESSION['after_login'] ?? '';
            unset($_SESSION['after_login']);
            if ($user['role'] === 'Customer' && $to === 'Presentation/Order/order_summary.php') {
                header('Location: ../../' . $to);
            } elseif ($user['role'] === 'Administrator') {
                header('Location: ../report/');          // 管理員不看首頁
            } else {
                header('Location: ../../index.php');
            }
            exit;
        }
    }
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

        <?php if ($loginNotice !== ''): ?>
            <div class="alert alert-success" role="status"><?= $e($loginNotice) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= $e($error) ?></div>
        <?php elseif ($registered): ?>
            <div class="alert alert-success" role="status">Your account is ready. Please sign in.</div>
        <?php elseif (($_GET['reset'] ?? '') === 'success'): ?>
            <div class="alert alert-success" role="status">Your password has been reset. Please sign in.</div>
        <?php endif; ?>

        <form action="" method="post" id="login-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['auth_csrf']) ?>">

            <div class="form-group">
                <label class="field" for="email">Email Address</label>
                <input class="form-control<?= $emailFieldError !== '' ? ' auth-invalid' : '' ?>" type="email" id="email" name="email"
                       placeholder="you@example.com" autocomplete="email"
                       value="<?= $e($loginEmail) ?>" required>
                <small class="auth-field-error" data-error-for="email"><?= $e($emailFieldError) ?></small>
            </div>

            <div class="form-group">
                <label class="field" for="password">Password</label>
                <div class="auth-password">
                    <input class="form-control<?= $passwordFieldError !== '' ? ' auth-invalid' : '' ?>" type="password" id="password" name="password"
                           placeholder="Enter your password" autocomplete="current-password" required>
                    <button class="auth-toggle" type="button" data-toggle-password="password"
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <small class="auth-field-error" data-error-for="password"><?= $e($passwordFieldError) ?></small>
            </div>

            <p class="auth-forgot"><a href="forgot_password.php">Forgot password?</a></p>
            <button type="submit" class="auth-submit">Sign In <span aria-hidden="true">→</span></button>
        </form>

        <p class="auth-switch">Don't have an account? <a href="register.php">Create Account</a></p>
        <a class="auth-home-link" href="../../index.php">Back to home</a>
    </section>
</main>

<?php include __DIR__ . '/../includes/site_footer.php'; ?>
<script src="../../Assets/js/auth.js"></script>

<script>
const ch = new BroadcastChannel('dinenest-auth');
ch.onmessage = (ev) => {
  if (ev.data === 'password-reset-done') {
    window.focus();
    window.location.href = 'login.php?reset=success';   // 回到登入頁，顯示「密碼已重設」
  }
};
</script>
</body>
</html>
