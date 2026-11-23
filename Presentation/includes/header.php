<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$restaurantName = 'DineNest Restaurant';          
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? '';
$e = fn($s) => htmlspecialchars((string)$s);

$base = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/') . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($pageTitle ?? $restaurantName) ?></title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../assets/css/style.css') ?>">
</head>
<body class="inner-page <?= $e($bodyClass ?? '') ?>">

<?php include __DIR__ . '/navbar.php'; ?>

<main class="page-main">
    <div class="container">