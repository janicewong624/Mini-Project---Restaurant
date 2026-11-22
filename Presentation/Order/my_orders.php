<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_customer();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$orders  = $service->getByUser($user['userID']);
$flash   = order_flash_get();

$pageTitle = 'My Orders';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'myorders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">My Orders</h1>
<p class="page-sub">Click an order to see its status.</p>

<?php if ($flash): ?><div class="alert alert-success ob-alert"><?= $e($flash) ?></div><?php endif; ?>

<p style="margin-bottom:14px;"><a href="order_menu.php" class="btn btn-gold btn-sm">+ New Order</a></p>

<table class="table-clean">
    <thead>
        <tr><th>Order</th><th>Date</th><th>Type</th><th>Table</th><th>Total</th><th>Status</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php if (!$orders): ?>
        <tr><td colspan="7">You have no orders yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td><?= $e($o['orderID']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($o['orderDate'])) ?></td>
            <td><?= $e($o['orderType']) ?></td>
            <td><?= $o['orderType'] === 'Dine-in' ? $e($o['tableNumber'] ?? '-') : '-' ?></td>
            <td>RM <?= number_format((float)$o['totalAmount'], 2) ?></td>
            <td><span class="badge badge-<?= strtolower($e($o['orderStatus'])) ?>"><?= $e($o['orderStatus']) ?></span></td>
            <td class="actions">
                <a class="link-edit" href="order_view.php?id=<?= urlencode($o['orderID']) ?>">View status</a>
                <?php if ($o['orderStatus'] === 'Pending'): ?>
                    <a class="link-delete" href="order_cancel.php?id=<?= urlencode($o['orderID']) ?>">Cancel</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
