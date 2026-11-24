<?php
/**
 * ★ 暫時頁面（給 Member C）：只是讓 admin 導覽列的 Customers 連結點進去有東西看（唯讀列表）。
 *   Member C 做 Customer CRUD 時，直接用自己的 index.php 覆蓋這個檔案就可以。
 *   這個檔案不依賴 Order 的任何東西。
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (($_SESSION['role'] ?? '') !== 'Administrator') {
    header('Location: ../auth/login.php');   // 只有管理員可以看
    exit;
}
require_once __DIR__ . '/../../Data/Database.php';

$pdo       = Database::connect();
$customers = $pdo->query(
    "SELECT userID, name, email, contactNumber FROM user WHERE role = 'Customer' ORDER BY name"
)->fetchAll();

$pageTitle = 'Customers';
$bodyClass = 'feedback-page list-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Customers</h1>
<p class="page-sub">Registered customers. Customer management (add / edit / delete) will be added here by Member C.</p>

<table class="table-clean">
    <thead>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Contact</th></tr>
    </thead>
    <tbody>
    <?php if (!$customers): ?>
        <tr><td colspan="4">No customers yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($customers as $c): ?>
        <tr>
            <td><?= $e($c['userID']) ?></td>
            <td><?= $e($c['name']) ?></td>
            <td><?= $e($c['email']) ?></td>
            <td><?= $e($c['contactNumber'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
