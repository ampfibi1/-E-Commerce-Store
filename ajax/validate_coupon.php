<?php
// AJAX endpoint: POST {coupon_code, seller_id, subtotal}
// Returns JSON: {valid, discount, message, coupon_id}

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/db.php';
foreach (glob(APP . '/models/*.php') as $_mf) require_once $_mf;
foreach (glob(APP . '/models/*/*.php') as $_mf) require_once $_mf;
// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array(
        'valid'      => false,
        'discount'   => 0,
        'message'    => 'Method not allowed.',
        'coupon_id'  => null,
    ));
    exit;
}

$coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
$seller_id   = isset($_POST['seller_id'])   ? (int)$_POST['seller_id']   : 0;
$subtotal    = isset($_POST['subtotal'])    ? (float)$_POST['subtotal']  : 0.0;

if ($coupon_code === '') {
    echo json_encode(array(
        'valid'     => false,
        'discount'  => 0,
        'message'   => 'Coupon code is required.',
        'coupon_id' => null,
    ));
    exit;
}

if ($seller_id <= 0) {
    echo json_encode(array(
        'valid'     => false,
        'discount'  => 0,
        'message'   => 'Seller ID is required.',
        'coupon_id' => null,
    ));
    exit;
}

if ($subtotal <= 0) {
    echo json_encode(array(
        'valid'     => false,
        'discount'  => 0,
        'message'   => 'Subtotal must be greater than 0.',
        'coupon_id' => null,
    ));
    exit;
}

$result = coupon_validate($conn, $coupon_code, $seller_id, $subtotal);

if (!$result || !$result['valid']) {
    $msg = isset($result['message']) ? $result['message'] : 'Invalid, expired, or already used coupon.';
    echo json_encode(array(
        'valid'     => false,
        'discount'  => 0,
        'message'   => $msg,
        'coupon_id' => null,
    ));
    exit;
}

$discount = round((float)$result['discount'], 2);

echo json_encode(array(
    'valid'     => true,
    'discount'  => $discount,
    'message'   => isset($result['message']) ? $result['message'] : 'Coupon applied! You save ' . number_format($discount, 2) . '.',
    'coupon_id' => $result['coupon_id'],
));
