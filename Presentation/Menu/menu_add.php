<?php
require_once __DIR__ . '/../includes/customer_admin_guard.php';
require_once __DIR__ . '/../../Business/MenuService.php';

$errors = [];
$csrfToken = customer_admin_csrf_token();
$service = null;
try {
    $service = new MenuService();
} catch (PDOException $error) {
    $errors[] = 'Menu management is unavailable. Check that MySQL is running and apply Database/menu_setup.sql.';
}
$item = [
    'foodName' => customer_admin_value($_POST, 'foodName'),
    'description' => customer_admin_value($_POST, 'description'),
    'price' => customer_admin_value($_POST, 'price'),
    'category' => customer_admin_value($_POST, 'category'),
    'image' => '',
    'isPopular' => customer_admin_value($_POST, 'isPopular') === '1',
];

if ($service !== null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!customer_admin_csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        try {
            $result = $service->save(null, $_POST, $_FILES['image'] ?? []);
            if ($result['success']) {
                $_SESSION['menu_flash'] = 'Menu item added.';
                if (($result['syncWarning'] ?? '') !== '') {
                    $_SESSION['menu_error'] = $result['syncWarning'];
                }
                header('Location: index.php');
                exit;
            }
            $errors = $result['errors'];
        } catch (PDOException $error) {
            error_log('Menu item insert failed: ' . $error->getMessage());
            $errors[] = 'The menu item could not be saved. Check the menu table setup and try again.';
        }
    }
}

$pageTitle = 'Add Menu Item';
$bodyClass = 'feedback-page menu-admin-page';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Add Menu Item</h1>
<p class="page-sub">Add a dish or drink to your restaurant menu.</p>

<div class="panel menu-admin-panel">
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= $e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
        <?php $buttonText = 'Add Menu Item'; include __DIR__ . '/menu_form_fields.php'; ?>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
