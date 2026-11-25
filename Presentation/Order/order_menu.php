<?php
/**
 * 顧客選餐已經搬到 Menu 頁面。這個檔案只負責接收 Menu 頁面送來的數量：
 *   POST -> 檢查數量、存進購物車 -> 去 Order Summary
 *   GET  -> 直接回 Menu
 */
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_customer();
require_once __DIR__ . '/../../Business/OrderService.php';

$menuUrl = '../Menu/index.php';

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
