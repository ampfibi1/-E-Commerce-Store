<?php
session_start();

require_once '../../model/connection.php';
require_once '../../model/adminModel/ordersModel.php';

$status = $_POST['status'] ?? '';
$date_from = $_POST['date_from'] ?? '';
$date_to = $_POST['date_to'] ?? '';
$seller = $_POST['seller'] ?? '';
$customer = $_POST['customer'] ?? '';

$conn = conn_open();
$orders = getAllOrders($status, $date_from,$date_to,$seller,$customer, $conn);
$sellers = getSellersForOrderFilter($conn);
$customers = getCustomersForOrderFilter($conn);
conn_close($conn);


$_SESSION['orders'] = $orders;
$_SESSION['sellers_order'] = $sellers;
$_SESSION['customers_order'] = $customers;
$_SESSION['status'] = $status;
$_SESSION['date_from'] = $date_from;
$_SESSION['date_to'] = $date_to;
$_SESSION['seller'] = $seller;
$_SESSION['customer'] = $customer;

header("Location: ../../views/admin/orders.php");
exit();
?>