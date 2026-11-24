<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_customer();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$items   = $service->getMenu();
$cart    = $_SESSION['cart'] ?? [];
$errors  = [];
$flash   = order_flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$cart, $errors] = $service->normalizeQuantities($_POST['qty'] ?? []);
    if (!$errors && !$cart) {
        $errors[] = "Please choose at least one item.";
    }
    if (!$errors) {
        $_SESSION['cart'] = $cart;
        header("Location: order_summary.php");
        exit;
    }
}

// 依分類分組
$byCategory = [];
foreach ($items as $it) {
    $byCategory[$it['category'] ?: 'Others'][] = $it;
}

$pageTitle = 'Choose Your Food';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'menu';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Choose Your Food</h1>
<p class="page-sub">Pick what you like, then continue to your order summary.</p>

<div class="ob-steps no-print">
    <span class="active">1. Choose food</span><span>2. Order summary</span><span>3. Dine-in / Takeaway</span><span>4. Track status</span>
</div>

<?php if ($flash): ?><div class="alert alert-success ob-alert"><?= $e($flash) ?></div><?php endif; ?>
<?php foreach ($errors as $err): ?>
    <div class="alert alert-error ob-alert"><?= $e($err) ?></div>
<?php endforeach; ?>

<?php if (!$items): ?>
    <div class="panel"><p>The menu is empty right now. Please check back later.</p></div>
<?php else: ?>
<form method="post" id="menuForm" class="ob-wide">
    <?php foreach ($byCategory as $cat => $list): ?>
        <h2 class="ob-cat"><?= $e($cat) ?></h2>
        <div class="ob-grid">
            <?php foreach ($list as $it): $q = (int)($cart[$it['menuItemID']] ?? 0); ?>
                <div class="ob-card">
                    <div class="ob-card-body">
                        <h3><?= $e($it['foodName']) ?></h3>
                        <?php if (!empty($it['description'])): ?><p><?= $e($it['description']) ?></p><?php endif; ?>
                        <span class="ob-price">RM <?= number_format((float)$it['price'], 2) ?></span>
                    </div>
                    <div class="qty-stepper" data-price="<?= $e($it['price']) ?>">
                        <button type="button" class="qty-minus" aria-label="Less">−</button>
                        <input type="number" name="qty[<?= $e($it['menuItemID']) ?>]" value="<?= $q ?>"
                               min="0" max="<?= OrderService::MAX_QTY ?>" aria-label="Quantity of <?= $e($it['foodName']) ?>">
                        <button type="button" class="qty-plus" aria-label="More">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <div class="ob-total-bar">
        <div><span id="totalCount">0</span> item(s) · <strong>RM <span id="totalAmount">0.00</span></strong></div>
        <button type="submit" class="btn btn-gold btn-sm">View Order Summary</button>
    </div>
</form>

<script>
const steppers = document.querySelectorAll('.qty-stepper');
function recalc() {
    let count = 0, amount = 0;
    steppers.forEach(s => {
        const q = parseInt(s.querySelector('input').value, 10) || 0;
        count += q; amount += q * parseFloat(s.dataset.price);
    });
    document.getElementById('totalCount').textContent = count;
    document.getElementById('totalAmount').textContent = amount.toFixed(2);
}
steppers.forEach(s => {
    const input = s.querySelector('input');
    s.querySelector('.qty-minus').addEventListener('click', () => { input.value = Math.max(0, (parseInt(input.value, 10) || 0) - 1); recalc(); });
    s.querySelector('.qty-plus').addEventListener('click',  () => { input.value = Math.min(<?= OrderService::MAX_QTY ?>, (parseInt(input.value, 10) || 0) + 1); recalc(); });
    input.addEventListener('input', recalc);
});
recalc();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
