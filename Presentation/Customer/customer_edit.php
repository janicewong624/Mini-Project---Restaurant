<?php
require_once __DIR__ . '/../includes/customer_admin_guard.php';
require_once __DIR__ . '/../../Business/CustomerService.php';

$service = new CustomerService();
$id = customer_admin_value($_GET, 'id');
$customer = $service->getById($id);
$errors = [];
$isPost = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');

if ($customer === null) {
    http_response_code(404);
}

$name = $isPost ? customer_admin_value($_POST, 'full_name') : ($customer['name'] ?? '');
$email = $isPost ? customer_admin_value($_POST, 'email') : ($customer['email'] ?? '');
$phone = $isPost ? customer_admin_value($_POST, 'phone') : ($customer['contactNumber'] ?? '');
$csrfToken = customer_admin_csrf_token();

if ($customer !== null && $isPost) {
    if (!customer_admin_csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $name = customer_admin_value($_POST, 'full_name');
        $email = customer_admin_value($_POST, 'email');
        $phone = customer_admin_value($_POST, 'phone');
        $result = $service->update($id, $name, $email, $phone);
        if ($result['success']) {
            $_SESSION['customer_flash'] = 'Customer details have been updated.';
            header('Location: index.php');
            exit;
        }
        $errors = $result['errors'];
    }
}

$pageTitle = 'Edit Customer';
$bodyClass = 'feedback-page';
$navActive = 'customers';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($customer === null): ?>
    <h1 class="page-title">Customer Not Found</h1>
    <p class="page-sub">This customer does not exist or is no longer available.</p>
    <a href="index.php" class="btn btn-secondary btn-sm">Back to Customers</a>
<?php else: ?>
    <h1 class="page-title">Edit Customer</h1>
    <p class="page-sub">Customer ID: <?= $e($id) ?></p>

    <div class="panel">
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= $e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" action="customer_edit.php?id=<?= urlencode($id) ?>"
              onsubmit="return confirm('Save changes to this customer?');">
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
            <button type="submit" class="btn btn-gold btn-sm">Save Changes</button>
            <a href="customer_view.php?id=<?= urlencode($id) ?>" class="btn btn-secondary btn-sm">Cancel</a>
        </form>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
