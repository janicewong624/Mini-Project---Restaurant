<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_any();
require_once __DIR__ . '/../../Business/OrderService.php';

$isAdmin = $user['role'] === 'Administrator';
$service = new OrderService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$detail  = $service->getDetail($id, $isAdmin ? null : $user['userID']);
$error   = '';
$backUrl = $isAdmin ? 'order_list.php' : 'my_orders.php';

if ($detail === null) {
    exit("Order not found. <a href='$backUrl'>Back</a>");
}
$o = $detail['order'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $service->cancel($id, $isAdmin ? null : $user['userID']);
    if ($result['success']) {
        order_flash_set("Order $id has been cancelled.");
        header("Location: $backUrl");
        exit;
    }
    $error = implode(' ', $result['errors']);
}

$pageTitle = 'Cancel Order';
$bodyClass = 'center-page order-page';
$navActive = $isAdmin ? 'orders' : 'myorders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Cancel Order</h1>
<p class="page-sub">Are you sure you want to cancel this order?</p>

<div class="panel">
    <?php if ($error): ?><div class="alert alert-error"><?= $e($error) ?></div><?php endif; ?>

    <p><strong>Order <?= $e($o['orderID']) ?></strong></p>
    <?php if ($isAdmin): ?><p><?= $e($o['customerName']) ?></p><?php endif; ?>
    <p style="margin:10px 0 24px;">
        <?= $e($o['orderType']) ?><?= $o['orderType'] === 'Dine-in' ? ' · Table ' . $e($o['tableNumber'] ?? '-') : '' ?>
        · RM <?= number_format((float)$o['totalAmount'], 2) ?> · <?= $e($o['orderStatus']) ?>
    </p>

    <form method="post">
        <input type="hidden" name="id" value="<?= $e($id) ?>">
        <button type="submit" class="btn btn-gold btn-sm" style="background:#c0504d;">Yes, cancel order</button>
        <a href="order_view.php?id=<?= urlencode($id) ?>" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
