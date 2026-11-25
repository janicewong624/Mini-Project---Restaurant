<?php
/**
 * admin 在訂單列表直接改狀態（POST）。
 * 訂單狀態和付款是分開的：送完餐就可以標 Completed，不用先收款（收款在 Payments 頁面另外做）。
 * 規則在 OrderService::changeStatus。
 */
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/OrderService.php';

$back = order_back_url('order_list.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . $back); exit; }

$id = $_POST['id'] ?? '';
$to = $_POST['status'] ?? '';

$result = (new OrderService())->changeStatus($id, $to);
if ($result['success']) {
    order_flash_set("Order $id is now $to.");
} else {
    order_flash_set(implode(' ', $result['errors']), 'error');
}
header('Location: ' . $back);
exit;
