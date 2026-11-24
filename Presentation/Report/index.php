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

<div class="ob-reportcards">
    <a class="ob-reportcard" href="order_summary_report.php">
        <span class="ob-reportcard-icon">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/>
            </svg>
        </span>
        <h3>Order Summary</h3>
        <p>Orders per day, status and type breakdown, most ordered items and busiest tables.</p>
        <span class="ob-reportcard-btn">View Report <span aria-hidden="true">&rarr;</span></span>
    </a>
    <a class="ob-reportcard" href="sales_report.php">
        <span class="ob-reportcard-icon">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>
            </svg>
        </span>
        <h3>Sales Report</h3>
        <p>Revenue by day or month, payment method, order type, category and best sellers.</p>
        <span class="ob-reportcard-btn">View Report <span aria-hidden="true">&rarr;</span></span>
    </a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
