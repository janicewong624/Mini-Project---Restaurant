<?php
session_start();
require_once __DIR__ . '/../../business/FeedbackService.php';

$service   = new FeedbackService();
$feedbacks = $service->getAll();

$pageTitle = 'Feedback Records';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Feedback Records</h1>
<p class="page-sub">All customer feedback and ratings.</p>

<table class="table-clean">
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$feedbacks): ?>
        <tr><td colspan="6">No feedback yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($feedbacks as $f): ?>
        <tr>
            <td><?= $e($f['feedbackID']) ?></td>
            <td><?= $e($f['name']) ?></td>
            <td><span class="star-display"><?= str_repeat('★', (int)$f['rating']) . str_repeat('☆', 5 - (int)$f['rating']) ?></span></td>
            <td><?= $e($f['comment'] ?? '') ?></td>
            <td><?= date('d/m/Y', strtotime($f['feedbackDate'])) ?></td>
            <td class="actions">
                <a class="link-edit" href="feedback_edit.php?id=<?= urlencode($f['feedbackID']) ?>">Edit</a>
                <a class="link-delete" href="feedback_delete.php?id=<?= urlencode($f['feedbackID']) ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>