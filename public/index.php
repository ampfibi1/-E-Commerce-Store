<?php
session_start();

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/db.php';

// Load all models (recurse into role subfolders: SharedModel/, Admin_model/, etc.)
foreach (glob(APP . '/models/*.php') as $mf)        require_once $mf;
foreach (glob(APP . '/models/*/*.php') as $mf)      require_once $mf;

// Controller slug → file path. Each file defines procedural functions
// named "<slug>_<action>" (e.g. customer_cart, admin_dashboard, auth_login).
// No classes — class-based PHP is forbidden by the project's procedural-only rule.
$controller_file_map = array(
    'auth'     => APP . '/controllers/SharedController/AuthController.php',
    'info'     => APP . '/controllers/SharedController/InfoController.php',
    'customer' => APP . '/controllers/Customer_controller/CustomerController.php',
    'seller'   => APP . '/controllers/Seller_controller/SellerController.php',
    'delivery' => APP . '/controllers/Delivery_controller/DeliveryController.php',
    'admin'    => APP . '/controllers/Admin_controller/AdminController.php',
);

$c = isset($_GET['c']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['c']) : 'auth';
$a = isset($_GET['a']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['a']) : 'login';

// Default routing: if logged in, redirect to role dashboard; else login
if ($c === 'auth' && $a === 'login' && isset($_SESSION['uid'])) {
    $role = $_SESSION['role'];
    if ($role === 'customer')          redirect(BASE_URL . '?c=customer&a=dashboard');
    if ($role === 'seller')            redirect(BASE_URL . '?c=seller&a=dashboard');
    if ($role === 'delivery_manager')  redirect(BASE_URL . '?c=delivery&a=dashboard');
    if ($role === 'admin')             redirect(BASE_URL . '?c=admin&a=dashboard');
}

$controller_file = isset($controller_file_map[$c]) ? $controller_file_map[$c] : '';

if ($controller_file && file_exists($controller_file)) {
    require_once $controller_file;
    // Dispatch to the procedural function named "<slug>_<action>".
    // The DB connection ($conn) is passed in explicitly — no $this access.
    $fn = $c . '_' . $a;
    if (function_exists($fn)) {
        $fn($conn);
    } else {
        http_response_code(404);
        include APP . '/views/404.php';
    }
} else {
    http_response_code(404);
    include APP . '/views/404.php';
}
