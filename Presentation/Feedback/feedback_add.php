<?php
session_start();
require_once __DIR__ . '/../../business/FeedbackService.php';

$service = new FeedbackService();
$userID  = $_SESSION['userID'] ?? 'U0001';   // 暫時：登入做好後會自動用 session
$errors  = [];
$success = '';
$rating  = '';
$comment = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating  = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $result  = $service->submit($userID, $rating, $comment);

    if ($result['success']) {
        $success = "Thank you! Your feedback has been submitted (ID: {$result['id']}).";
        $rating = $comment = '';
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Submit Feedback</title>
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
    <h2>Submit Feedback</h2>

    <?php foreach ($errors as $e): ?>
        <p style="color:red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Rating:</label><br>
        <div class="stars">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                       <?= $rating === (string)$i ? 'checked' : '' ?>>
                <label for="star<?= $i ?>" title="<?= $i ?> star(s)">★</label>
            <?php endfor; ?>
        </div>
        <br><br>

        <label>Comment:</label><br>
        <textarea name="comment" rows="4" cols="40" maxlength="500"><?= htmlspecialchars($comment) ?></textarea><br><br>

        <button type="submit">Submit</button>
    </form>
</body>
</html>