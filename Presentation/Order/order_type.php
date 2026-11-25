<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_customer();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$cart    = $_SESSION['cart'] ?? [];
$errors  = [];
$type    = '';
$tableNo = '';
$myOrders = $service->getMyOpenOrders($user['userID']);
$existing = $myOrders ? $myOrders[0]['orderID'] : '';   // 有進行中的訂單 -> 預設「加點到原訂單」

if (!$cart) {
    order_flash_set("Your order is empty. Please choose some food first.", 'error');
    header("Location: ../Menu/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type     = $_POST['orderType'] ?? '';
    $tableNo  = $_POST['tableNumber'] ?? '';
    $existing = $myOrders ? (string)($_POST['existingOrder'] ?? '') : '';

    if ($existing !== '') {
        $result = $service->addToOrder($existing, $user['userID'], $cart);
        $msg    = "Added to your order " . $existing . ". You will pay once for the whole order.";
    } else {
        $result = $service->place($user['userID'], $cart, $type, $tableNo);
        $msg    = "Thank you! Your order " . ($result['id'] ?? '') . " has been placed.";
    }

    if ($result['success']) {
        unset($_SESSION['cart']);
        order_flash_set($msg);
        header("Location: my_orders.php?open=" . urlencode($result['id']));
        exit;
    }
    $errors = $result['errors'];
}

[$lines, $total, $lineErrors] = $service->buildLines($cart);
$errors = array_merge($errors, $lineErrors);
$available = $service->getAvailableTableNumbers();
$myTables  = $service->getMyActiveTables($user['userID']);
if ($tableNo === '' && count($myTables) === 1) $tableNo = $myTables[0];   // 加點餐：預設沿用自己的桌子

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
        <?php if ($myOrders): ?>
        <p class="ob-hint" style="margin-bottom:8px;">You already have an order in progress. Add these items to it so you only pay once.</p>
        <div class="ob-choice" style="margin-bottom:16px;">
            <?php foreach ($myOrders as $mo): ?>
            <label class="ob-choice-card">
                <input type="radio" name="existingOrder" value="<?= $e($mo['orderID']) ?>" <?= $existing === $mo['orderID'] ? 'checked' : '' ?>>
                <span><strong>Add to Order <?= $e($mo['orderID']) ?></strong><small>Table <?= $e($mo['tableNumber']) ?> · currently RM <?= number_format((float)$mo['totalAmount'], 2) ?></small></span>
            </label>
            <?php endforeach; ?>
            <label class="ob-choice-card">
                <input type="radio" name="existingOrder" value="" <?= $existing === '' ? 'checked' : '' ?>>
                <span><strong>New separate order</strong><small>Paid separately</small></span>
            </label>
        </div>
        <?php endif; ?>

        <div id="newOrderBox">
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
            <?php if ($myTables): ?>
                <small class="ob-hint">Your current table: <?= $e(implode(', ', $myTables)) ?> (you can keep ordering here).</small><br>
            <?php endif; ?>
            <?php if ($available): ?>
                <small class="ob-hint">Available tables: <?= $e(implode(', ', $available)) ?></small>
            <?php else: ?>
                <small class="ob-hint">All tables are taken right now. Please ask our staff, or choose Takeaway.</small>
            <?php endif; ?>
        </div>

        </div><!-- /newOrderBox -->

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
// 選了「加點到原訂單」就不用再選內用/外帶和桌號
const newBox = document.getElementById('newOrderBox');
function toggleNew() {
    const ex = document.querySelector('input[name="existingOrder"]:checked');
    newBox.style.display = (ex && ex.value !== '') ? 'none' : 'block';
}
radios.forEach(r => r.addEventListener('change', toggleTable));
document.querySelectorAll('input[name="existingOrder"]').forEach(r => r.addEventListener('change', toggleNew));
toggleTable();
toggleNew();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
