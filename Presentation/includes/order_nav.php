<?php
/**
 * 只剩「開發模式」黃色提示條（ORDER_DEV_MODE = true 時才會出現）。
 * 頁面頂部的小導覽列已經移除。
 *
 * @var Closure     $e     htmlspecialchars 的簡寫（header.php 定義的）
 * @var string|null $role  Customer / Administrator / null（header.php 定義的）
 */
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
