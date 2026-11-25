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
$here    = basename($_SERVER['SCRIPT_NAME']) . ($_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');  

$pageTitle = 'Orders';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'orders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<div class="ob-pagehead">
    <h1 class="page-title">Orders</h1>
    <p class="page-sub">All customer orders.</p>
    <?php include __DIR__ . '/../includes/report_links.php'; ?>
</div>

<?php $modalMsg = $flash; include __DIR__ . '/../includes/flash_modal.php'; ?>

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

<table class="table-clean ob-orders-table">
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
            <td>
                <?php if ($o['orderStatus'] === 'Cancelled'): ?>
                    <span class="badge badge-cancelled" title="A cancelled order cannot be changed.">Cancelled</span>
                <?php else: ?>
                    <form method="post" action="order_status.php" class="ob-status-form">
                        <input type="hidden" name="id" value="<?= $e($o['orderID']) ?>">
                        <input type="hidden" name="back" value="<?= $e($here) ?>">
                        <select name="status" class="ob-status-select badge-<?= strtolower($e($o['orderStatus'])) ?>"
                                data-current="<?= $e($o['orderStatus']) ?>" data-order="<?= $e($o['orderID']) ?>"
                                <?= $o['paymentStatus'] === 'Completed' ? 'disabled title="Paid. Cancel the payment first to change the status."' : '' ?>
                                aria-label="Status of order <?= $e($o['orderID']) ?>">
                            <?php foreach (OrderService::STATUSES as $st): ?>
                                <option value="<?= $e($st) ?>" <?= $o['orderStatus'] === $st ? 'selected' : '' ?>><?= $e($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
            </td>
            <td>
                <div class="ob-row-actions">
                    <a class="ob-btn ob-btn-view" href="order_view.php?id=<?= urlencode($o['orderID']) ?>&amp;back=<?= urlencode($here) ?>">View</a>
                    <?php if ($o['orderStatus'] === 'Pending'): ?>
                        <a class="ob-btn ob-btn-edit" href="order_edit.php?id=<?= urlencode($o['orderID']) ?>&amp;back=<?= urlencode($here) ?>">Edit</a>
                    <?php else: ?>
                        <span class="ob-btn is-disabled" title="Only Pending orders can be edited.">Edit</span>
                    <?php endif; ?>
                    <?php if ($o['orderStatus'] !== 'Completed'): ?>
                        <a class="ob-btn ob-btn-delete" href="order_delete.php?id=<?= urlencode($o['orderID']) ?>&amp;back=<?= urlencode($here) ?>">Delete</a>
                    <?php else: ?>
                        <span class="ob-btn is-disabled" title="A paid (Completed) order cannot be deleted.">Delete</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
// 直接在列表改狀態：選好就送出（取消是不可逆的，先確認）
document.querySelectorAll('.ob-status-select').forEach(sel => {
    sel.addEventListener('change', () => {
        const from = sel.dataset.current, to = sel.value, id = sel.dataset.order;
        let msg = '';
        if (to === 'Cancelled') msg = 'Cancel order ' + id + '? This cannot be undone.';
        if (msg && !confirm(msg)) { sel.value = from; return; }
        sel.form.submit();
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
