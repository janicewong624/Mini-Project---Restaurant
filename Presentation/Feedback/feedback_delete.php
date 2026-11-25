<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('Administrator');
require_once __DIR__ . '/../../business/FeedbackService.php';

$service = new FeedbackService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$record  = $service->getById($id);
$error   = '';

if ($record === null) {
    exit("Feedback record not found. <a href='feedback_list.php'>Back</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $service->delete($id);
    if ($result['success']) {
        header("Location: feedback_list.php");
        exit;
    }
    $error = implode(' ', $result['errors']);
}

$pageTitle = 'Delete Feedback';
$bodyClass = 'center-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Delete Feedback</h1>
<p class="page-sub">This action cannot be undone.</p>

<div class="panel">
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $e($error) ?></div>
    <?php endif; ?>

    <p><strong><?= $e($record['name']) ?></strong></p>
    <p class="star-display"><?= str_repeat('★', (int)$record['rating']) . str_repeat('☆', 5 - (int)$record['rating']) ?></p>
    <p style="margin:10px 0 24px;"><?= $e($record['comment'] ?? '') ?></p>

    <form method="post">
        <input type="hidden" name="id" value="<?= $e($id) ?>">
        <button type="submit" class="btn btn-gold btn-sm" style="background:#c0504d;">Yes, delete</button>
        <a href="feedback_list.php" class="btn btn-secondary btn-sm">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>