<?php
require_once __DIR__ . '/../includes/order_guard.php';
$user = guard_admin();
require_once __DIR__ . '/../../Business/TableService.php';

$service  = new TableService();
$errors   = [];
$number   = '';
$capacity = '';
$status   = 'Available';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number   = $_POST['tableNumber'] ?? '';
    $capacity = $_POST['seatingCapacity'] ?? '';
    $status   = $_POST['tableStatus'] ?? '';
    $result   = $service->add($number, $capacity, $status);

    if ($result['success']) {
        order_flash_set("Table " . trim($number) . " has been added.");
        header("Location: table_list.php");
        exit;
    }
    $errors = $result['errors'];
}

$pageTitle = 'Add Table';
$bodyClass = 'feedback-page order-page';
$navActive = 'tables';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/order_head.php';
?>

<h1 class="page-title">Add Table</h1>
<p class="page-sub">Create a new restaurant table.</p>

<div class="panel">
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= $e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="form-group">
            <label class="field" for="tableNumber">Table Number</label>
            <input type="text" id="tableNumber" name="tableNumber" class="form-control" maxlength="10"
                   value="<?= $e($number) ?>" placeholder="e.g. 1">
        </div>

        <div class="form-group">
            <label class="field" for="seatingCapacity">Seating Capacity</label>
            <input type="number" id="seatingCapacity" name="seatingCapacity" class="form-control" min="1" max="20"
                   value="<?= $e($capacity) ?>" placeholder="1 - 20">
        </div>

        <div class="form-group">
            <label class="field" for="tableStatus">Status</label>
            <select id="tableStatus" name="tableStatus" class="form-control">
                <?php foreach (TableService::STATUSES as $s): ?>
                    <option value="<?= $e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="ob-hint">Available / Occupied are updated automatically from active dine-in orders.</small>
        </div>

        <button type="submit" class="btn btn-gold btn-sm">Add Table</button>
        <a href="table_list.php" class="btn btn-secondary btn-sm">Back</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
