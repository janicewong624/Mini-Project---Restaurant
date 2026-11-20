<?php
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Feedback</title>
    <style>
        .stars {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .stars input { display: none; }
        .stars label {
            font-size: 40px;
            color: #ccc;
            cursor: pointer;
            padding: 0 2px;
        }
        .stars label:hover,
        .stars label:hover ~ label,
        .stars input:checked ~ label {
            color: #f5b301;
        }
    </style>
</head>
<body>
    <h2>Edit Feedback <?= htmlspecialchars($id) ?></h2>
    <p>Customer: <?= htmlspecialchars($record['name']) ?></p>

    <?php foreach ($errors as $e): ?>
        <p style="color:red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <label>Rating:</label><br>
        <div class="stars">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                       <?= (string)$record['rating'] === (string)$i ? 'checked' : '' ?>>
                <label for="star<?= $i ?>" title="<?= $i ?> star(s)">★</label>
            <?php endfor; ?>
        </div>
        <br><br>

        <label>Comment:</label><br>
        <textarea name="comment" rows="4" cols="40" maxlength="500"><?= htmlspecialchars($record['comment'] ?? '') ?></textarea><br><br>

        <button type="submit">Update</button>
    </form>
    <p><a href="feedback_list.php">Back to list</a></p>
</body>
</html>