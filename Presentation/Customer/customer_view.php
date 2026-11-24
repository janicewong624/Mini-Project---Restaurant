<?php
require_once __DIR__ . '/../includes/customer_admin_guard.php';
require_once __DIR__ . '/../../Business/CustomerService.php';

$id = customer_admin_value($_GET, 'id');
$customer = (new CustomerService())->getById($id);

$pageTitle = 'View Customer';
$bodyClass = 'feedback-page';
$navActive = 'customers';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($customer === null): ?>
    <h1 class="page-title">Customer Not Found</h1>
    <p class="page-sub">This customer does not exist or is no longer available.</p>
    <a href="index.php" class="btn btn-secondary btn-sm">Back to Customers</a>
<?php else: ?>
    <?php $registeredAt = strtotime((string)($customer['created_at'] ?? '')); ?>
    <h1 class="page-title">Customer Details</h1>
    <p class="page-sub">Customer ID: <?= $e($customer['userID']) ?></p>

    <div class="panel">
        <div class="form-group"><label class="field">Full Name</label><p><?= $e($customer['name']) ?></p></div>
        <div class="form-group"><label class="field">Email Address</label><p><?= $e($customer['email']) ?></p></div>
        <div class="form-group"><label class="field">Phone Number</label><p><?= $e($customer['contactNumber']) ?></p></div>
        <div class="form-group"><label class="field">Registered</label><p><?= $registeredAt ? date('d M Y, H:i', $registeredAt) : '-' ?></p></div>
        <a href="customer_edit.php?id=<?= urlencode($customer['userID']) ?>" class="btn btn-gold btn-sm">Edit Customer</a>
        <a href="index.php" class="btn btn-secondary btn-sm">Back</a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
