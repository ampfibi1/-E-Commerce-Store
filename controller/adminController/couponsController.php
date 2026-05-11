<?php
session_start();
require_once '../../model/adminModel/couponsModel.php';
require_once '../../model/connection.php';

$conn = conn_open();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_coupon'])) {
        addPlatformCoupon($_POST['code'],$_POST['discount_pct'], $_POST['max_uses'],$_POST['valid_until'],$conn);
    } elseif (isset($_POST['deactivate'])) {
        deactivateCoupon($_POST['id'],$conn);
    }
}
$coupons = getPlatformCoupons($conn);
conn_close($conn);

$_SESSION['coupons'] = $coupons;

header("Location: ../../views/admin/coupons.php");
exit();
?>