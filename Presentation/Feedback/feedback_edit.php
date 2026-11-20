<?php
session_start();
require_once __DIR__ . '/../../business/FeedbackService.php';

$service = new FeedbackService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$record  = $service->getById($id);
$errors  = [];
$success = '';

if ($record === null) {
    exit("Feedback record not found. <a href='feedback_list.php'>Back</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating  = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $result  = $service->update($id, $rating, $comment);

    if ($result['success']) {
        $success = "Feedback updated successfully.";
        $record  = $service->getById($id);
    } else {
        $errors = $result['errors'];
        $record['rating']  = $rating;
        $record['comment'] = $comment;
    }
}

$pageTitle = 'Edit Feedback';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Edit Feedback <?= $e($id) ?></h1>
<p class="page-sub">Customer: <?= $e($record['name']) ?></p>

<div class="panel">
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= $e($err) ?></div>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $e($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $e($id) ?>">

        <div class="form-group">
            <label class="field">Rating</label>
            <div class="stars-input">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                           <?= (string)$record['rating'] === (string)$i ? 'checked' : '' ?>>
                    <label for="star<?= $i ?>" title="<?= $i ?> star(s)">★</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="field" for="comment">Comment</label>
            <textarea id="comment" name="comment" class="form-control" maxlength="500"><?= $e($record['comment'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-gold btn-sm">Update</button>
        <a href="feedback_list.php" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>