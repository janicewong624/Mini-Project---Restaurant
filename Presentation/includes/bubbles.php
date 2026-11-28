<?php
// 需要 $bubblePool（評論陣列：name, rating, comment）
$bubblePool = array_values($bubblePool ?? []);
if (!$bubblePool) return;
$maskName = fn($n) => mb_substr((string)$n, 0, 1) . '***';
$data = array_map(fn($b) => [
    'rating'  => (int)$b['rating'],
    'comment' => mb_strimwidth((string)$b['comment'], 0, 70, '…'),
    'name'    => $maskName($b['name']),
], $bubblePool);
?>
<div class="bubble-field" aria-hidden="true">
    <?php for ($s = 1; $s <= 6; $s++): ?>
        <div class="bubble slot-<?= $s ?>" style="--delay:<?= $s * 0.7 ?>s">
            <span class="star-display"></span><p></p><small></small>
        </div>
    <?php endfor; ?>
</div>
<script>
(function () {
  var pool = <?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  var bubbles = document.querySelectorAll('.bubble-field .bubble');
  var offset = 0;
  function render() {
    bubbles.forEach(function (el, i) {
      var r = pool[(offset + i) % pool.length];
      el.classList.remove('show');
      setTimeout(function () {
        el.querySelector('.star-display').textContent = '★'.repeat(r.rating);
        el.querySelector('p').textContent = r.comment;
        el.querySelector('small').textContent = '— ' + r.name;
        el.classList.add('show');
      }, 400);
    });
    offset = (offset + bubbles.length) % pool.length;
  }
  render();
  if (pool.length > bubbles.length) setInterval(render, 5000);   // 每 5 秒換一批
})();
</script>