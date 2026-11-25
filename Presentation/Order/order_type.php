<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_customer();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$cart    = $_SESSION['cart'] ?? [];
$errors  = [];
$type    = '';
$tableNo = '';

if (!$cart) {
    order_flash_set("Your order is empty. Please choose some food first.", 'error');
    header("Location: ../Menu/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type    = $_POST['orderType'] ?? '';
    $tableNo = $_POST['tableNumber'] ?? '';
    $result  = $service->place($user['userID'], $cart, $type, $tableNo);

    if ($result['success']) {
        unset($_SESSION['cart']);
        order_flash_set("Thank you! Your order " . $result['id'] . " has been placed.");
        header("Location: my_orders.php?open=" . urlencode($result['id']));
        exit;
    }
    $errors = $result['errors'];
}

[$lines, $total, $lineErrors] = $service->buildLines($cart);
$errors = array_merge($errors, $lineErrors);
$available = $service->getAvailableTableNumbers();

$pageTitle = 'Dine-in or Takeaway';
$bodyClass = 'feedback-page order-page';
$navActive = 'menu';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Dine-in or Takeaway?</h1>
<p class="page-sub">Tell us how you would like to enjoy your meal.</p>

<div class="ob-steps no-print">
    <a class="done" href="../Menu/index.php">1. Choose food</a><a class="done" href="order_summary.php">2. Order summary</a><span class="active">3. Dine-in / Takeaway</span><span>4. Track in My Orders</span>
</div>

<div class="panel">
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= $e($err) ?></div>
    <?php endforeach; ?>

    <div class="ob-mini">
        <?php foreach ($lines as $l): ?>
            <div><span><?= (int)$l['quantity'] ?> × <?= $e($l['foodName']) ?></span><span>RM <?= number_format($l['subtotal'], 2) ?></span></div>
        <?php endforeach; ?>
        <div class="ob-mini-total"><span>Total</span><span>RM <?= number_format($total, 2) ?></span></div>
    </div>

    <form method="post">
        <div class="ob-choice">
            <label class="ob-choice-card">
                <input type="radio" name="orderType" value="Dine-in" <?= $type === 'Dine-in' ? 'checked' : '' ?>>
                <span><strong>Dine-in</strong><small>Eat at the restaurant</small></span>
            </label>
            <label class="ob-choice-card">
                <input type="radio" name="orderType" value="Takeaway" <?= $type === 'Takeaway' ? 'checked' : '' ?>>
                <span><strong>Takeaway</strong><small>Pack it to go</small></span>
            </label>
        </div>

        <div class="form-group" id="tableBox" style="display:none;">
            <label class="field" for="tableNumber">Table Number</label>
            <input type="text" id="tableNumber" name="tableNumber" class="form-control" maxlength="10"
                   value="<?= $e($tableNo) ?>" placeholder="Enter your table number">
            <?php if ($available): ?>
                <small class="ob-hint">Available tables: <?= $e(implode(', ', $available)) ?></small>
            <?php else: ?>
                <small class="ob-hint">All tables are taken right now. Please ask our staff, or choose Takeaway.</small>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-gold btn-sm">Confirm Order</button>
        <a href="order_summary.php" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<script>
const radios = document.querySelectorAll('input[name="orderType"]');
const tableBox = document.getElementById('tableBox');
function toggleTable() {
    const dine = document.querySelector('input[name="orderType"][value="Dine-in"]').checked;
    tableBox.style.display = dine ? 'block' : 'none';
}
radios.forEach(r => r.addEventListener('change', toggleTable));
toggleTable();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
