<?php
// AJAX endpoint: GET ?order_id=X
// Returns JSON: {status, items:[{id, item_status, tracking_note, product_name}]}

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/db.php';
require_once APP . '/models/OrderModel.php';

// Require a logged-in user
if (!isset($_SESSION['uid'])) {
    echo json_encode(array(
        'error'  => true,
        'message'=> 'Unauthorised. Please log in.',
    ));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(array(
        'error'  => true,
        'message'=> 'Method not allowed.',
    ));
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    echo json_encode(array(
        'error'  => true,
        'message'=> 'Invalid order ID.',
    ));
    exit;
}

$uid   = (int)$_SESSION['uid'];
$order = order_get_by_id($conn, $order_id);

if (!$order) {
    echo json_encode(array(
        'error'  => true,
        'message'=> 'Order not found.',
    ));
    exit;
}

// Verify ownership: customer must own the order
if ((int)$order['customer_id'] !== $uid) {
    echo json_encode(array(
        'error'  => true,
        'message'=> 'Access denied.',
    ));
    exit;
}

$raw_items = order_get_items($conn, $order_id);
$items     = array();

foreach ($raw_items as $item) {
    $items[] = array(
        'id'            => (int)$item['id'],
        'item_status'   => $item['item_status'],
        'tracking_note' => $item['tracking_note'],
        'product_name'  => isset($item['product_name']) ? $item['product_name'] : '',
    );
}

echo json_encode(array(
    'error'  => false,
    'status' => $order['status'],
    'items'  => $items,
));
