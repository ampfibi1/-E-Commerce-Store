<?php
session_start();

require_once '../../model/adminModel/dashboardModel.php';

$data = withoutDB();

// store in session (temporary cache)
$_SESSION['dashboard'] = $data;

// redirect ONLY (no echo)
header("Location: ../../views/admin/dashboard.php");
exit();
?>