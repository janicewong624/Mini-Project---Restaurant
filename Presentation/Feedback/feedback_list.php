<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('Administrator');
require_once __DIR__ . '/../../Business/FeedbackService.php';

$service = new FeedbackService();
$rating  = $_GET['rating'] ?? '';
$from    = $_GET['from'] ?? '';
$to      = $_GET['to'] ?? '';

$feedbacks = $service->filtered($rating, $from, $to);
$stats     = $service->stats($from, $to);
$max       = max(1, max($stats['dist']));

$pageTitle = 'Feedback Records';
$bodyClass = 'feedback-page list-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Feedback Records</h1>
<p class="page-sub">All customer feedback and ratings.</p>

<div class="fb-summary">
    <div class="fb-score">
        <strong><?= $stats['avg'] ?></strong><span>/ 5</span>
        <small><?= $stats['count'] ?> review<?= $stats['count'] === 1 ? '' : 's' ?></small>
    </div>
    <div class="fb-bars">
        <?php for ($s = 5; $s >= 1; $s--): $c = $stats['dist'][$s]; ?>
            <div class="fb-bar-row">
                <span><?= $s ?> ★</span>
                <div class="fb-bar"><i style="width:<?= round($c / $max * 100) ?>%"></i></div>
                <span><?= $c ?></span>
            </div>
        <?php endfor; ?>
    </div>
</div>

<form method="get" class="fb-filter">
    <div class="ff">
        <label>Rating</label>
        <select name="rating" class="form-control">
            <option value="">All ratings</option>
            <?php for ($s = 5; $s >= 1; $s--): ?>
                <option value="<?= $s ?>" <?= (string)$rating === (string)$s ? 'selected' : '' ?>><?= $s ?> star<?= $s > 1 ? 's' : '' ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="ff">
        <label>From</label>
        <input type="date" name="from" value="<?= $e($from) ?>" class="form-control">
    </div>
    <div class="ff">
        <label>To</label>
        <input type="date" name="to" value="<?= $e($to) ?>" class="form-control">
    </div>
    <button class="btn btn-gold btn-sm" type="submit">Filter</button>
    <a href="feedback_list.php" class="btn btn-secondary btn-sm">Reset</a>
</form>

<table class="table-clean">
    <thead>
        <tr><th>ID</th><th>Customer</th><th>Rating</th><th>Comment</th><th>Date</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php if (!$feedbacks): ?>
        <tr><td colspan="6">No feedback found.</td></tr>
    <?php endif; ?>
    <?php foreach ($feedbacks as $f): ?>
        <tr>
            <td><?= $e($f['feedbackID']) ?></td>
            <td><?= $e($f['name']) ?></td>
            <td><span class="star-display"><?= str_repeat('★', (int)$f['rating']) . str_repeat('☆', 5 - (int)$f['rating']) ?></span></td>
            <td><?= $e($f['comment'] ?? '') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($f['feedbackDate'])) ?></td>
            <td class="actions">
                <a class="link-edit" href="feedback_edit.php?id=<?= urlencode($f['feedbackID']) ?>">Edit</a>
                <a class="link-delete" href="feedback_delete.php?id=<?= urlencode($f['feedbackID']) ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>