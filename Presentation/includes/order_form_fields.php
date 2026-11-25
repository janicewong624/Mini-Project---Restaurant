<?php
/**
 * admin 新增 / 修改訂單共用的表單欄位：用餐方式、桌號、菜單數量。
 * 需要：$type, $tableNo, $menu（菜單陣列）, $qty（[menuItemID => 數量]）
 * @var Closure     $e  
 * @var array       $menu
 * @var string      $type
 */
?>
<div class="form-group">
    <label class="field">Order Type</label>
    <div class="ob-choice">
        <?php foreach (OrderService::TYPES as $t): ?>
            <label class="ob-choice-card">
                <input type="radio" name="orderType" value="<?= $e($t) ?>" <?= $type === $t ? 'checked' : '' ?>>
                <span><strong><?= $e($t) ?></strong></span>
            </label>
        <?php endforeach; ?>
    </div>
</div>

<div class="form-group" id="tableBox" style="display:none;">
    <label class="field" for="tableNumber">Table Number</label>
    <input type="text" id="tableNumber" name="tableNumber" class="form-control" maxlength="10"
           value="<?= $e($tableNo) ?>" placeholder="Required for dine-in">
</div>

<div class="form-group">
    <label class="field">Items (quantity 0 = not ordered)</label>
    <?php if (!$menu): ?>
        <p class="ob-hint">There are no menu items yet.</p>
    <?php else: ?>
    <table class="table-clean ob-flat">
        <thead><tr><th>Item</th><th>Category</th><th>Price</th><th style="width:110px;">Qty</th></tr></thead>
        <tbody>
        <?php foreach ($menu as $it): ?>
            <tr>
                <td><?= $e($it['foodName']) ?></td>
                <td><?= $e($it['category'] ?? '') ?></td>
                <td>RM <?= number_format((float)$it['price'], 2) ?></td>
                <td><input type="number" class="form-control" name="qty[<?= $e($it['menuItemID']) ?>]"
                           min="0" max="<?= OrderService::MAX_QTY ?>" value="<?= (int)($qty[$it['menuItemID']] ?? 0) ?>"></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
const typeRadios = document.querySelectorAll('input[name="orderType"]');
const tableBox = document.getElementById('tableBox');
function toggleTable() {
    const dine = document.querySelector('input[name="orderType"][value="Dine-in"]').checked;
    tableBox.style.display = dine ? 'block' : 'none';
}
typeRadios.forEach(r => r.addEventListener('change', toggleTable));
toggleTable();
</script>
