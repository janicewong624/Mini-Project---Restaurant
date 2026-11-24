<?php
/**
 * ★ 暫時頁面（給 Member C）：只是讓導覽列的 Menu 連結點進去有東西看（唯讀列表）。
 *   Member C 做 Menu CRUD 時，直接用自己的 index.php 覆蓋這個檔案就可以。
 *   這個檔案不依賴 Order 的任何東西。
 */
require_once __DIR__ . '/../../Data/Database.php';

$pdo   = Database::connect();
$items = $pdo->query(
    "SELECT menuItemID, foodName, description, price, category FROM menuitem ORDER BY category, foodName"
)->fetchAll();

$pageTitle = 'Menu';
$bodyClass = 'feedback-page list-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Menu</h1>
<p class="page-sub">Our dishes and drinks.</p>

<?php if ($role === 'Administrator'): ?>
    <p class="page-sub">Menu management (add / edit / delete) will be added here by Member C.</p>
<?php endif; ?>

<table class="table-clean">
    <thead>
        <tr><th>Item</th><th>Category</th><th>Description</th><th>Price</th></tr>
    </thead>
    <tbody>
    <?php if (!$items): ?>
        <tr><td colspan="4">No menu items yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($items as $it): ?>
        <tr>
            <td><strong><?= $e($it['foodName']) ?></strong></td>
            <td><?= $e($it['category'] ?? '-') ?></td>
            <td><?= $e($it['description'] ?? '') ?></td>
            <td>RM <?= number_format((float)$it['price'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
