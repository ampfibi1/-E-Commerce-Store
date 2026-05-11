<?php
session_start();

require_once '../../model/adminModel/settingsModel.php';
require_once '../../model/connection.php';

$conn = conn_open();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_commission'])) {
    updateCommission($_POST['seller_id'], $_POST['commission_rate'],$conn);
}

$sellers = getSellersForCommission($conn);
conn_close($conn);

$_SESSION['commission_sellers'] = $sellers;

header("Location: ../../views/admin/settings.php");
exit();
?>