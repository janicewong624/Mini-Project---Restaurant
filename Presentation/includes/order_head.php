<?php
/**
 * header.php 之後 include：載入 Member B 專用的樣式 order.css，再顯示小導覽列。
 * （header.php 是共用檔案，所以額外的 CSS 在這裡載入，不去改它。）
 */
?>
<link rel="stylesheet" href="<?= $base ?>Assets/css/order.css?v=<?= @filemtime(__DIR__ . '/../../Assets/css/order.css') ?: 1 ?>">
<?php include __DIR__ . '/order_nav.php'; ?>
