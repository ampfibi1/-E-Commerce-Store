<?php
// ============================================================
// app/models/DeliveryAssignmentModel.php
// Procedural model functions for delivery_assignments table
// Also contains dashboard count helpers
// ============================================================

// ----------------------------------------------------------
// Dashboard counts
// ----------------------------------------------------------

// Orders with status='shipped' and NOT yet assigned
function delivery_count_pending_dispatch($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM orders
         WHERE status = 'shipped'
         AND id NOT IN (SELECT order_id FROM delivery_assignments)");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int)$row['cnt'];
}

// Assignments that are assigned/picked_up/in_transit
function delivery_count_active($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM delivery_assignments
         WHERE status IN ('assigned','picked_up','in_transit')");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int)$row['cnt'];
}

// Delivered today
function delivery_count_delivered_today($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM delivery_assignments
         WHERE status = 'delivered'
         AND DATE(updated_at) = CURDATE()");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int)$row['cnt'];
}

// ----------------------------------------------------------
// Orders ready for dispatch (shipped + not assigned)
// ----------------------------------------------------------
function delivery_orders_ready_for_dispatch($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT o.id, o.customer_id AS user_id, o.total_amount, o.status, o.created_at
         FROM orders o
         WHERE o.status = 'shipped'
         AND o.id NOT IN (SELECT order_id FROM delivery_assignments)
         ORDER BY o.created_at ASC");
    mysqli_stmt_execute($stmt);
    $res    = mysqli_stmt_get_result($stmt);
    $orders = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $orders[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $orders;
}

// ----------------------------------------------------------
// Insert new assignment
// ----------------------------------------------------------
function delivery_assignment_insert($conn, $order_id, $agent_id, $zone_id) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO delivery_assignments (order_id, agent_id, zone_id, status)
         VALUES (?, ?, ?, 'assigned')");
    mysqli_stmt_bind_param($stmt, 'iii', $order_id, $agent_id, $zone_id);
    mysqli_stmt_execute($stmt);
    $new_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $new_id;
}

// ----------------------------------------------------------
// Active deliveries list (joined with agent and zone)
// ----------------------------------------------------------
function delivery_active_list($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT da.id AS assignment_id,
                da.order_id,
                da.status,
                da.assigned_at,
                da.updated_at,
                ag.name         AS agent_name,
                ag.phone        AS agent_phone,
                ag.vehicle_type,
                dz.zone_name,
                o.total_amount
         FROM delivery_assignments da
         JOIN delivery_agents ag ON ag.id = da.agent_id
         JOIN delivery_zones  dz ON dz.id = da.zone_id
         JOIN orders          o  ON o.id  = da.order_id
         WHERE da.status IN ('assigned','picked_up','in_transit')
         ORDER BY da.assigned_at DESC");
    mysqli_stmt_execute($stmt);
    $res         = mysqli_stmt_get_result($stmt);
    $deliveries  = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $deliveries[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $deliveries;
}

// ----------------------------------------------------------
// Update assignment status (with optional failure reason)
// ----------------------------------------------------------
function delivery_assignment_update_status($conn, $assignment_id, $status, $failure_reason) {
    $stmt = mysqli_prepare($conn,
        "UPDATE delivery_assignments
         SET status = ?, failure_reason = ?, updated_at = NOW()
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $failure_reason, $assignment_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ----------------------------------------------------------
// Failed deliveries list
// ----------------------------------------------------------
function delivery_failed_list($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT da.id AS assignment_id,
                da.order_id,
                da.status,
                da.failure_reason,
                da.assigned_at,
                da.updated_at,
                ag.name      AS agent_name,
                dz.zone_name,
                o.total_amount
         FROM delivery_assignments da
         JOIN delivery_agents ag ON ag.id = da.agent_id
         JOIN delivery_zones  dz ON dz.id = da.zone_id
         JOIN orders          o  ON o.id  = da.order_id
         WHERE da.status = 'failed'
         ORDER BY da.updated_at DESC");
    mysqli_stmt_execute($stmt);
    $res        = mysqli_stmt_get_result($stmt);
    $deliveries = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $deliveries[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $deliveries;
}

// ----------------------------------------------------------
// Reassign agent for a failed delivery
// ----------------------------------------------------------
function delivery_assignment_reassign($conn, $assignment_id, $agent_id) {
    $stmt = mysqli_prepare($conn,
        "UPDATE delivery_assignments
         SET agent_id = ?, status = 'assigned', failure_reason = NULL, updated_at = NOW()
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $agent_id, $assignment_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
