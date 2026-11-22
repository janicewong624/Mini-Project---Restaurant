<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/TableService.php';

$service = new TableService();
$id      = $_GET['id'] ?? $_POST['id'] ?? '';
$table   = $service->getById($id);
$error   = '';

if ($table === null) {
    exit("Table not found. <a href='table_list.php'>Back</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $service->delete($id);
    if ($result['success']) {
        order_flash_set("Table " . $table['tableNumber'] . " has been deleted.");
        header("Location: table_list.php");
        exit;
    }
    $error = implode(' ', $result['errors']);
}

$pageTitle = 'Delete Table';
$bodyClass = 'center-page order-page';
$navActive = 'tables';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Delete Table</h1>
<p class="page-sub">This action cannot be undone.</p>

<div class="panel">
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $e($error) ?></div>
    <?php endif; ?>

    <p><strong>Table <?= $e($table['tableNumber']) ?></strong></p>
    <p style="margin:10px 0 24px;"><?= (int)$table['seatingCapacity'] ?> seats · <?= $e($table['tableStatus']) ?></p>

    <form method="post">
        <input type="hidden" name="id" value="<?= $e($id) ?>">
        <button type="submit" class="btn btn-gold btn-sm" style="background:#c0504d;">Yes, delete</button>
        <a href="table_list.php" class="btn btn-secondary btn-sm">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
