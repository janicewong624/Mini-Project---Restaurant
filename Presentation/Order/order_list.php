<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$fStatus = $_GET['status'] ?? '';
$fType   = $_GET['type'] ?? '';
$fFrom   = $_GET['from'] ?? '';
$fTo     = $_GET['to'] ?? '';
$orders  = $service->search(['status' => $fStatus, 'type' => $fType, 'from' => $fFrom, 'to' => $fTo]);
$flash   = order_flash_get();

$pageTitle = 'Orders';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'orders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Orders</h1>
<p class="page-sub">All customer orders.</p>

<?php if ($flash): ?><div class="alert alert-success ob-alert"><?= $e($flash) ?></div><?php endif; ?>

<form method="get" class="ob-filter no-print">
    <select name="status" class="form-control">
        <option value="">All status</option>
        <?php foreach (OrderService::STATUSES as $s): ?>
            <option value="<?= $e($s) ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= $e($s) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="type" class="form-control">
        <option value="">Dine-in &amp; Takeaway</option>
        <?php foreach (OrderService::TYPES as $t): ?>
            <option value="<?= $e($t) ?>" <?= $fType === $t ? 'selected' : '' ?>><?= $e($t) ?></option>
        <?php endforeach; ?>
    </select>
    <label>From <input type="date" name="from" class="form-control" value="<?= $e($fFrom) ?>"></label>
    <label>To <input type="date" name="to" class="form-control" value="<?= $e($fTo) ?>"></label>
    <button type="submit" class="btn btn-gold btn-sm">Filter</button>
    <a href="order_list.php" class="btn btn-secondary btn-sm">Reset</a>
    <a href="order_add.php" class="btn btn-gold btn-sm" style="margin-left:auto;">+ New Order</a>
</form>

<table class="table-clean">
    <thead>
        <tr>
            <th>ID</th><th>Customer</th><th>Type</th><th>Table</th><th>Date</th>
            <th>Total</th><th>Payment</th><th>Status</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$orders): ?>
        <tr><td colspan="9">No orders found.</td></tr>
    <?php endif; ?>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td><?= $e($o['orderID']) ?></td>
            <td><?= $e($o['customerName']) ?></td>
            <td><?= $e($o['orderType']) ?></td>
            <td><?= $o['orderType'] === 'Dine-in' ? $e($o['tableNumber'] ?? '-') : '-' ?></td>
            <td><?= date('d/m/Y H:i', strtotime($o['orderDate'])) ?></td>
            <td>RM <?= number_format((float)$o['totalAmount'], 2) ?></td>
            <td>
                <?php if ($o['paymentStatus']): ?>
                    <span class="badge badge-<?= strtolower($e($o['paymentStatus'])) ?>"><?= $e($o['paymentStatus']) ?></span>
                <?php else: ?>-<?php endif; ?>
            </td>
            <td><span class="badge badge-<?= strtolower($e($o['orderStatus'])) ?>"><?= $e($o['orderStatus']) ?></span></td>
            <td class="actions">
                <a class="link-edit" href="order_view.php?id=<?= urlencode($o['orderID']) ?>">View</a>
                <?php if ($o['orderStatus'] === 'Pending'): ?>
                    <a class="link-edit" href="order_edit.php?id=<?= urlencode($o['orderID']) ?>">Edit</a>
                    <a class="link-delete" href="order_cancel.php?id=<?= urlencode($o['orderID']) ?>">Cancel</a>
                <?php endif; ?>
                <?php if ($o['orderStatus'] !== 'Completed'): ?>
                    <a class="link-delete" href="order_delete.php?id=<?= urlencode($o['orderID']) ?>">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
