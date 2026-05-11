<?php
session_start();
require_once '../../model/adminModel/analyticsModel.php';
require_once '../../model/connection.php';

$conn = conn_open();
$data = getAnalyticsData($conn);
conn_close($conn);

$_SESSION['analytics'] = $data;

header("Location: ../../views/admin/analytics.php");
exit();
?>