<?php
require_once __DIR__ . '/../includes/customer_admin_guard.php';
require_once __DIR__ . '/../../Business/CustomerService.php';

$service = new CustomerService();
$id = customer_admin_value($_GET, 'id');
$customer = $service->getById($id);
$errors = [];
$csrfToken = customer_admin_csrf_token();

if ($customer === null) {
    http_response_code(404);
}

if ($customer !== null && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    if (!customer_admin_csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $result = $service->delete($id);
        if ($result['success']) {
            $_SESSION['customer_flash'] = 'Customer account has been deleted.';
            header('Location: index.php');
            exit;
        }
        $errors = $result['errors'];
    }
}

$pageTitle = 'Delete Customer';
$bodyClass = 'center-page';
$navActive = 'customers';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($customer === null): ?>
    <h1 class="page-title">Customer Not Found</h1>
    <p class="page-sub">This customer does not exist or is no longer available.</p>
    <a href="index.php" class="btn btn-secondary btn-sm">Back to Customers</a>
<?php else: ?>
    <h1 class="page-title">Delete Customer</h1>
    <p class="page-sub">This action cannot be undone.</p>

    <div class="panel">
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= $e($error) ?></div>
        <?php endforeach; ?>

        <p><strong><?= $e($customer['name']) ?></strong></p>
        <p><?= $e($customer['email']) ?></p>
        <form method="post" action="customer_delete.php?id=<?= urlencode($id) ?>" style="margin-top:24px;">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <button type="submit" class="btn btn-gold btn-sm" style="background:#c0504d;">Yes, delete customer</button>
            <a href="customer_view.php?id=<?= urlencode($id) ?>" class="btn btn-secondary btn-sm">Cancel</a>
        </form>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
