<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/OrderService.php';

$service   = new OrderService();
$customers = $service->getCustomers();
$menu      = $service->getMenu();
$errors    = [];
$userID    = '';
$type      = 'Dine-in';
$tableNo   = '';
$qty       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID  = $_POST['userID'] ?? '';
    $type    = $_POST['orderType'] ?? '';
    $tableNo = $_POST['tableNumber'] ?? '';
    [$qty, $errors] = $service->normalizeQuantities($_POST['qty'] ?? []);

    if ($userID === '') $errors[] = "Please select a customer.";

    if (!$errors) {
        $result = $service->place($userID, $qty, $type, $tableNo);
        if ($result['success']) {
            order_flash_set("Order " . $result['id'] . " has been created.");
            header("Location: order_view.php?id=" . urlencode($result['id']));
            exit;
        }
        $errors = $result['errors'];
    }
}

$pageTitle = 'New Order';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'neworder';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">New Order</h1>
<p class="page-sub">Create an order for a customer.</p>

<div class="panel wide">
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= $e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" class="ob-form">
        <div class="form-group">
            <label class="field" for="userID">Customer</label>
            <select id="userID" name="userID" class="form-control">
                <option value="">-- Select customer --</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $e($c['userID']) ?>" <?= $userID === $c['userID'] ? 'selected' : '' ?>>
                        <?= $e($c['userID']) ?> · <?= $e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php include __DIR__ . '/../includes/order_form_fields.php'; ?>

        <button type="submit" class="btn btn-gold btn-sm">Create Order</button>
        <a href="order_list.php" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
