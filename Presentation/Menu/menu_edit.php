<?php
require_once __DIR__ . '/../includes/customer_admin_guard.php';
require_once __DIR__ . '/../../Business/MenuService.php';

$errors = [];
$csrfToken = customer_admin_csrf_token();
$service = null;
$id = customer_admin_value($_GET, 'id');
try {
    $service = new MenuService();
    $item = $service->getById($id);
} catch (PDOException $error) {
    $service = null;
    $item = null;
    $errors[] = 'Menu management is unavailable. Check that MySQL is running and apply Database/menu_setup.sql.';
}

if ($service !== null && $item === null) {
    http_response_code(404);
} elseif ($service !== null && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    if (!customer_admin_csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        try {
            $result = $service->save($id, $_POST, $_FILES['image'] ?? []);
            if ($result['success']) {
                $_SESSION['menu_flash'] = 'Menu item updated.';
                if (($result['syncWarning'] ?? '') !== '') {
                    $_SESSION['menu_error'] = $result['syncWarning'];
                }
                header('Location: index.php');
                exit;
            }
            $errors = $result['errors'];
        } catch (PDOException $error) {
            error_log('Menu item update failed: ' . $error->getMessage());
            $errors[] = 'The menu item could not be updated. Check the menu table setup and try again.';
        }
    }

    $item['foodName'] = customer_admin_value($_POST, 'foodName');
    $item['description'] = customer_admin_value($_POST, 'description');
    $item['price'] = customer_admin_value($_POST, 'price');
    $item['category'] = customer_admin_value($_POST, 'category');
    $item['isPopular'] = customer_admin_value($_POST, 'isPopular') === '1';
}

$pageTitle = $item === null ? 'Menu Item Not Found' : 'Edit Menu Item';
$bodyClass = 'feedback-page menu-admin-page';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($service === null): ?>
    <h1 class="page-title">Menu Management Unavailable</h1>
    <p class="page-sub">Please check the database connection and migration.</p>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= $e($error) ?></div>
    <?php endforeach; ?>
    <a href="index.php" class="btn btn-secondary btn-sm">Back to Menu</a>
<?php elseif ($item === null): ?>
    <h1 class="page-title">Menu Item Not Found</h1>
    <p class="page-sub">This item does not exist or is no longer available.</p>
    <a href="index.php" class="btn btn-secondary btn-sm">Back to Menu</a>
<?php else: ?>
    <h1 class="page-title">Edit Menu Item</h1>
    <p class="page-sub">Update the details, photo, or home page placement.</p>

    <div class="panel menu-admin-panel">
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= $e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" enctype="multipart/form-data"
              onsubmit="return confirm('Save changes to this menu item?');">
            <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
            <?php $buttonText = 'Save Changes'; include __DIR__ . '/menu_form_fields.php'; ?>
        </form>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
