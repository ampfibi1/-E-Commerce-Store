<?php
session_start();
$_SESSION['msg'] = '';
require_once '../model/connection.php';
require_once '../model/logModel.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $_SESSION['msg'] = '';

    //php validation
    if(empty($email) || empty($password)){
        $_SESSION['msg'] = 'Please fill in all fields.';
        header('Location: ../views/index.php');
        exit();
    }

    $conn = conn_open();
    $user = login($email, $password, $conn);
    conn_close($conn);

    if($user){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        if($user['role'] === 'admin') {
            header('Location: ../views/admin/dashboard.php');
            exit();
        }
        
    } else {
        $_SESSION['msg'] = 'Invalid email or password. Please try again.';
        header('Location: ../views/index.php');
        exit();
    }
}
