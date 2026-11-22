<?php
/**
 * ★ 暫時頁面（給 Member C）：只是讓 admin 導覽列的 Customers 連結點進去有東西看（唯讀列表）。
 *   Member C 做 Customer CRUD 時，直接用自己的 index.php 覆蓋這個檔案就可以。
 */
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/OrderService.php';

$customers = (new OrderService())->getCustomers();

$pageTitle = 'Customers';
$bodyClass = 'feedback-page order-page list-page';
$navActive = '';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Customers</h1>
<p class="page-sub">Registered customers.</p>

<div class="alert alert-success ob-alert">Customer management (add / edit / delete) will be added here by Member C.</div>

<table class="table-clean">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Contact</th></tr></thead>
    <tbody>
    <?php if (!$customers): ?><tr><td colspan="4">No customers yet.</td></tr><?php endif; ?>
    <?php foreach ($customers as $c): ?>
        <tr>
            <td><?= $e($c['userID']) ?></td>
            <td><?= $e($c['name']) ?></td>
            <td><?= $e($c['email']) ?></td>
            <td><?= $e($c['contactNumber'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
