<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();

$pageTitle = 'Reports';
$bodyClass = 'feedback-page order-page';
$navActive = '';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Reports</h1>
<p class="page-sub">See how the restaurant is doing.</p>

<div class="ob-cols" style="max-width:760px;width:100%;">
    <a class="ob-card ob-linkcard" href="order_summary_report.php">
        <div class="ob-card-body">
            <h3>Order Summary</h3>
            <p>Orders per day, status and type breakdown, most ordered items and busiest tables.</p>
        </div>
    </a>
    <a class="ob-card ob-linkcard" href="sales_report.php">
        <div class="ob-card-body">
            <h3>Sales Report</h3>
            <p>Revenue by day or month, payment method, order type, category and best sellers.</p>
        </div>
    </a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
