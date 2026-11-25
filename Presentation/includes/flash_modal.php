<?php
/**
 * 提示訊息彈窗：訊息會蓋在畫面中間，按 OK 才能繼續操作。
 * 用法：$modalMsg = order_flash_get(); ... include 'flash_modal.php';（要在 header.php 之後）
 *
 * @var string  $modalMsg  要顯示的訊息（空字串就什麼都不顯示）
 * @var Closure $e         htmlspecialchars 的簡寫
 */
$modalMsg  = $modalMsg ?? '';
$modalKind = ($GLOBALS['order_flash_kind'] ?? 'success') === 'error' ? 'error' : 'success';
if ($modalMsg === '') return;
?>
<div class="ob-modal" id="obModal" role="alertdialog" aria-modal="true" aria-describedby="obModalMsg">
    <div class="ob-modal-box <?= $modalKind ?>">
        <div class="ob-modal-icon" aria-hidden="true"><?= $modalKind === 'error' ? '!' : '✓' ?></div>
        <p id="obModalMsg"><?= $e($modalMsg) ?></p>
        <button type="button" class="btn btn-gold btn-sm" id="obModalOk">OK</button>
    </div>
</div>
<script>
(function () {
    const modal = document.getElementById('obModal'), ok = document.getElementById('obModalOk');
    function close() { modal.remove(); document.removeEventListener('keydown', onKey); }
    function onKey(ev) { if (ev.key === 'Escape' || ev.key === 'Enter') { ev.preventDefault(); close(); } }
    ok.addEventListener('click', close);
    document.addEventListener('keydown', onKey);
    ok.focus();
})();
</script>
