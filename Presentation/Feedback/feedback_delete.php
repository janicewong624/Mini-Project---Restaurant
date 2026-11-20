<?php
require_once __DIR__ . '/../../business/FeedbackService.php';

$service = new FeedbackService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$record  = $service->getById($id);

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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete Feedback</title>
</head>
<body>
    <h2>Delete Feedback</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <p>Are you sure you want to delete this feedback?</p>
    <p><strong><?= htmlspecialchars($record['name']) ?></strong>
       <span style="color:#f5b301;"><?= str_repeat('★', (int)$record['rating']) . str_repeat('☆', 5 - (int)$record['rating']) ?></span>
       <?= htmlspecialchars($record['comment'] ?? '') ?></p>

    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <button type="submit">Yes, delete</button>
        <a href="feedback_list.php">Cancel</a>
    </form>
</body>
</html>