<?php
/**
 * filter 框外面、標題右邊上下排列的兩個連結：Order Summary / Sales Report。
 *
 * @var string      $base       網站根路徑（header.php 算出來的）
 * @var Closure     $e          htmlspecialchars 的簡寫（header.php 定義的）
 * @var string|null $navActive  目前頁面代號：'summary' 或 'sales'（可以不設）
 */
$navActive = $navActive ?? '';
$navActive = $navActive ?? '';
$RP = $base . 'Presentation/Report/';
?>
<div class="ob-quicklinks no-print">
    <a href="<?= $e($RP . 'order_summary_report.php') ?>" class="<?= $navActive === 'summary' ? 'active' : '' ?>">Order Summary</a>
    <a href="<?= $e($RP . 'sales_report.php') ?>" class="<?= $navActive === 'sales' ? 'active' : '' ?>">Sales Report</a>
</div>
