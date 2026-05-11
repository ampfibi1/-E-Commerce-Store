<?php
session_start();

require_once '../../model/adminModel/usersModel.php';
require_once '../../model/connection.php';

$conn = conn_open();


$action = $_GET['action'] ?? '';

if ($action == 'deactivate' && isset($_GET['id'])) {
    deactivateUser($_GET['id'], $conn);
}

if ($action == 'reactivate' && isset($_GET['id'])) {
    reactivateUser($_GET['id'], $conn);
}

$search = $_POST['search'] ?? '';
$role = $_POST['role'] ?? '';

$users = getAllUsers($search, $role, $conn);

conn_close($conn);

$_SESSION['users'] = $users;
$_SESSION['search'] = $search;
$_SESSION['role'] = $role;

header("Location: ../../views/admin/users.php");
exit();
?>