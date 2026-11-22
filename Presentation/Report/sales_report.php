<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/ReportService.php';

$service = new ReportService();
$range   = $service->resolveRange($_GET['from'] ?? '', $_GET['to'] ?? '');
$r       = $service->sales($range, $_GET['group'] ?? '');
$t       = $r['totals'];
$group   = $r['group'];

function bar_pct($v, $max): int { return $max > 0 ? (int)round($v / $max * 100) : 0; }
$maxPeriod = $r['byPeriod']    ? max(array_column($r['byPeriod'], 'revenue')) : 0;
$maxMethod = $r['byMethod']    ? max(array_column($r['byMethod'], 'revenue')) : 0;
$maxType   = $r['byType']      ? max(array_column($r['byType'], 'revenue')) : 0;
$maxCat    = $r['byCategory']  ? max(array_column($r['byCategory'], 'revenue')) : 0;
$maxBest   = $r['bestSellers'] ? max(array_column($r['bestSellers'], 'revenue')) : 0;

$showGroup = true;
$pageTitle = 'Sales Report';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'sales';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Sales Report</h1>
<p class="page-sub">Revenue from payments that were received (Completed).</p>

<?php include __DIR__ . '/../includes/report_filter.php'; ?>

<div class="ob-stats">
    <div class="ob-stat wide"><small>Total Sales</small><strong>RM <?= number_format((float)$t['revenue'], 2) ?></strong></div>
    <div class="ob-stat"><small>Paid Orders</small><strong><?= (int)$t['payments'] ?></strong></div>
    <div class="ob-stat wide"><small>Average per Order</small><strong>RM <?= number_format((float)$t['average'], 2) ?></strong></div>
</div>

<h2 class="ob-cat">Sales by <?= $group === 'month' ? 'Month' : 'Day' ?></h2>
<table class="table-clean">
    <thead><tr><th><?= $group === 'month' ? 'Month' : 'Date' ?></th><th>Paid Orders</th><th style="width:40%;"></th><th style="text-align:right;">Sales</th></tr></thead>
    <tbody>
    <?php if (!$r['byPeriod']): ?><tr><td colspan="4">No sales in this period.</td></tr><?php endif; ?>
    <?php foreach ($r['byPeriod'] as $p): ?>
        <tr>
            <td><?= $group === 'month' ? $e(date('M Y', strtotime($p['period'] . '-01'))) : date('d/m/Y', strtotime($p['period'])) ?></td>
            <td><?= (int)$p['payments'] ?></td>
            <td><div class="ob-bar"><span style="width:<?= bar_pct($p['revenue'], $maxPeriod) ?>%"></span></div></td>
            <td style="text-align:right;">RM <?= number_format((float)$p['revenue'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="ob-cols">
    <div>
        <h2 class="ob-cat">By Payment Method</h2>
        <table class="table-clean">
            <thead><tr><th>Method</th><th>Orders</th><th style="width:30%;"></th><th style="text-align:right;">Sales</th></tr></thead>
            <tbody>
            <?php if (!$r['byMethod']): ?><tr><td colspan="4">No data.</td></tr><?php endif; ?>
            <?php foreach ($r['byMethod'] as $m): ?>
                <tr>
                    <td><?= $e($m['method']) ?></td>
                    <td><?= (int)$m['payments'] ?></td>
                    <td><div class="ob-bar"><span style="width:<?= bar_pct($m['revenue'], $maxMethod) ?>%"></span></div></td>
                    <td style="text-align:right;">RM <?= number_format((float)$m['revenue'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2 class="ob-cat">By Order Type</h2>
        <table class="table-clean">
            <thead><tr><th>Type</th><th>Orders</th><th style="width:30%;"></th><th style="text-align:right;">Sales</th></tr></thead>
            <tbody>
            <?php if (!$r['byType']): ?><tr><td colspan="4">No data.</td></tr><?php endif; ?>
            <?php foreach ($r['byType'] as $ty): ?>
                <tr>
                    <td><?= $e($ty['type']) ?></td>
                    <td><?= (int)$ty['payments'] ?></td>
                    <td><div class="ob-bar"><span style="width:<?= bar_pct($ty['revenue'], $maxType) ?>%"></span></div></td>
                    <td style="text-align:right;">RM <?= number_format((float)$ty['revenue'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2 class="ob-cat">By Category</h2>
        <table class="table-clean">
            <thead><tr><th>Category</th><th>Qty</th><th style="width:30%;"></th><th style="text-align:right;">Sales</th></tr></thead>
            <tbody>
            <?php if (!$r['byCategory']): ?><tr><td colspan="4">No data.</td></tr><?php endif; ?>
            <?php foreach ($r['byCategory'] as $c): ?>
                <tr>
                    <td><?= $e($c['category']) ?></td>
                    <td><?= (int)$c['qty'] ?></td>
                    <td><div class="ob-bar"><span style="width:<?= bar_pct($c['revenue'], $maxCat) ?>%"></span></div></td>
                    <td style="text-align:right;">RM <?= number_format((float)$c['revenue'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div>
        <h2 class="ob-cat">Best Sellers</h2>
        <table class="table-clean">
            <thead><tr><th>Item</th><th>Qty</th><th style="width:26%;"></th><th style="text-align:right;">Sales</th></tr></thead>
            <tbody>
            <?php if (!$r['bestSellers']): ?><tr><td colspan="4">No data.</td></tr><?php endif; ?>
            <?php foreach ($r['bestSellers'] as $b): ?>
                <tr>
                    <td><?= $e($b['foodName']) ?></td>
                    <td><?= (int)$b['qty'] ?></td>
                    <td><div class="ob-bar"><span style="width:<?= bar_pct($b['revenue'], $maxBest) ?>%"></span></div></td>
                    <td style="text-align:right;">RM <?= number_format((float)$b['revenue'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
