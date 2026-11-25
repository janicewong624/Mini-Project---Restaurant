<?php
/**
 * 共用導覽列：header.php 和 index.php 都 include 這個檔案。要改導覽列只改這裡。
 *
 * @var string      $base            網站根路徑（header.php 算出來；index.php 是 ''）
 * @var string|null $role            Customer / Administrator / null
 * @var string      $name            使用者名稱
 * @var Closure     $e               htmlspecialchars 的簡寫
 * @var string      $restaurantName  餐廳名稱
 */

// 目前在哪一頁：用網址判斷，所以每個頁面都不用另外設定。
$script  = strtolower(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
$curPage = basename($script, '.php');
$curDir  = preg_match('#/presentation/([^/]+)/#', $script, $m) ? $m[1] : 'home';
$navCurrent = $curDir;
if ($curDir === 'receipt') $navCurrent = 'payment';
if ($curDir === 'auth')  $navCurrent = $curPage === 'register' ? 'register' : '';
if ($curDir === 'order' && $role === 'Customer' && in_array($curPage, ['order_menu', 'order_summary', 'order_type'], true)) {
    $navCurrent = 'menu';          // 顧客下單流程屬於 Menu
}
$navA = fn(string $key): string => $navCurrent === $key ? ' class="active" aria-current="page"' : '';
?>
<nav class="navbar">
    <div class="container">
        <a href="<?= $base ?>index.php" class="logo">
            <span class="logo-mark">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M7 3v8a2 2 0 0 0 2 2v8M11 3v8M7 7h4M17 3c-2 2-3 5-3 8h3v10"/>
                </svg>
            </span>
            <span class="logo-text"><?= $e($restaurantName) ?></span>
        </a>

        <div class="nav-links">
            <a href="<?= $base ?>index.php"<?= $navA('home') ?>>Home</a>

            <?php if ($role === 'Customer'): ?>
                <a href="<?= $base ?>presentation/menu/"<?= $navA('menu') ?>>Menu</a>
                <a href="<?= $base ?>presentation/order/"<?= $navA('order') ?>>My Orders</a>
                <a href="<?= $base ?>presentation/feedback/feedback_add.php"<?= $navA('feedback') ?>>Feedback</a>
                <span class="nav-user">Hi, <?= $e($name) ?></span>
                <a href="<?= $base ?>presentation/auth/logout.php" class="btn-nav">Logout</a>

            <?php elseif ($role === 'Administrator'): ?>
                <a href="<?= $base ?>presentation/customer/"<?= $navA('customer') ?>>Customers</a>
                <a href="<?= $base ?>presentation/menu/">Menu</a>
                <a href="<?= $base ?>presentation/table/"<?= $navA('table') ?>>Tables</a>
                <a href="<?= $base ?>presentation/order/"<?= $navA('order') ?>>Orders</a>
                <a href="<?= $base ?>presentation/payment/payment_list.php"<?= $navA('payment') ?>>Payments</a>
                <a href="<?= $base ?>presentation/report/"<?= $navA('report') ?>>Reports</a>
                <a href="<?= $base ?>presentation/feedback/feedback_list.php"<?= $navA('feedback') ?>>Feedback</a>
                <a href="<?= $base ?>presentation/auth/logout.php" class="btn-nav">Logout</a>

            <?php else: ?>
                <a href="<?= $base ?>presentation/auth/register.php"<?= $navA('register') ?>>Register</a>
                <a href="<?= $base ?>presentation/auth/login.php" class="btn-nav">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
