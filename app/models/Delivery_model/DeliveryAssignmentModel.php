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
    // Returns one row per (order_id, seller_id) pair that:
    //   - has at least one item shipped by that seller, and
    //   - does NOT currently have an active (assigned/picked_up/in_transit/delivered)
    //     assignment. Failed assignments DO NOT block re-dispatch — the manager
    //     can pick a new agent here instead of through the Failed page.
    $stmt = mysqli_prepare($conn,
        "SELECT o.id, o.customer_id AS user_id, o.total_amount, o.status, o.created_at,
                o.zone_id, z.zone_name, z.delivery_fee,
                oi.seller_id, s.shop_name,
                SUM(oi.unit_price * oi.quantity) AS seller_subtotal,
                COUNT(*) AS item_count
         FROM orders o
         JOIN order_items     oi ON oi.order_id = o.id
         JOIN sellers         s  ON s.id = oi.seller_id
         LEFT JOIN delivery_zones z ON o.zone_id = z.id
         WHERE oi.item_status = 'shipped'
           AND o.status NOT IN ('cancelled','returned')
           AND NOT EXISTS (
                 SELECT 1 FROM delivery_assignments da
                 WHERE da.order_id = o.id
                   AND da.seller_id = oi.seller_id
                   AND da.status IN ('assigned','picked_up','in_transit','delivered')
             )
         GROUP BY o.id, oi.seller_id, o.customer_id, o.total_amount, o.status,
                  o.created_at, o.zone_id, z.zone_name, z.delivery_fee, s.shop_name
         ORDER BY o.created_at ASC, oi.seller_id ASC");
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
function delivery_assignment_insert($conn, $order_id, $agent_id, $zone_id, $seller_id = null) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO delivery_assignments (order_id, seller_id, agent_id, zone_id, status)
         VALUES (?, ?, ?, ?, 'assigned')");
    mysqli_stmt_bind_param($stmt, 'iiii', $order_id, $seller_id, $agent_id, $zone_id);
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
                da.seller_id,
                da.status,
                da.assigned_at,
                da.updated_at,
                ag.name         AS agent_name,
                ag.phone        AS agent_phone,
                ag.vehicle_type,
                dz.zone_name,
                o.total_amount,
                s.shop_name
         FROM delivery_assignments da
         JOIN delivery_agents ag ON ag.id = da.agent_id
         JOIN delivery_zones  dz ON dz.id = da.zone_id
         JOIN orders          o  ON o.id  = da.order_id
         LEFT JOIN sellers    s  ON s.id  = da.seller_id
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

    // Look up the order + seller this assignment belongs to.
    $stmt = mysqli_prepare($conn, "SELECT order_id, seller_id FROM delivery_assignments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if (!$row) { return; }
    $order_id  = (int)$row['order_id'];
    $seller_id = isset($row['seller_id']) ? (int)$row['seller_id'] : 0;

    // Propagate "delivered" only to THIS seller's items in the order. The
    // parent order.status is then recomputed from all items via the rollup
    // helper, so it only flips to 'delivered' when every seller's shipment
    // has arrived.
    if ($status === 'delivered') {
        if ($seller_id > 0) {
            $stmt = mysqli_prepare($conn,
                "UPDATE order_items SET item_status = 'delivered'
                 WHERE order_id = ? AND seller_id = ?
                   AND item_status IN ('pending','confirmed','shipped')");
            mysqli_stmt_bind_param($stmt, 'ii', $order_id, $seller_id);
        } else {
            $stmt = mysqli_prepare($conn,
                "UPDATE order_items SET item_status = 'delivered'
                 WHERE order_id = ? AND item_status IN ('pending','confirmed','shipped')");
            mysqli_stmt_bind_param($stmt, 'i', $order_id);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        order_recompute_status($conn, $order_id);
    }
    // picked_up / in_transit / assigned / failed leave the order's overall
    // status untouched. Failure is surfaced via the Failed Deliveries page
    // AND the dispatch page (the order/seller pair re-appears for reassignment).
}

// ----------------------------------------------------------
// Failed deliveries list
// ----------------------------------------------------------
function delivery_failed_list($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT da.id AS assignment_id,
                da.order_id,
                da.seller_id,
                da.status,
                da.failure_reason,
                da.assigned_at,
                da.updated_at,
                ag.name      AS agent_name,
                dz.zone_name,
                o.total_amount,
                s.shop_name
         FROM delivery_assignments da
         JOIN delivery_agents ag ON ag.id = da.agent_id
         JOIN delivery_zones  dz ON dz.id = da.zone_id
         LEFT JOIN sellers    s  ON s.id  = da.seller_id
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
