<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_customer();
require_once __DIR__ . '/../../Business/OrderService.php';

$service = new OrderService();
$cart    = $_SESSION['cart'] ?? [];
$errors  = [];
$notice  = '';

if (!$cart) {
    order_flash_set("Your order is empty. Please choose some food first.", 'error');
    header("Location: ../Menu/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 顧客在這裡修改數量（填 0 = 移除這個項目）
    [$newCart, $errors] = $service->normalizeQuantities($_POST['qty'] ?? []);
    if (!$errors) {
        if (!$newCart) {
            unset($_SESSION['cart']);
            order_flash_set("All items were removed. Please choose some food.", 'error');
            header("Location: ../Menu/index.php");
            exit;
        }
        $_SESSION['cart'] = $cart = $newCart;

        if (($_POST['action'] ?? '') === 'order') {
            header("Location: order_type.php");   
            exit;
        }
        $notice = "Your order has been updated.";
    }
}

[$lines, $total, $lineErrors] = $service->buildLines($cart);
$errors = array_merge($errors, $lineErrors);

$pageTitle = 'Order Summary';
$bodyClass = 'feedback-page order-page list-page';
$navActive = 'menu';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Order Summary</h1>
<p class="page-sub">Check your items. Change the quantity with + / − (0 removes an item).</p>

<div class="ob-steps no-print">
    <a class="done" href="../Menu/index.php">1. Choose food</a><span class="active">2. Order summary</span><span>3. Dine-in / Takeaway</span><span>4. Track in My Orders</span>
</div>

<?php if ($notice): ?><div class="alert alert-success ob-alert"><?= $e($notice) ?></div><?php endif; ?>
<?php foreach ($errors as $err): ?>
    <div class="alert alert-error ob-alert"><?= $e($err) ?></div>
<?php endforeach; ?>

<form method="post" class="ob-wide">
    <table class="table-clean">
        <thead>
            <tr><th>Item</th><th>Unit Price</th><th style="width:170px;">Quantity</th><th style="text-align:right;">Subtotal</th></tr>
        </thead>
        <tbody>
        <?php foreach ($lines as $l): ?>
            <tr class="ob-line">
                <td><strong><?= $e($l['foodName']) ?></strong></td>
                <td>RM <?= number_format($l['unitPrice'], 2) ?></td>
                <td>
                    <div class="qty-stepper small" data-price="<?= $e($l['unitPrice']) ?>">
                        <button type="button" class="qty-minus" aria-label="Less">−</button>
                        <input type="number" name="qty[<?= $e($l['menuItemID']) ?>]" value="<?= (int)$l['quantity'] ?>"
                               min="0" max="<?= OrderService::MAX_QTY ?>">
                        <button type="button" class="qty-plus" aria-label="More">+</button>
                    </div>
                </td>
                <td style="text-align:right;">RM <span class="ob-line-sub"><?= number_format($l['subtotal'], 2) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="ob-total-row">
                <td colspan="3" style="text-align:right;">Total</td>
                <td style="text-align:right;">RM <span id="grandTotal"><?= number_format($total, 2) ?></span></td>
            </tr>
        </tfoot>
    </table>

    <div class="ob-actions">
        <a href="../Menu/index.php" class="btn btn-secondary btn-sm">+ Add more items</a>
        <button type="submit" name="action" value="order" class="btn btn-gold btn-sm">Order</button>
    </div>
</form>

<script>
// 按 + / − 時，小計和總額馬上跟著變（不需要按任何更新按鈕）
const MAX_QTY = <?= OrderService::MAX_QTY ?>;
const grand = document.getElementById('grandTotal');
function recalc() {
    let total = 0;
    document.querySelectorAll('.ob-line').forEach(row => {
        const s = row.querySelector('.qty-stepper');
        const q = Math.min(MAX_QTY, Math.max(0, parseInt(s.querySelector('input').value, 10) || 0));
        const sub = q * parseFloat(s.dataset.price);
        row.querySelector('.ob-line-sub').textContent = sub.toFixed(2);
        row.classList.toggle('is-zero', q === 0);
        total += sub;
    });
    grand.textContent = total.toFixed(2);
}
document.querySelectorAll('.qty-stepper').forEach(s => {
    const input = s.querySelector('input');
    s.querySelector('.qty-minus').addEventListener('click', () => { input.value = Math.max(0, (parseInt(input.value, 10) || 0) - 1); recalc(); });
    s.querySelector('.qty-plus').addEventListener('click',  () => { input.value = Math.min(MAX_QTY, (parseInt(input.value, 10) || 0) + 1); recalc(); });
    input.addEventListener('input', recalc);
});
recalc();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
