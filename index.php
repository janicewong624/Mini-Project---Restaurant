<?php
require_once __DIR__ . '/data/Database.php';

try {
    $pdo = Database::connect();
    echo "Database connected!<br>";
    echo "Next payment ID: " . Database::generateID($pdo, 'payment', 'paymentID', 'P', 4);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}