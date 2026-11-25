<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('Administrator');
require_once __DIR__ . '/../../business/PaymentService.php';

$service = new PaymentService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$payment = $service->getById($id);
$error   = '';

if ($payment === null) {
    exit("Payment record not found. <a href='payment_list.php'>Back</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $service->cancel($id);
    if ($result['success']) {
        $_SESSION['flash'] = "Payment $id has been cancelled.";
        header("Location: payment_list.php");
        exit;
    }
    $error = implode(' ', $result['errors']);
}

$pageTitle = 'Cancel Payment';
$bodyClass = 'center-page payment-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Cancel Payment</h1>
<p class="page-sub">The order will return to Pending.</p>

<div class="panel">
    <?php if ($error): ?><div class="alert alert-error"><?= $e($error) ?></div><?php endif; ?>

    <p><strong><?= $e($payment['paymentID']) ?> · Order <?= $e($payment['orderID']) ?></strong></p>
    <p><?= $e($payment['name']) ?></p>
    <p style="margin:10px 0 24px;">
        RM <?= number_format((float)$payment['paymentAmount'], 2) ?> ·
        <?= $e($payment['paymentMethod'] ?? '-') ?><?= !empty($payment['onlineType']) ? ' (' . $e($payment['onlineType']) . ')' : '' ?>
    </p>

    <form method="post">
        <input type="hidden" name="id" value="<?= $e($id) ?>">
        <button type="submit" class="btn btn-gold btn-sm" style="background:#c0504d;">Yes, cancel payment</button>
        <a href="payment_list.php" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>