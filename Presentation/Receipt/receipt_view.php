<?php
session_start();
require_once __DIR__ . '/../../business/ReceiptService.php';

$id   = $_GET['id'] ?? '';
$type = $_GET['type'] ?? 'Original';

$mailMsg = ''; $mailOk = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $r = (new ReceiptService())->emailToCustomer($id);
    $mailOk  = $r['success'];
    $mailMsg = $mailOk ? 'Receipt has been emailed to the customer.' : ($r['error'] ?? 'Failed to send.');
}

$data = (new ReceiptService())->view($id, $type);
if ($data === null) {
    exit("Receipt not found. <a href='../payment/payment_list.php'>Back</a>");
}
$h      = $data['header'];
$type   = $data['type'];
$voided = $h['paymentStatus'] === 'Cancelled';

$pageTitle = 'Receipt';
$bodyClass = 'feedback-page payment-page receipt-page';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($mailMsg): ?>
    <div class="alert <?= $mailOk ? 'alert-success' : 'alert-error' ?> no-print" style="max-width:380px;margin:0 auto 14px;">
        <?= $e($mailMsg) ?>
    </div>
<?php endif; ?>

<div class="receipt-tabs no-print">
    <a href="?id=<?= urlencode($id) ?>&type=Original"  class="<?= $type === 'Original'  ? 'active' : '' ?>">Customer Copy (Original)</a>
    <a href="?id=<?= urlencode($id) ?>&type=Duplicate" class="<?= $type === 'Duplicate' ? 'active' : '' ?>">Restaurant Copy (Duplicate)</a>
</div>

<div class="receipt">
    <?php if ($voided): ?><div class="receipt-void">VOIDED</div><?php endif; ?>
    <h2><?= $e($restaurantName) ?></h2>
    <hr>
    <p>Receipt No : <?= $e($data['receipt']['receiptID']) ?></p>
    <p>Payment ID : <?= $e($h['paymentID']) ?></p>
    <p>Order ID   : <?= $e($h['orderID']) ?> (<?= $e($h['orderType']) ?>)</p>
    <p>Date       : <?= date('d/m/Y H:i', strtotime($h['paymentDate'])) ?></p>
    <p>Customer   : <?= $e($h['customerName']) ?></p>
    <p>Table      : <?= $e($h['tableNumber']) ?></p>
    <hr>
    <table>
        <?php foreach ($data['items'] as $it): ?>
        <tr>
            <td><?= $e($it['foodName']) ?></td>
            <td>x<?= (int)$it['quantity'] ?></td>
            <td class="r">RM <?= number_format((float)$it['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <hr>
    <p class="total">TOTAL <span>RM <?= number_format((float)$h['paymentAmount'], 2) ?></span></p>
    <p>Payment Method : <?= $e($h['paymentMethod']) ?><?= !empty($h['onlineType']) ? ' (' . $e($h['onlineType']) . ')' : '' ?></p>
    <p>Status         : <?= $voided ? 'CANCELLED' : 'PAID' ?></p>
    <hr>
    <p class="center">Thank you!</p>
</div>

<div class="no-print" style="text-align:center;margin-top:16px;">
    <button onclick="window.print()" class="btn btn-gold btn-sm">Print</button>
    <?php if (!$voided): ?>
        <form method="post" style="display:inline;">
            <button type="submit" name="send_email" class="btn btn-gold btn-sm">Email to Customer</button>
        </form>
    <?php endif; ?>
    <a href="../payment/payment_list.php" class="btn btn-secondary btn-sm">Back to Payments</a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>