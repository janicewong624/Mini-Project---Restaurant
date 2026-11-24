<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (($_SESSION['role'] ?? '') !== 'Administrator') {
    header('Location: ../auth/login.php');
    exit;
}

function customer_admin_value(array $source, string $key): string {
    return isset($source[$key]) && is_string($source[$key]) ? $source[$key] : '';
}

function customer_admin_csrf_token(): string {
    if (empty($_SESSION['customer_csrf'])) {
        $_SESSION['customer_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['customer_csrf'];
}

function customer_admin_csrf_valid(): bool {
    $token = $_POST['csrf_token'] ?? null;
    return is_string($token)
        && isset($_SESSION['customer_csrf'])
        && hash_equals($_SESSION['customer_csrf'], $token);
}
