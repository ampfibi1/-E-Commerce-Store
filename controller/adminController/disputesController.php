<?php
session_start();

require_once '../../model/adminModel/disputesModel.php';
require_once '../../model/connection.php';


$action = $_GET['action'] ?? 'list';

$conn = conn_open();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resolve_dispute'])) {
    resolveDispute($_POST['id'], $_POST['note'],$conn);
}

$disputes = getAllDisputes($conn);
conn_close($conn);
$_SESSION['disputes'] = $disputes;


header("Location: ../../views/admin/disputes.php");
exit();
?>