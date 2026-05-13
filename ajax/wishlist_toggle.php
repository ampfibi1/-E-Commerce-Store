<?php
// AJAX endpoint: POST {product_id}
// Returns JSON: {success, in_wishlist, message}

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/db.php';
require_once APP . '/models/WishlistModel.php';

// Require logged-in customer
if (!isset($_SESSION['uid']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(array(
        'success'    => false,
        'in_wishlist'=> false,
        'message'    => 'Please log in as a customer to use the wishlist.',
    ));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array(
        'success'    => false,
        'in_wishlist'=> false,
        'message'    => 'Method not allowed.',
    ));
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$uid        = (int)$_SESSION['uid'];

if ($product_id <= 0) {
    echo json_encode(array(
        'success'    => false,
        'in_wishlist'=> false,
        'message'    => 'Invalid product.',
    ));
    exit;
}

$currently_in = wishlist_is_in($conn, $uid, $product_id);

if ($currently_in) {
    // Remove from wishlist
    wishlist_remove($conn, $uid, $product_id);
    echo json_encode(array(
        'success'    => true,
        'in_wishlist'=> false,
        'message'    => 'Removed from wishlist.',
    ));
} else {
    // Add to wishlist
    wishlist_add($conn, $uid, $product_id);
    echo json_encode(array(
        'success'    => true,
        'in_wishlist'=> true,
        'message'    => 'Added to wishlist.',
    ));
}
