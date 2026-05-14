<?php
session_start();

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/db.php';

// Load all models
$model_files = glob(APP . '/models/*.php');
foreach ($model_files as $mf) {
    require_once $mf;
}

$c = isset($_GET['c']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['c']) : 'auth';
$a = isset($_GET['a']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['a']) : 'login';

// Default routing: if logged in, redirect to role dashboard; else login
if ($c === 'auth' && $a === 'login' && isset($_SESSION['uid'])) {
    $role = $_SESSION['role'];
    if ($role === 'customer') redirect(BASE_URL . '?c=customer&a=dashboard');
    if ($role === 'seller')   redirect(BASE_URL . '?c=seller&a=dashboard');
    // other roles: just go to login
}

$controller_file = APP . '/controllers/' . ucfirst($c) . 'Controller.php';

if (file_exists($controller_file)) {
    require_once $controller_file;
    $class = ucfirst($c) . 'Controller';
    if (class_exists($class)) {
        $obj = new $class($conn);
        if (method_exists($obj, $a)) {
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
