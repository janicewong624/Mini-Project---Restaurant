<?php
require_once __DIR__ . '/../includes/customer_admin_guard.php';
require_once __DIR__ . '/../../Business/MenuService.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!customer_admin_csrf_valid()) {
    $_SESSION['menu_error'] = 'Your session expired. Please try again.';
    header('Location: index.php');
    exit;
}

$id = customer_admin_value($_POST, 'id');
try {
    $service = new MenuService();
    $result = $service->delete($id);
    if ($result['success']) {
        $_SESSION['menu_flash'] = 'Menu item deleted.';
        if (($result['syncWarning'] ?? '') !== '') {
            $_SESSION['menu_error'] = $result['syncWarning'];
        }
    } else {
        $_SESSION['menu_error'] = implode(' ', $result['errors']);
    }
} catch (PDOException $error) {
    $_SESSION['menu_error'] = 'The menu item could not be deleted. Check the database connection or whether it is used by an order.';
}

header('Location: index.php');
exit;
