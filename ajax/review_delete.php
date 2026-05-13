<?php
// AJAX endpoint: POST {review_id}
// Require customer role
// Call review_delete($conn, $review_id, $_SESSION['uid'])
// Return JSON {success, message}

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/db.php';
require_once APP . '/models/ReviewModel.php';

// Require logged-in customer
if (!isset($_SESSION['uid']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Please log in as a customer.',
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

$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
$uid       = (int)$_SESSION['uid'];

if ($review_id <= 0) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid review ID.',
    ));
    exit;
}

$result = review_delete($conn, $review_id, $uid);

if ($result) {
    echo json_encode(array(
        'success' => true,
        'message' => 'Review deleted.',
    ));
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Could not delete review. It may not exist or belong to another user.',
    ));
}
