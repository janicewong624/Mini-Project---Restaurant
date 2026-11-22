<?php
session_start();
require_once __DIR__ . '/../../business/PaymentService.php';

$service = new PaymentService();
$orders  = $service->getPayableOrders();
$errors  = [];
$orderID = '';
$method  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderID = $_POST['orderID'] ?? '';
    $method  = $_POST['method'] ?? '';
    $result  = $service->add($orderID, $method);

    if ($result['success']) {
        header("Location: ../Receipt/receipt_view.php?id=" . urlencode($result['id']));
        exit;
    }
    $errors = $result['errors'];
}

$pageTitle = 'Add Payment';
$bodyClass = 'feedback-page payment-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Add Payment</h1>
<p class="page-sub">Confirm payment received at the counter.</p>

<div class="panel">
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= $e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="form-group">
            <label class="field" for="orderID">Order</label>
            <select id="orderID" name="orderID" class="form-control">
                <option value="">-- Select order --</option>
                <?php foreach ($orders as $o): ?>
                    <option value="<?= $e($o['orderID']) ?>" data-amount="<?= number_format((float)$o['totalAmount'], 2) ?>"
                        <?= $orderID === $o['orderID'] ? 'selected' : '' ?>>
                        <?= $e($o['orderID']) ?> · Table <?= $e($o['tableNumber']) ?> · <?= $e($o['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="field">Amount to pay</label>
            <div id="amountBox" class="form-control" style="font-weight:700;">RM 0.00</div>
        </div>

        <div class="form-group">
            <label class="field" for="method">Payment Method</label>
            <select id="method" name="method" class="form-control">
                <option value="">-- Select method --</option>
                <?php foreach (PaymentService::METHODS as $m): ?>
                    <option value="<?= $e($m) ?>" <?= $method === $m ? 'selected' : '' ?>><?= $e($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-gold btn-sm">Confirm Payment</button>
        <a href="payment_list.php" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<script>
const sel = document.getElementById('orderID'), box = document.getElementById('amountBox');
function showAmount() {
    const opt = sel.options[sel.selectedIndex];
    box.textContent = 'RM ' + (opt.dataset.amount || '0.00');
}
sel.addEventListener('change', showAmount); showAmount();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>