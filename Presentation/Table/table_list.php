<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/TableService.php';

$service = new TableService();
$tables  = $service->getAll();
$flash   = order_flash_get();

$pageTitle = 'Tables';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'tables';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Tables</h1>
<p class="page-sub">Manage the restaurant tables.</p>

<?php $modalMsg = $flash; include __DIR__ . '/../includes/flash_modal.php'; ?>

<p style="margin-bottom:14px;"><a href="table_add.php" class="btn btn-gold btn-sm">+ Add Table</a></p>

<table class="table-clean">
    <thead>
        <tr>
            <th>ID</th><th>Table No.</th><th>Seats</th><th>Status</th><th>Active Orders</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$tables): ?>
        <tr><td colspan="6">No tables yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($tables as $t): ?>
        <tr>
            <td><?= $e($t['tableID']) ?></td>
            <td><strong><?= $e($t['tableNumber']) ?></strong></td>
            <td><?= (int)$t['seatingCapacity'] ?></td>
            <td><span class="badge badge-<?= strtolower($e($t['tableStatus'])) ?>"><?= $e($t['tableStatus']) ?></span></td>
            <td><?= (int)$t['activeOrders'] ?></td>
            <td class="actions">
                <a class="link-edit" href="table_edit.php?id=<?= urlencode($t['tableID']) ?>">Edit</a>
                <a class="link-delete" href="table_delete.php?id=<?= urlencode($t['tableID']) ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
