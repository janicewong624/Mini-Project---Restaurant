<?php
require_once __DIR__ . '/../../business/FeedbackService.php';

$service   = new FeedbackService();
$feedbacks = $service->getAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Feedback List</title>
</head>
<body>
    <h2>Feedback Records</h2>

    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th><th>Customer</th><th>Rating</th>
            <th>Comment</th><th>Date</th><th>Action</th>
        </tr>
        <?php if (!$feedbacks): ?>
            <tr><td colspan="6">No feedback yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($feedbacks as $f): ?>
        <tr>
            <td><?= htmlspecialchars($f['feedbackID']) ?></td>
            <td><?= htmlspecialchars($f['name']) ?></td>
            <td style="color:#f5b301; font-size:20px;">
                <?= str_repeat('★', (int)$f['rating']) . str_repeat('☆', 5 - (int)$f['rating']) ?>
            </td>
            <td><?= htmlspecialchars($f['comment'] ?? '') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($f['feedbackDate'])) ?></td>
            <td>
                <a href="feedback_edit.php?id=<?= urlencode($f['feedbackID']) ?>">Edit</a> |
                <a href="feedback_delete.php?id=<?= urlencode($f['feedbackID']) ?>">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="feedback_add.php">Add Feedback</a></p>
</body>
</html>