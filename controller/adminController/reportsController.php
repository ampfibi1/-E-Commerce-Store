<?php
session_start();
require_once '../../model/adminModel/reportsModel.php';
require_once '../../model/connection.php';

$month = date('Y-m');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month = $_POST['month'] ?? date('Y-m');
}

$conn = conn_open();
$report = generateMonthlyReport($month, $conn);
conn_close($conn);

$_SESSION['report'] = $report;
$_SESSION['report_month'] = $month;

header("Location: ../../views/admin/reports.php");
exit();
?>