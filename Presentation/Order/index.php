<?php
// index.php 的「My Orders」/「Orders」連結（presentation/order/）會進到這裡 -> 依角色轉到對應頁面
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_any();

header('Location: ' . ($user['role'] === 'Administrator' ? 'order_list.php' : 'my_orders.php'));
exit;
