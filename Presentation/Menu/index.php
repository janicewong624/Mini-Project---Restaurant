<?php
/**
 * ★ 暫時頁面（給 Member C）：只是讓首頁的 Menu 連結點進去不會是 404 / 空白。
 *   Member C 做 Menu CRUD 時，直接用自己的 index.php 覆蓋這個檔案就可以。
 *   Order 的流程（選餐 -> 下單）在 ../Order/order_menu.php，不受影響。
 */
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_any();
require_once __DIR__ . '/../../Business/OrderService.php';

$items = (new OrderService())->getMenu();
$byCategory = [];
foreach ($items as $it) $byCategory[$it['category'] ?: 'Others'][] = $it;

$pageTitle = 'Menu';
$bodyClass = 'feedback-page order-page list-page';
$navActive = '';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Menu</h1>
<p class="page-sub">Our dishes and drinks.</p>

<?php if ($user['role'] === 'Customer'): ?>
    <p style="margin-bottom:18px;"><a href="../Order/order_menu.php" class="btn btn-gold btn-sm">Order Now</a></p>
<?php else: ?>
    <div class="alert alert-success ob-alert">Menu management (add / edit / delete) will be added here by Member C.</div>
<?php endif; ?>

<?php if (!$items): ?>
    <div class="panel"><p>No menu items yet.</p></div>
<?php endif; ?>

<?php foreach ($byCategory as $cat => $list): ?>
    <h2 class="ob-cat"><?= $e($cat) ?></h2>
    <div class="ob-grid">
        <?php foreach ($list as $it): ?>
            <div class="ob-card">
                <div class="ob-card-body">
                    <h3><?= $e($it['foodName']) ?></h3>
                    <?php if (!empty($it['description'])): ?><p><?= $e($it['description']) ?></p><?php endif; ?>
                    <span class="ob-price">RM <?= number_format((float)$it['price'], 2) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
