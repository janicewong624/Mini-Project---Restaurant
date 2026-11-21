<?php
session_start();
require_once __DIR__ . '/../../business/ReceiptService.php';

$id   = $_GET['id'] ?? '';
$type = $_GET['type'] ?? 'Original';

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
    <p>Payment Method : <?= $e($h['paymentMethod']) ?></p>
    <p>Status         : <?= $voided ? 'CANCELLED' : 'PAID' ?></p>
    <hr>
    <p class="center">Thank you!</p>
</div>

<p class="no-print" style="text-align:center;margin-top:16px;">
    <button onclick="window.print()" class="btn btn-gold btn-sm">Print</button>
    <a href="../payment/payment_list.php" class="btn btn-secondary btn-sm">Back to Payments</a>
</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>