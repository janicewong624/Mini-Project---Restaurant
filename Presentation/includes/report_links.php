<?php
/**
 * 標題右邊（filter 框外面）的報表按鈕：Order Summary / Sales Report。
 * 目前所在的報表不顯示它自己的按鈕（在 Sales Report 就只剩 Order Summary，反之亦然）。
 * 在 Orders 頁面（$navActive = 'orders'）兩個都會顯示。
 *
 * @var string      $base       網站根路徑（header.php 算出來的）
 * @var Closure     $e          htmlspecialchars 的簡寫（header.php 定義的）
 * @var string|null $navActive  目前頁面代號：'summary' 或 'sales'（可以不設）
 */
$navActive = $navActive ?? '';
$RP = $base . 'Presentation/Report/';
?>
<div class="ob-quicklinks no-print">

    <?php if ($navActive !== 'summary'): ?>
    <a href="<?= $e($RP . 'order_summary_report.php') ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/>
        </svg>
        Order Summary
    </a>
    <?php endif; ?>

    <?php if ($navActive !== 'sales'): ?>
    <a href="<?= $e($RP . 'sales_report.php') ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>
        </svg>
        Sales Report
    </a>
    <?php endif; ?>

</div>