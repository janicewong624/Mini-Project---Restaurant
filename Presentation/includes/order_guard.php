<?php
/**
 * Member B 的頁面（Order / Table / Report）共用的「登入檢查」。
 *
 * auth_check.php 目前是空的、login.php 也還沒做，所以先在這裡放一個簡單版本。
 * 等登入功能完成後：
 *   1. 把下面的 ORDER_DEV_MODE 改成 false
 *   2. （可選）把 guard_any / guard_customer / guard_admin 換成 auth_check.php 的寫法
 *
 * 需要的 session：$_SESSION['userID']、$_SESSION['name']、$_SESSION['role']（Customer / Administrator）
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// ★ true = 還沒有登入功能時，自動用測試帳號進入（頁面上方會出現黃色提示）。
// ★ 登入功能完成後一定要改成 false！
const ORDER_DEV_MODE = true;

/** 測試模式：沒登入時自動當資料庫中的第一位 Customer，網址加 ?as=admin 可切換成管理員 */
function order_dev_login(): void {
    if (!ORDER_DEV_MODE) return;

    $isDev = !empty($_SESSION['_dev']);
    if (!empty($_SESSION['role']) && !$isDev) return;   // 已經是真的登入，不要動

    $as = $_GET['as'] ?? ($isDev && ($_SESSION['role'] ?? '') === 'Administrator' ? 'admin' : 'customer');
    if ($as === 'admin') {
        $_SESSION['userID'] = 'A0001';
        $_SESSION['name']   = 'Dev Admin';
        $_SESSION['role']   = 'Administrator';
        $_SESSION['_dev']   = true;
    } elseif ($as === 'customer') {
        require_once __DIR__ . '/../../Data/UserData.php';
        $customers = (new UserData())->getCustomers();
        if (!$customers) order_deny('No customer account is available for the development login.');

        $_SESSION['userID'] = (string)$customers[0]['userID'];
        $_SESSION['name']   = $customers[0]['name'];
        $_SESSION['role']   = 'Customer';
        $_SESSION['_dev']   = true;
    }
}

function order_deny(string $message): void {
    http_response_code(403);
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Access denied</title></head>"
       . "<body style='font-family:sans-serif;padding:40px;'>"
       . "<h2>Access denied</h2><p>" . htmlspecialchars($message) . "</p>"
       . "<p><a href='../../index.php'>Back to home</a></p></body></html>";
    exit;
}

/** 必須已登入（任何角色）。回傳 ['role'=>..., 'userID'=>..., 'name'=>...] */
function guard_any(): array {
    order_dev_login();
    if (empty($_SESSION['role']) || empty($_SESSION['userID'])) {
        header('Location: ../auth/login.php');
        exit;
    }
    return [
        'role'   => $_SESSION['role'],
        'userID' => $_SESSION['userID'],
        'name'   => $_SESSION['name'] ?? '',
    ];
}

function guard_customer(): array {
    $u = guard_any();
    if ($u['role'] !== 'Customer') order_deny('This page is for customers only.');
    return $u;
}

function guard_admin(): array {
    $u = guard_any();
    if ($u['role'] !== 'Administrator') order_deny('This page is for administrators only.');
    return $u;
}

/** 一次性提示訊息（用 order_flash，避免跟 Member A 的 flash 混在一起） */
function order_flash_set(string $msg): void { $_SESSION['order_flash'] = $msg; }
function order_flash_get(): string {
    $m = $_SESSION['order_flash'] ?? '';
    unset($_SESSION['order_flash']);
    return $m;
}
