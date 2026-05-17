<?php
// AJAX endpoint: POST {coupon_id}
// Require seller role
// Verify coupon belongs to this seller, then toggle is_active
// Return JSON {success, is_active}

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/db.php';
require_once APP . '/models/CouponModel.php';

// Require logged-in seller
if (!isset($_SESSION['uid']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    echo json_encode(array(
        'success'   => false,
        'is_active' => null,
        'message'   => 'Please log in as a seller.',
    ));
    exit;
}

if (!isset($_SESSION['sid']) || (int)$_SESSION['sid'] <= 0) {
    echo json_encode(array(
        'success'   => false,
        'is_active' => null,
        'message'   => 'Seller profile not found.',
    ));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array(
        'success'   => false,
        'is_active' => null,
        'message'   => 'Method not allowed.',
    ));
    exit;
}

$coupon_id = isset($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : 0;
$sid       = (int)$_SESSION['sid'];

if ($coupon_id <= 0) {
    echo json_encode(array(
        'success'   => false,
        'is_active' => null,
        'message'   => 'Invalid coupon ID.',
    ));
    exit;
}

// Verify ownership before toggling (inline query; no coupon_get_by_id in model)
$chk_stmt = mysqli_prepare($conn, "SELECT * FROM coupons WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($chk_stmt, "i", $coupon_id);
mysqli_stmt_execute($chk_stmt);
$chk_result = mysqli_stmt_get_result($chk_stmt);
$coupon     = mysqli_fetch_assoc($chk_result);
mysqli_stmt_close($chk_stmt);

if (!$coupon) {
    echo json_encode(array(
        'success'   => false,
        'is_active' => null,
        'message'   => 'Coupon not found.',
    ));
    exit;
}

if ((int)$coupon['seller_id'] !== $sid) {
    echo json_encode(array(
        'success'   => false,
        'is_active' => null,
        'message'   => 'Access denied. This coupon does not belong to your shop.',
    ));
    exit;
}

$new_state = coupon_toggle($conn, $coupon_id);

// coupon_toggle returns the new is_active value (0 or 1), or null on failure
if ($new_state === null) {
    echo json_encode(array(
        'success'   => false,
        'is_active' => (int)$coupon['is_active'],
        'message'   => 'Failed to toggle coupon status.',
    ));
    exit;
}

echo json_encode(array(
    'success'   => true,
    'is_active' => (int)$new_state,
    'message'   => ((int)$new_state === 1) ? 'Coupon activated.' : 'Coupon deactivated.',
));
