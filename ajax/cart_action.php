<?php
// AJAX endpoint: POST {action:'add'|'update'|'remove', product_id, qty}
// Returns JSON: {success, count, subtotal, message}

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/db.php';
foreach (glob(APP . '/models/*.php') as $_mf) require_once $_mf;
foreach (glob(APP . '/models/*/*.php') as $_mf) require_once $_mf;
// Require logged-in customer
if (!isset($_SESSION['uid']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(array(
        'success'  => false,
        'count'    => 0,
        'subtotal' => 0,
        'message'  => 'Please log in as a customer to manage your cart.',
    ));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array(
        'success'  => false,
        'count'    => 0,
        'subtotal' => 0,
        'message'  => 'Method not allowed.',
    ));
    exit;
}

$action     = isset($_POST['action'])     ? trim($_POST['action'])     : '';
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty        = isset($_POST['qty'])        ? (int)$_POST['qty']        : 1;

if (!in_array($action, array('add', 'update', 'remove'))) {
    echo json_encode(array(
        'success'  => false,
        'count'    => cart_count(),
        'subtotal' => cart_subtotal($conn),
        'message'  => 'Invalid action.',
    ));
    exit;
}

if ($product_id <= 0) {
    echo json_encode(array(
        'success'  => false,
        'count'    => cart_count(),
        'subtotal' => cart_subtotal($conn),
        'message'  => 'Invalid product.',
    ));
    exit;
}

if ($action === 'add') {
    $product = product_get_by_id($conn, $product_id);

    if (!$product) {
        echo json_encode(array(
            'success'  => false,
            'count'    => cart_count(),
            'subtotal' => cart_subtotal($conn),
            'message'  => 'Product does not exist.',
        ));
        exit;
    }

    if (!(int)$product['is_available']) {
        echo json_encode(array(
            'success'  => false,
            'count'    => cart_count(),
            'subtotal' => cart_subtotal($conn),
            'message'  => 'This product is currently unavailable.',
        ));
        exit;
    }

    if ((int)$product['stock_qty'] <= 0) {
        echo json_encode(array(
            'success'  => false,
            'count'    => cart_count(),
            'subtotal' => cart_subtotal($conn),
            'message'  => 'This product is out of stock.',
        ));
        exit;
    }

    $add_qty = ($qty > 0) ? $qty : 1;
    cart_add($product_id, $add_qty);

    echo json_encode(array(
        'success'  => true,
        'count'    => cart_count(),
        'subtotal' => cart_subtotal($conn),
        'message'  => 'Item added to cart.',
    ));
    exit;
}

if ($action === 'update') {
    if ($qty <= 0) {
        echo json_encode(array(
            'success'  => false,
            'count'    => cart_count(),
            'subtotal' => cart_subtotal($conn),
            'message'  => 'Quantity must be at least 1. Use remove to delete the item.',
        ));
        exit;
    }

    // Validate stock is sufficient
    $product = product_get_by_id($conn, $product_id);
    if ($product && (int)$product['stock_qty'] < $qty) {
        echo json_encode(array(
            'success'  => false,
            'count'    => cart_count(),
            'subtotal' => cart_subtotal($conn),
            'message'  => 'Only ' . (int)$product['stock_qty'] . ' units available.',
        ));
        exit;
    }

    cart_update($product_id, $qty);

    echo json_encode(array(
        'success'  => true,
        'count'    => cart_count(),
        'subtotal' => cart_subtotal($conn),
        'message'  => 'Cart updated.',
    ));
    exit;
}

if ($action === 'remove') {
    cart_remove($product_id);

    echo json_encode(array(
        'success'  => true,
        'count'    => cart_count(),
        'subtotal' => cart_subtotal($conn),
        'message'  => 'Item removed from cart.',
    ));
    exit;
}
