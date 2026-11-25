<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_customer();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$orders  = $service->getByUser($user['userID']);
$flash   = order_flash_get();

// ?open=O0001 或 ?open=O0001,O0002 -> 這些訂單一進來就是展開的
$open = array_filter(array_map('trim', explode(',', (string)($_GET['open'] ?? ''))));

// 每張訂單的明細（只會是自己的訂單）
$details = [];
foreach ($orders as $o) {
    $d = $service->getDetail($o['orderID'], $user['userID']);
    $details[$o['orderID']] = $d ? $d['items'] : [];
}
$hasPending = false;
foreach ($orders as $o) if ($o['orderStatus'] === 'Pending') $hasPending = true;

$pageTitle = 'My Orders';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'myorders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">My Orders</h1>
<p class="page-sub">Your order history. Click an order to see its details.</p>

<?php $modalMsg = $flash; include __DIR__ . '/../includes/flash_modal.php'; ?>

<?php if (!$orders): ?>
    <div class="panel wide ob-empty">
        <p>You have no orders yet.</p>
        <a href="../Menu/index.php" class="btn btn-gold btn-sm">Browse the menu</a>
    </div>
<?php else: ?>
<div class="ob-orders">
    <div class="ob-orders-head" aria-hidden="true">
        <span>Order</span><span>Date</span><span>Type</span><span>Total</span><span>Status</span><span></span>
    </div>

    <?php foreach ($orders as $o):
        $id     = $o['orderID'];
        $status = $o['orderStatus'];
        $items  = $details[$id] ?? [];
    ?>
    <details class="ob-order" id="order-<?= $e($id) ?>" data-id="<?= $e($id) ?>" <?= in_array($id, $open, true) ? 'open' : '' ?>>
        <summary>
            <strong class="ob-order-id"><?= $e($id) ?></strong>
            <span class="ob-order-date"><?= date('d/m/Y H:i', strtotime($o['orderDate'])) ?></span>
            <span class="ob-order-type"><?= $e($o['orderType']) ?><?= $o['orderType'] === 'Dine-in' ? ' · Table ' . $e($o['tableNumber'] ?? '-') : '' ?></span>
            <span class="ob-order-total">RM <?= number_format((float)$o['totalAmount'], 2) ?></span>
            <span><span class="badge badge-<?= strtolower($e($status)) ?>"><?= $e($status) ?></span></span>
            <span class="ob-chevron" aria-hidden="true"></span>
        </summary>

        <div class="ob-order-body">
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

            <div class="ob-info">
                <div><small>Order Type</small><strong><?= $e($o['orderType']) ?></strong></div>
                <div><small>Table</small><strong><?= $o['orderType'] === 'Dine-in' ? $e($o['tableNumber'] ?? '-') : '-' ?></strong></div>
                <div><small>Date</small><strong><?= date('d/m/Y H:i', strtotime($o['orderDate'])) ?></strong></div>
                <div><small>Payment</small>
                    <?php if ($o['paymentStatus'] && $o['paymentStatus'] !== 'Pending'): ?>
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

            <?php if ($status === 'Pending'): ?>
                <p class="ob-note">Please pay at the counter when you are done. This page refreshes by itself.</p>
            <?php endif; ?>

            <?php if ($status === 'Pending' || $status === 'Completed'): ?>
            <div class="ob-actions">
                <?php if ($status === 'Completed' && $o['paymentStatus'] === 'Completed' && $o['paymentID']): ?>
                    <a href="../Receipt/receipt_view.php?id=<?= urlencode($o['paymentID']) ?>" class="btn btn-secondary btn-sm">View Receipt</a>
                <?php endif; ?>
                <?php if ($status === 'Pending'): ?>
                    <a href="order_cancel.php?id=<?= urlencode($id) ?>&amp;back=<?= urlencode('my_orders.php?open=' . $id) ?>" class="btn btn-danger btn-sm">Cancel Order</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </details>
    <?php endforeach; ?>
</div>

<script>
// 剛下單 / 從別頁指定要展開的那一張，捲動到畫面中間
const target = document.querySelector('.ob-order[open]');
if (target) target.scrollIntoView({ block: 'center' });

<?php if ($hasPending): ?>
// 有 Pending 的訂單時，每 20 秒自動更新（保留目前展開的訂單）
setTimeout(() => {
    const ids = [...document.querySelectorAll('.ob-order[open]')].map(d => d.dataset.id);
    location.href = 'my_orders.php' + (ids.length ? '?open=' + encodeURIComponent(ids.join(',')) : '');
}, 20000);
<?php endif; ?>
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
