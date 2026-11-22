<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/ReportService.php';

$service = new ReportService();
$range   = $service->resolveRange($_GET['from'] ?? '', $_GET['to'] ?? '');
$r       = $service->orderSummary($range);
$t       = $r['totals'];

// 長條圖用：每個表格取最大值當 100%
function bar_pct($v, $max): int { return $max > 0 ? (int)round($v / $max * 100) : 0; }
$maxDay   = $r['byDay']    ? max(array_column($r['byDay'], 'orders')) : 0;
$maxItem  = $r['topItems'] ? max(array_column($r['topItems'], 'qty')) : 0;
$maxTable = $r['tables']   ? max(array_column($r['tables'], 'orders')) : 0;

$pageTitle = 'Order Summary Report';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'summary';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Order Summary</h1>
<p class="page-sub">How many orders came in, and what customers ordered.</p>

<?php include __DIR__ . '/../includes/report_filter.php'; ?>

<div class="ob-stats">
    <div class="ob-stat"><small>Total Orders</small><strong><?= (int)$t['totalOrders'] ?></strong></div>
    <div class="ob-stat"><small>Completed (paid)</small><strong><?= (int)$t['completed'] ?></strong></div>
    <div class="ob-stat"><small>Pending</small><strong><?= (int)$t['pending'] ?></strong></div>
    <div class="ob-stat"><small>Cancelled</small><strong><?= (int)$t['cancelled'] ?></strong></div>
    <div class="ob-stat"><small>Dine-in</small><strong><?= (int)$t['dineIn'] ?></strong></div>
    <div class="ob-stat"><small>Takeaway</small><strong><?= (int)$t['takeaway'] ?></strong></div>
    <div class="ob-stat wide"><small>Order Value (excl. cancelled)</small><strong>RM <?= number_format((float)$t['orderValue'], 2) ?></strong></div>
    <div class="ob-stat wide"><small>Average per Order</small><strong>RM <?= number_format((float)$t['averageOrder'], 2) ?></strong></div>
</div>

<div class="ob-cols">
    <div>
        <h2 class="ob-cat">Orders per Day</h2>
        <table class="table-clean">
            <thead><tr><th>Date</th><th>Orders</th><th style="width:34%;"></th><th style="text-align:right;">Value</th></tr></thead>
            <tbody>
            <?php if (!$r['byDay']): ?><tr><td colspan="4">No orders in this period.</td></tr><?php endif; ?>
            <?php foreach ($r['byDay'] as $d): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($d['day'])) ?></td>
                    <td><?= (int)$d['orders'] ?></td>
                    <td><div class="ob-bar"><span style="width:<?= bar_pct($d['orders'], $maxDay) ?>%"></span></div></td>
                    <td style="text-align:right;">RM <?= number_format((float)$d['value'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div>
        <h2 class="ob-cat">Most Ordered Items</h2>
        <table class="table-clean">
            <thead><tr><th>Item</th><th>Qty</th><th style="width:34%;"></th></tr></thead>
            <tbody>
            <?php if (!$r['topItems']): ?><tr><td colspan="3">No items ordered in this period.</td></tr><?php endif; ?>
            <?php foreach ($r['topItems'] as $it): ?>
                <tr>
                    <td><?= $e($it['foodName']) ?></td>
                    <td><?= (int)$it['qty'] ?></td>
                    <td><div class="ob-bar"><span style="width:<?= bar_pct($it['qty'], $maxItem) ?>%"></span></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2 class="ob-cat">Busiest Tables (dine-in)</h2>
        <table class="table-clean">
            <thead><tr><th>Table</th><th>Orders</th><th style="width:34%;"></th></tr></thead>
            <tbody>
            <?php if (!$r['tables']): ?><tr><td colspan="3">No dine-in orders in this period.</td></tr><?php endif; ?>
            <?php foreach ($r['tables'] as $tb): ?>
                <tr>
                    <td>Table <?= $e($tb['tableNumber']) ?></td>
                    <td><?= (int)$tb['orders'] ?></td>
                    <td><div class="ob-bar"><span style="width:<?= bar_pct($tb['orders'], $maxTable) ?>%"></span></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
