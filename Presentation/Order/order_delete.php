<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$detail  = $service->getDetail($id);
$error   = '';

if ($detail === null) {
    exit("Order not found. <a href='order_list.php'>Back</a>");
}
$o = $detail['order'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $service->delete($id);
    if ($result['success']) {
        order_flash_set("Order $id has been deleted.");
        header("Location: order_list.php");
        exit;
    }
    $error = implode(' ', $result['errors']);
}

$pageTitle = 'Delete Order';
$bodyClass = 'center-page order-page';
$navActive = 'orders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Delete Order</h1>
<p class="page-sub">This action cannot be undone.</p>

<div class="panel">
    <?php if ($error): ?><div class="alert alert-error"><?= $e($error) ?></div><?php endif; ?>

    <p><strong>Order <?= $e($o['orderID']) ?> · <?= $e($o['customerName']) ?></strong></p>
    <p style="margin:10px 0 24px;">
        <?= $e($o['orderType']) ?> · RM <?= number_format((float)$o['totalAmount'], 2) ?> · <?= $e($o['orderStatus']) ?>
    </p>

    <form method="post">
        <input type="hidden" name="id" value="<?= $e($id) ?>">
        <button type="submit" class="btn btn-gold btn-sm" style="background:#c0504d;">Yes, delete</button>
        <a href="order_view.php?id=<?= urlencode($id) ?>" class="btn btn-secondary btn-sm">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
