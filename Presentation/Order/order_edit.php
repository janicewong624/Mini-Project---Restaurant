<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$detail  = $service->getDetail($id);

if ($detail === null) {
    exit("Order not found. <a href='order_list.php'>Back</a>");
}
$order    = $detail['order'];
$editable = $order['orderStatus'] === 'Pending';
$menu     = $service->getMenu();
$errors   = [];

$type    = $order['orderType'];
$tableNo = (string)($order['tableNumber'] ?? '');
$qty     = [];
foreach ($detail['items'] as $it) $qty[$it['menuItemID']] = (int)$it['quantity'];

if ($editable && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $type    = $_POST['orderType'] ?? '';
    $tableNo = $_POST['tableNumber'] ?? '';
    [$qty, $errors] = $service->normalizeQuantities($_POST['qty'] ?? []);

    if (!$errors) {
        $result = $service->update($id, $qty, $type, $tableNo);
        if ($result['success']) {
            order_flash_set("Order $id has been updated.");
            header("Location: order_view.php?id=" . urlencode($id));
            exit;
        }
        $errors = $result['errors'];
    }
}

$pageTitle = 'Edit Order';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'orders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Edit Order <?= $e($id) ?></h1>
<p class="page-sub">Customer: <?= $e($order['customerName']) ?></p>

<div class="panel wide">
<?php if (!$editable): ?>
    <div class="alert alert-error">
        Only Pending orders can be edited. This order is <?= $e($order['orderStatus']) ?>.
    </div>
    <a href="order_view.php?id=<?= urlencode($id) ?>" class="btn btn-secondary btn-sm">Back</a>
<?php else: ?>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= $e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" class="ob-form">
        <input type="hidden" name="id" value="<?= $e($id) ?>">

        <?php include __DIR__ . '/../includes/order_form_fields.php'; ?>

        <button type="submit" class="btn btn-gold btn-sm">Save Changes</button>
        <a href="order_view.php?id=<?= urlencode($id) ?>" class="btn btn-secondary btn-sm">Back</a>
    </form>
<?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
