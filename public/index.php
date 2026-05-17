<?php
session_start();

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/db.php';

// Load all models (recurse into role subfolders: SharedModel/, Admin_model/, etc.)
foreach (glob(APP . '/models/*.php') as $mf)        require_once $mf;
foreach (glob(APP . '/models/*/*.php') as $mf)      require_once $mf;

// Controller slug → class name + file map
$controller_class_map = array(
    'auth'     => array('AuthController',     APP . '/controllers/SharedController/AuthController.php'),
    'info'     => array('InfoController',     APP . '/controllers/SharedController/InfoController.php'),
    'customer' => array('CustomerController', APP . '/controllers/Customer_controller/CustomerController.php'),
    'seller'   => array('SellerController',   APP . '/controllers/Seller_controller/SellerController.php'),
    'delivery' => array('DeliveryController', APP . '/controllers/Delivery_controller/DeliveryController.php'),
    'admin'    => array('AdminController',    APP . '/controllers/Admin_controller/AdminController.php'),
);

$c = isset($_GET['c']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['c']) : 'auth';
$a = isset($_GET['a']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['a']) : 'login';

// Default routing: if logged in, redirect to role dashboard; else login
if ($c === 'auth' && $a === 'login' && isset($_SESSION['uid'])) {
    $role = $_SESSION['role'];
    if ($role === 'customer')          redirect(BASE_URL . '?c=customer&a=dashboard');
    if ($role === 'seller')            redirect(BASE_URL . '?c=seller&a=dashboard');
    if ($role === 'delivery_manager')  redirect(BASE_URL . '?c=delivery&a=dashboard');
    // admin: legacy direct-file pages — leave on login for now
}

$controller_file = isset($controller_class_map[$c]) ? $controller_class_map[$c][1] : '';

if ($controller_file && file_exists($controller_file)) {
    require_once $controller_file;
    $class = $controller_class_map[$c][0];
    if (class_exists($class)) {
        $obj = new $class($conn);
        if (method_exists($obj, $a) || method_exists($obj, '__call')) {
            $obj->$a();
        } else {
            http_response_code(404);
            include APP . '/views/404.php';
        }
    } else {
        http_response_code(404);
        include APP . '/views/404.php';
    }
} else {
    http_response_code(404);
    include APP . '/views/404.php';
}
