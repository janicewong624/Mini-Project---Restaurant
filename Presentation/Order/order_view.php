<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_any();
require_once __DIR__ . '/../../Business/OrderService.php';

$isAdmin = $user['role'] === 'Administrator';
$service = new OrderService();
$id      = $_GET['id'] ?? '';
// 顧客只能看自己的訂單；admin 可以看全部
$detail  = $service->getDetail($id, $isAdmin ? null : $user['userID']);
$flash   = order_flash_get();

if ($detail === null) {
    $back = $isAdmin ? 'order_list.php' : 'my_orders.php';
    exit("Order not found. <a href='$back'>Back</a>");
}
$o     = $detail['order'];
$items = $detail['items'];
$status = $o['orderStatus'];
$backUrl = $isAdmin ? 'order_list.php' : 'my_orders.php';

$pageTitle = 'Order ' . $o['orderID'];
$bodyClass = 'feedback-page order-page list-page';
$navActive = $isAdmin ? 'orders' : 'myorders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Order <?= $e($o['orderID']) ?></h1>
<p class="page-sub"><?= $isAdmin ? 'Order details.' : 'Track the status of your order.' ?></p>

<?php if ($flash): ?><div class="alert alert-success ob-alert"><?= $e($flash) ?></div><?php endif; ?>

<?php if ($status === 'Cancelled'): ?>
    <div class="ob-track cancelled"><div class="ob-track-step"><span class="dot"></span><em>Cancelled</em></div></div>
<?php else: ?>
    <div class="ob-track">
        <div class="ob-track-step done"><span class="dot"></span><em>Order placed</em></div>
        <div class="ob-track-line <?= $status === 'Completed' ? 'done' : '' ?>"></div>
        <div class="ob-track-step <?= $status === 'Completed' ? 'done' : 'active' ?>"><span class="dot"></span><em>Pay at the counter</em></div>
        <div class="ob-track-line <?= $status === 'Completed' ? 'done' : '' ?>"></div>
        <div class="ob-track-step <?= $status === 'Completed' ? 'done' : '' ?>"><span class="dot"></span><em>Completed</em></div>
    </div>
<?php endif; ?>

<div class="panel wide">
    <div class="ob-info">
        <div><small>Status</small><span class="badge badge-<?= strtolower($e($status)) ?>"><?= $e($status) ?></span></div>
        <div><small>Order Type</small><strong><?= $e($o['orderType']) ?></strong></div>
        <div><small>Table</small><strong><?= $o['orderType'] === 'Dine-in' ? $e($o['tableNumber'] ?? '-') : '-' ?></strong></div>
        <div><small>Date</small><strong><?= date('d/m/Y H:i', strtotime($o['orderDate'])) ?></strong></div>
        <?php if ($isAdmin): ?>
            <div><small>Customer</small><strong><?= $e($o['customerName']) ?></strong></div>
        <?php endif; ?>
        <div><small>Payment</small>
            <?php if ($o['paymentStatus']): ?>
                <span class="badge badge-<?= strtolower($e($o['paymentStatus'])) ?>"><?= $e($o['paymentStatus']) ?></span>
                <?php if ($o['paymentMethod']): ?><small><?= $e($o['paymentMethod']) ?></small><?php endif; ?>
            <?php else: ?>
                <strong>Not paid yet</strong>
            <?php endif; ?>
        </div>
    </div>

    <table class="table-clean ob-flat">
        <thead>
            <tr><th>Item</th><th>Unit Price</th><th>Qty</th><th style="text-align:right;">Subtotal</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?= $e($it['foodName']) ?></td>
                <td>RM <?= number_format((float)$it['unitPrice'], 2) ?></td>
                <td>× <?= (int)$it['quantity'] ?></td>
                <td style="text-align:right;">RM <?= number_format((float)$it['subtotal'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="ob-total-row">
                <td colspan="3" style="text-align:right;">Total</td>
                <td style="text-align:right;">RM <?= number_format((float)$o['totalAmount'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!$isAdmin && $status === 'Pending'): ?>
        <p class="ob-note">Please pay at the counter when you are done. This page refreshes by itself.</p>
    <?php endif; ?>

    <div class="ob-actions">
        <a href="<?= $backUrl ?>" class="btn btn-secondary btn-sm">Back</a>

        <?php if ($o['paymentStatus'] === 'Completed'): ?>
            <a href="../Receipt/receipt_view.php?id=<?= urlencode($o['paymentID']) ?>" class="btn btn-secondary btn-sm">View Receipt</a>
        <?php endif; ?>

        <?php if ($isAdmin && $status === 'Pending'): ?>
            <a href="../Payment/payment_add.php" class="btn btn-secondary btn-sm">Record Payment</a>
            <a href="order_edit.php?id=<?= urlencode($o['orderID']) ?>" class="btn btn-gold btn-sm">Edit</a>
        <?php endif; ?>

        <?php if ($status === 'Pending'): ?>
            <a href="order_cancel.php?id=<?= urlencode($o['orderID']) ?>" class="btn btn-danger btn-sm">Cancel Order</a>
        <?php endif; ?>

        <?php if ($isAdmin && $status !== 'Completed'): ?>
            <a href="order_delete.php?id=<?= urlencode($o['orderID']) ?>" class="btn btn-danger btn-sm">Delete</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!$isAdmin && $status === 'Pending'): ?>
<script>setTimeout(() => location.reload(), 20000);</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
