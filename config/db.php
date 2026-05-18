<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce');

define('APP', dirname(__DIR__) . '/app');
define('ROOT', dirname(__DIR__));
// Auto-detect BASE_URL from the running request so the project works no
// matter what folder the repo lives in (e.g. /ecommerce/, /shophub/, or
// whatever name a teammate's zip extracted to).
if (!defined('BASE_URL')) {
    $_bu_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $_bu_host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $_bu_dir    = isset($_SERVER['SCRIPT_NAME']) ? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') : '';
    define('BASE_URL', $_bu_scheme . '://' . $_bu_host . $_bu_dir . '/');
}
define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('DB connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

function set_flash($type, $msg) {
    $_SESSION['flash'] = array('type' => $type, 'msg' => $msg);
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function require_login() {
    if (!isset($_SESSION['uid'])) {
        set_flash('error', 'Please log in to continue.');
        header('Location: ' . BASE_URL . '?c=auth&a=login');
        exit;
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        set_flash('error', 'Access denied.');
        header('Location: ' . BASE_URL);
        exit;
    }
}

function require_seller_approved($conn) {
    require_role('seller');
    $sid = isset($_SESSION['sid']) ? (int)$_SESSION['sid'] : 0;
    if (!$sid) {
        set_flash('error', 'Seller profile not found.');
        header('Location: ' . BASE_URL . '?c=auth&a=login');
        exit;
    }
    $stmt = mysqli_prepare($conn, 'SELECT is_approved FROM sellers WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $sid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if (!$row || !$row['is_approved']) {
        set_flash('info', 'Your seller account is pending admin approval.');
        header('Location: ' . BASE_URL . '?c=auth&a=pendingSeller');
        exit;
    }
}

function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}
