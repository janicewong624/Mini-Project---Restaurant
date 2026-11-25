<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('Administrator');
require_once __DIR__ . '/../../business/PaymentService.php';

$service = new PaymentService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$payment = $service->getById($id);
$errors  = [];
$success = '';

if ($payment === null) {
    exit("Payment record not found. <a href='payment_list.php'>Back</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $service->updateMethod($id, $_POST['method'] ?? '', $_POST['onlineType'] ?? '');
    if ($result['success']) {
        $success = "Payment updated successfully.";
        $payment = $service->getById($id);
    } else {
        $errors = $result['errors'];
    }
}

$method     = $payment['paymentMethod'] ?? '';
$onlineType = $payment['onlineType'] ?? '';

$pageTitle = 'Edit Payment';
$bodyClass = 'feedback-page payment-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Edit Payment <?= $e($id) ?></h1>
<p class="page-sub">Order <?= $e($payment['orderID']) ?> · <?= $e($payment['name']) ?> · RM <?= number_format((float)$payment['paymentAmount'], 2) ?></p>

<div class="panel">
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= $e($err) ?></div><?php endforeach; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $e($id) ?>">

        <div class="form-group">
            <label class="field" for="method">Payment Method</label>
            <select id="method" name="method" class="form-control">
                <option value="">-- Select method --</option>
                <?php foreach (PaymentService::METHODS as $m): ?>
                    <option value="<?= $e($m) ?>" <?= $method === $m ? 'selected' : '' ?>><?= $e($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" id="onlineBox" style="display:none;">
            <label class="field" for="onlineType">Online Payment Type</label>
            <select id="onlineType" name="onlineType" class="form-control">
                <option value="">-- Select type --</option>
                <?php foreach (PaymentService::ONLINE_TYPES as $t): ?>
                    <option value="<?= $e($t) ?>" <?= $onlineType === $t ? 'selected' : '' ?>><?= $e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-gold btn-sm">Update</button>
        <a href="payment_list.php" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<script>
const methodSel = document.getElementById('method'),
      onlineBox = document.getElementById('onlineBox'),
      onlineSel = document.getElementById('onlineType');
function toggleOnline() {
    const on = methodSel.value === 'Online Payment';
    onlineBox.style.display = on ? 'block' : 'none';
    if (!on) onlineSel.value = '';
}
methodSel.addEventListener('change', toggleOnline); toggleOnline();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>