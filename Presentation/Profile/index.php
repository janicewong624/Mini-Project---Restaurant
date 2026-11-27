<?php
session_start();
if (empty($_SESSION['userID'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../../Data/UserData.php';

$users  = new UserData();
$uid    = (string)$_SESSION['userID'];
$user   = $users->findProfile($uid);
$errors = [];
$ok     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['profile_csrf'] ?? '', $_POST['csrf'])) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'info') {
            $name  = trim($_POST['name'] ?? '');
            $phone = preg_replace('/[\s-]/', '', trim($_POST['phone'] ?? ''));
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Name must be 2 to 100 characters.';
            if (!preg_match('/^\+?[0-9]{9,15}$/', $phone))       $errors[] = 'Please enter a valid phone number.';
            if (!$errors) {
                $users->updateProfile($uid, $name, $phone);
                $_SESSION['name'] = $name;
                $ok = 'Profile updated.';
            }
        } elseif ($action === 'password') {
            $cur = $_POST['current'] ?? '';
            $new = $_POST['new'] ?? '';
            $con = $_POST['confirm'] ?? '';
            if (!password_verify($cur, $user['password']))                       $errors[] = 'Current password is incorrect.';
            elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*[0-9]).{8,}$/', $new))     $errors[] = 'New password must be at least 8 characters with letters and numbers.';
            elseif ($new !== $con)                                               $errors[] = 'Passwords do not match.';
            if (!$errors) {
                $users->updatePassword($uid, password_hash($new, PASSWORD_DEFAULT));
                $ok = 'Password changed.';
            }
        }
        $user = $users->findProfile($uid);
    }
}
$_SESSION['profile_csrf'] = $_SESSION['profile_csrf'] ?? bin2hex(random_bytes(32));

$pageTitle = 'My Profile';
$bodyClass = 'feedback-page';
include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">My Profile</h1>
<p class="page-sub">Update your name, phone and password. Email cannot be changed.</p>

<div class="panel">
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= $e($err) ?></div><?php endforeach; ?>
    <?php if ($ok): ?><div class="alert alert-success"><?= $e($ok) ?></div><?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= $e($_SESSION['profile_csrf']) ?>">
        <input type="hidden" name="action" value="info">
        <div class="form-group"><label class="field">Email (cannot be changed)</label>
            <input class="form-control" value="<?= $e($user['email']) ?>" disabled></div>
        <div class="form-group"><label class="field">Name</label>
            <input class="form-control" name="name" value="<?= $e($user['name']) ?>"></div>
        <div class="form-group"><label class="field">Phone</label>
            <input class="form-control" name="phone" value="<?= $e($user['contactNumber']) ?>"></div>
        <button class="btn btn-gold btn-sm" type="submit">Save Profile</button>
    </form>

    <hr style="margin:24px 0;border:0;border-top:1px solid #eee8dc;">

    <form method="post">
        <input type="hidden" name="csrf" value="<?= $e($_SESSION['profile_csrf']) ?>">
        <input type="hidden" name="action" value="password">
        <div class="form-group"><label class="field">Current Password</label>
            <input class="form-control" type="password" name="current" autocomplete="current-password"></div>
        <div class="form-group"><label class="field">New Password</label>
            <input class="form-control" type="password" name="new" autocomplete="new-password"></div>
        <div class="form-group"><label class="field">Confirm New Password</label>
            <input class="form-control" type="password" name="confirm" autocomplete="new-password"></div>
        <button class="btn btn-gold btn-sm" type="submit">Change Password</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>