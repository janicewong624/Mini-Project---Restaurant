<?php
require_once __DIR__ . '/../includes/customer_admin_guard.php';
require_once __DIR__ . '/../../Business/CustomerService.php';

$service = new CustomerService();
$customers = $service->getAll();
$flash = $_SESSION['customer_flash'] ?? '';
unset($_SESSION['customer_flash']);

$pageTitle = 'Customers';
$bodyClass = 'feedback-page list-page';
$navActive = 'customers';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Customer Management</h1>
<p class="page-sub">View and manage registered customer accounts.</p>

<?php if ($flash !== ''): ?>
    <div class="alert alert-success"><?= $e($flash) ?></div>
<?php endif; ?>

<p style="margin-bottom:14px;"><a href="customer_add.php" class="btn btn-gold btn-sm">+ Add Customer</a></p>

<table class="table-clean">
    <thead>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php if (!$customers): ?>
        <tr><td colspan="6">No customers yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($customers as $customer): ?>
        <?php $registeredAt = strtotime((string)($customer['created_at'] ?? '')); ?>
        <tr>
            <td><?= $e($customer['userID']) ?></td>
            <td><?= $e($customer['name']) ?></td>
            <td><?= $e($customer['email']) ?></td>
            <td><?= $e($customer['contactNumber']) ?></td>
            <td><?= $registeredAt ? date('d M Y', $registeredAt) : '-' ?></td>
            <td class="actions">
                <a href="customer_view.php?id=<?= urlencode($customer['userID']) ?>">View</a>
                <a class="link-edit" href="customer_edit.php?id=<?= urlencode($customer['userID']) ?>">Edit</a>
                <a class="link-delete" href="customer_delete.php?id=<?= urlencode($customer['userID']) ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
