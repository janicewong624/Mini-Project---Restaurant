<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('Customer');
require_once __DIR__ . '/../../business/FeedbackService.php';

$service = new FeedbackService();
try { $bubblePool = $service->recent(20); } catch (Throwable $t) { $bubblePool = []; }
$maskName = fn($n) => mb_substr((string)$n, 0, 1) . '***';
$userID = $_SESSION['userID'];
$errors  = [];
$success = '';
$rating  = '';
$comment = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating  = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $result  = $service->submit($userID, $rating, $comment);
    if ($result['success']) {
        $success = "Thank you! Your feedback has been submitted.";
        $rating = $comment = '';
    } else {
        $errors = $result['errors'];
    }
}

$pageTitle = 'Give Feedback';
$bodyClass = 'feedback-page';
include __DIR__ . '/../includes/header.php';
?>

<?php include __DIR__ . '/../includes/bubbles.php'; ?>

<h1 class="page-title">How was your meal?</h1>
<p class="page-sub">Your feedback helps us serve you better.</p>

<div class="panel">
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= $e($err) ?></div>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $e($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label class="field">Rating</label>
            <div class="stars-input">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                           <?= $rating === (string)$i ? 'checked' : '' ?>>
                    <label for="star<?= $i ?>" title="<?= $i ?> star(s)">★</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="field" for="comment">Comment</label>
            <textarea id="comment" name="comment" class="form-control" maxlength="500"><?= $e($comment) ?></textarea>
        </div>

        <button type="submit" class="btn btn-gold btn-sm">Submit</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>