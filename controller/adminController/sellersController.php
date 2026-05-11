<?php
session_start();

require_once '../../model/adminModel/sellersModel.php';
require_once '../../model/connection.php';

$conn = conn_open();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $sellerId = $_GET['id'];
    switch ($action) {
        case 'approve':
            approveSeller($sellerId, $conn);
            break;

        case 'reject':
            rejectSeller($sellerId, $conn);
            break;

        case 'suspend':
            suspendSeller($sellerId, $conn);
            break;

        case 'reactivate':
            reactivateSeller($sellerId, $conn);
            break;
    }

    echo "success";
}

$sellers = getAllSellers($conn);
conn_close($conn);

$_SESSION['sellers'] = $sellers;

header("Location: ../../views/admin/sellers.php");
exit();
    header("Location: ../../views/admin/sellers.php");
    exit();
?>