<?php
require_once __DIR__ . '/../includes/customer_admin_guard.php';
require_once __DIR__ . '/../../Business/CustomerService.php';

$service = new CustomerService();
$errors = [];
$name = customer_admin_value($_POST, 'full_name');
$email = customer_admin_value($_POST, 'email');
$phone = customer_admin_value($_POST, 'phone');
$csrfToken = customer_admin_csrf_token();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!customer_admin_csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $result = $service->add(
            $name,
            $email,
            $phone,
            customer_admin_value($_POST, 'password'),
            customer_admin_value($_POST, 'confirm_password')
        );
        if ($result['success']) {
            $_SESSION['customer_flash'] = 'Customer account has been added.';
            header('Location: index.php');
            exit;
        }
        $errors = $result['errors'];
    }
}

$pageTitle = 'Add Customer';
$bodyClass = 'feedback-page';
$navActive = 'customers';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Add Customer</h1>
<p class="page-sub">Create a new customer account.</p>

<div class="panel">
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= $e($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <div class="form-group">
            <label class="field" for="full-name">Full Name</label>
            <input class="form-control" type="text" id="full-name" name="full_name" maxlength="100"
                   value="<?= $e($name) ?>" autocomplete="name" required>
        </div>
        <div class="form-group">
            <label class="field" for="email">Email Address</label>
            <input class="form-control" type="email" id="email" name="email" maxlength="150"
                   value="<?= $e($email) ?>" autocomplete="email" required>
        </div>
        <div class="form-group">
            <label class="field" for="phone">Phone Number</label>
            <input class="form-control" type="tel" id="phone" name="phone" maxlength="20"
                   value="<?= $e($phone) ?>" autocomplete="tel" required>
        </div>
        <div class="form-group">
            <label class="field" for="password">Initial Password</label>
            <input class="form-control" type="password" id="password" name="password"
                   minlength="8" autocomplete="new-password" required>
            <small class="ob-hint">At least 8 characters, including letters and numbers.</small>
        </div>
        <div class="form-group">
            <label class="field" for="confirm-password">Confirm Password</label>
            <input class="form-control" type="password" id="confirm-password" name="confirm_password"
                   minlength="8" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-gold btn-sm">Add Customer</button>
        <a href="index.php" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
