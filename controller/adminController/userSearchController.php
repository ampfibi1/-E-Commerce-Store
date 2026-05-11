<?php
require_once '../../model/connection.php';
require_once '../../model/adminModel/usersModel.php';

$data = [];

if (isset($_GET['q'])) {
    $search = $_GET['q'];
    $conn = conn_open();
    $data = getUserSuggestions($search, $conn);
    conn_close($conn);
}

echo json_encode($data);
?>