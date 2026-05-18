<?php
// ============================================================
// ajax/update_status.php
// AJAX endpoint: update delivery assignment status
// Returns JSON only. Uses XMLHttpRequest from validation.js.
// Role check: delivery_manager only.
// ============================================================

session_start();
header('Content-Type: application/json');

// Load DB + helpers (two levels up from ajax/)
require_once dirname(__DIR__) . '/config/db.php';
foreach (glob(APP . '/models/*.php') as $_mf) require_once $_mf;
foreach (glob(APP . '/models/*/*.php') as $_mf) require_once $_mf;
// --- Auth check ---
if (!isset($_SESSION['uid']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'delivery_manager') {
    echo json_encode(array('success' => false, 'message' => 'Login required.'));
    exit;
}

// --- Method check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Method not allowed.'));
    exit;
}

// --- Input ---
$assignment_id  = isset($_POST['assignment_id'])  ? (int)trim($_POST['assignment_id'])  : 0;
$status         = isset($_POST['status'])         ? trim($_POST['status'])         : '';
$failure_reason = isset($_POST['failure_reason']) ? trim($_POST['failure_reason']) : '';

// --- Validation ---
$valid_statuses = array('assigned','picked_up','in_transit','delivered','failed');

if ($assignment_id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid assignment ID.'));
    exit;
}

if (!in_array($status, $valid_statuses)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid status value.'));
    exit;
}

if ($status === 'failed' && $failure_reason === '') {
    echo json_encode(array('success' => false, 'message' => 'Failure reason is required.'));
    exit;
}

// --- Update DB (using prepared statement from model) ---
delivery_assignment_update_status($conn, $assignment_id, $status, $failure_reason);

echo json_encode(array(
    'success' => true,
    'message' => 'Status updated to: ' . $status,
    'status'  => $status,
));
exit;
