<?php
/**
 * Member B 頁面頂部的小導覽列（放在 header.php 之後 include）。
 * 需要：$base（header.php 產生）、$role、$navActive（目前頁面代號，用來標示 active）
 *
 * 之後如果 header.php 的主導覽列補上這些連結，這個檔案可以直接不用。
 */
$navActive = $navActive ?? '';
$P = $base . 'Presentation/';

if ($role === 'Administrator') {
    $links = [
        'orders'  => ['Orders',          $P . 'Order/order_list.php'],
        'neworder'=> ['+ New Order',     $P . 'Order/order_add.php'],
        'tables'  => ['Tables',          $P . 'Table/table_list.php'],
        'summary' => ['Order Summary',   $P . 'Report/order_summary_report.php'],
        'sales'   => ['Sales Report',    $P . 'Report/sales_report.php'],
    ];
} else {
    $links = [
        'menu'    => ['Menu & New Order', $P . 'Order/order_menu.php'],
        'myorders'=> ['My Orders',        $P . 'Order/my_orders.php'],
    ];
}
?>
<?php if (!empty($_SESSION['_dev'])): ?>
    <div class="ob-dev no-print">
        DEV MODE: login is not ready, you are viewing as <strong><?= $e($role) ?></strong>.
        <?php
            $other = $role === 'Administrator' ? 'customer' : 'admin';
            $q = http_build_query(array_merge($_GET, ['as' => $other]));
        ?>
        <a href="?<?= $e($q) ?>">Switch to <?= $other ?></a>
        <span>(set ORDER_DEV_MODE to false in includes/order_guard.php once login works)</span>
    </div>
<?php endif; ?>

<nav class="ob-nav no-print">
    <?php foreach ($links as $key => [$label, $href]): ?>
        <a href="<?= $e($href) ?>" class="<?= $navActive === $key ? 'active' : '' ?>"><?= $e($label) ?></a>
    <?php endforeach; ?>
</nav>
