<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$restaurantName = 'Your Restaurant';          
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? '';
$e = fn($s) => htmlspecialchars((string)$s);

$base = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/') . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($pageTitle ?? $restaurantName) ?></title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../assets/css/style.css') ?>">
</head>
<body class="inner-page <?= $e($bodyClass ?? '') ?>">

<nav class="navbar">
    <div class="container">
        <a href="<?= $base ?>index.php" class="logo">
            <span class="logo-mark">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M7 3v8a2 2 0 0 0 2 2v8M11 3v8M7 7h4M17 3c-2 2-3 5-3 8h3v10"/>
                </svg>
            </span>
            <span class="logo-text"><?= $e($restaurantName) ?></span>
        </a>
        <div class="nav-links">
            <a href="<?= $base ?>index.php">Home</a>
            <?php if ($role === 'Customer'): ?>
                <a href="<?= $base ?>presentation/feedback/feedback_add.php">Feedback</a>
                <span class="nav-user">Hi, <?= $e($name) ?></span>
                <a href="<?= $base ?>presentation/auth/logout.php" class="btn-nav">Logout</a>
            <?php elseif ($role === 'Administrator'): ?>
                <a href="<?= $base ?>presentation/payment/payment_list.php">Payments</a>
                <a href="<?= $base ?>presentation/feedback/feedback_list.php">Feedback</a>
                <a href="<?= $base ?>presentation/auth/logout.php" class="btn-nav">Logout</a>
            <?php else: ?>
                <a href="<?= $base ?>presentation/auth/login.php" class="btn-nav">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="page-main">
    <div class="container">