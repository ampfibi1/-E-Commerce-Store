<?php
// AJAX endpoint: POST {review_id, reply}
// Require seller role
// Call review_seller_reply($conn, $review_id, $reply, $_SESSION['sid'])
// Return JSON {success, message}

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/db.php';
foreach (glob(APP . '/models/*.php') as $_mf) require_once $_mf;
foreach (glob(APP . '/models/*/*.php') as $_mf) require_once $_mf;
// Require logged-in seller
if (!isset($_SESSION['uid']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Please log in as a seller.',
    ));
    exit;
}

if (!isset($_SESSION['sid']) || (int)$_SESSION['sid'] <= 0) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Seller profile not found.',
    ));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Method not allowed.',
    ));
    exit;
}

$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id']   : 0;
$reply     = isset($_POST['reply'])     ? trim($_POST['reply'])       : '';
$sid       = (int)$_SESSION['sid'];

if ($review_id <= 0) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid review ID.',
    ));
    exit;
}

if ($reply === '') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Reply text cannot be empty.',
    ));
    exit;
}

$result = review_seller_reply($conn, $review_id, $reply, $sid);

if ($result) {
    echo json_encode(array(
        'success' => true,
        'message' => 'Reply posted successfully.',
    ));
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Could not post reply. The review may not belong to your shop.',
    ));
}
