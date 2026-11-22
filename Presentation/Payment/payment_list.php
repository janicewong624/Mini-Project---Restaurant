<?php
session_start();
require_once __DIR__ . '/../../business/PaymentService.php';

$service  = new PaymentService();
$payments = $service->getAll();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$pageTitle = 'Payment Records';
$bodyClass = 'feedback-page payment-page list-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Payment Records</h1>
<p class="page-sub">All payment transactions.</p>

<?php if ($flash): ?><div class="alert alert-success"><?= $e($flash) ?></div><?php endif; ?>

<p style="margin-bottom:14px;"><a href="payment_add.php" class="btn btn-gold btn-sm">+ Add Payment</a></p>

<table class="table-clean">
    <thead>
        <tr>
            <th>ID</th><th>Order</th><th>Customer</th><th>Amount</th>
            <th>Method</th><th>Status</th><th>Date</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$payments): ?>
        <tr><td colspan="8">No payment records yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($payments as $p): ?>
        <tr>
            <td><?= $e($p['paymentID']) ?></td>
            <td><?= $e($p['orderID']) ?></td>
            <td><?= $e($p['name']) ?></td>
            <td>RM <?= number_format((float)$p['paymentAmount'], 2) ?></td>
            <td><?= $e($p['paymentMethod'] ?? '-') ?><?= !empty($p['onlineType']) ? ' (' . $e($p['onlineType']) . ')' : '' ?></td>
            <td><span class="badge badge-<?= strtolower($e($p['paymentStatus'])) ?>"><?= $e($p['paymentStatus']) ?></span></td>
            <td><?= $p['paymentDate'] ? date('d/m/Y H:i', strtotime($p['paymentDate'])) : '-' ?></td>
            <td class="actions">
                <?php if ($p['paymentStatus'] === 'Completed'): ?>
                    <a class="link-edit" href="../Receipt/receipt_view.php?id=<?= urlencode($p['paymentID']) ?>">Receipt</a>
                <?php endif; ?>
                <?php if ($p['paymentStatus'] !== 'Cancelled'): ?>
                    <a class="link-edit" href="payment_edit.php?id=<?= urlencode($p['paymentID']) ?>">Edit</a>
                    <a class="link-delete" href="payment_cancel.php?id=<?= urlencode($p['paymentID']) ?>">Cancel</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>