<?php
session_start();

require_once '../../model/adminModel/dashboardModel.php';
require_once '../../model/connection.php';

$conn = conn_open();
$data = getDashboardData($conn);
conn_close($conn);

$_SESSION['dashboard'] = $data;

header("Location: ../../views/admin/dashboard.php");
exit();
?>