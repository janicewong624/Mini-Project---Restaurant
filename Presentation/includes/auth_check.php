<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function requireRole(string $role): void {
    if (empty($_SESSION['userID'])) {
        header("Location: ../auth/login.php");
        exit;
    }
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit("Access denied.");
    }
}