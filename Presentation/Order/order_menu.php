<?php
/**
 * 接收 Menu 頁面送來的數量：
 *   POST -> 檢查數量、存進購物車 -> 去 Order Summary（沒登入先去登入，購物車保留）
 *   GET  -> 直接回 Menu
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Business/OrderService.php';

$menuUrl = '../Menu/index.php';

// ---------- 訪客：存好購物車，再叫他登入 ----------
if (empty($_SESSION['userID'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $service = new OrderService();
        [$cart, $errors] = $service->normalizeQuantities($_POST['qty'] ?? []);
        if ($errors || !$cart) {
            $_SESSION['menu_error'] = $errors ? implode(' ', $errors) : 'Please choose at least one item.';
            header("Location: $menuUrl");
            exit;
        }
        $_SESSION['cart'] = $cart;                        // 登入後還在
    }
    $_SESSION['after_login']  = 'Presentation/Order/order_summary.php';
    $_SESSION['login_notice'] = 'Please log in to place your order. Your selection has been saved.';
    header('Location: ../auth/login.php');
    exit;
}

// ---------- 已登入顧客（原本的流程） ----------
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_customer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $menuUrl");
    exit;
}

$service = new OrderService();
[$cart, $errors] = $service->normalizeQuantities($_POST['qty'] ?? []);

if (!$errors && !$cart) {
    $errors[] = "Please choose at least one item.";
}
if ($errors) {
    order_flash_set(implode(' ', $errors), 'error');
    header("Location: $menuUrl");
    exit;
}

$_SESSION['cart'] = $cart;
header("Location: order_summary.php");
exit;