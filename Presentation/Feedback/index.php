<?php
session_start();
require_once __DIR__ . '/../../Business/FeedbackService.php';
$svc = new FeedbackService();
$bubblePool = $svc->recent(20);
$sum = $svc->summary();

$pageTitle = 'Customer Reviews';
$bodyClass = 'feedback-page';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/bubbles.php';
?>
<h1 class="page-title">What Our Customers Say</h1>
<p class="page-sub"><?= $sum['avg'] ?> / 5 average · <?= $sum['count'] ?> reviews</p>

<div class="panel" style="text-align:center;">
    <div class="star-display" style="font-size:2rem;">★★★★★</div>
    <p style="margin:10px 0 20px;">Share your own experience with us.</p>
    <a href="feedback_add.php" class="btn btn-gold">Write a Review</a>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>