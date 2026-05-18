<?php

function dispute_get_by_customer($conn, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT d.id, d.id AS dispute_id, d.customer_id, d.seller_id, d.order_id,
                d.description, d.status, d.admin_note,
                d.seller_response, d.seller_responded_at,
                d.created_at,
                s.shop_name
         FROM disputes d
         LEFT JOIN sellers s ON d.seller_id = s.id
         WHERE d.customer_id = ?
         ORDER BY d.created_at DESC"
    );
    if (!$stmt) { return array(); }
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function dispute_create($conn, $data) {
    $customer_id = (int)$data['customer_id'];
    $seller_id   = (int)$data['seller_id'];
    $order_id    = (int)$data['order_id'];
    $description = isset($data['description']) ? $data['description'] : '';

    $stmt = mysqli_prepare($conn,
        "INSERT INTO disputes (customer_id, seller_id, order_id, description, status)
         VALUES (?, ?, ?, ?, 'open')"
    );
    if (!$stmt) { return false; }
    mysqli_stmt_bind_param($stmt, "iiis", $customer_id, $seller_id, $order_id, $description);
    $ok = mysqli_stmt_execute($stmt);
    $new_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $ok ? $new_id : false;
}

// Returns the distinct sellers that appear in the given order's items, but
// only if the order belongs to $customer_id. Used to populate the seller
// dropdown on the customer dispute form so the customer picks the specific
// seller they're complaining about (an order with multiple sellers must
// not silently route the dispute to whichever seller MySQL returns first).
function dispute_sellers_for_order($conn, $order_id, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT DISTINCT oi.seller_id, s.shop_name
         FROM order_items oi
         JOIN orders  o ON o.id = oi.order_id
         JOIN sellers s ON s.id = oi.seller_id
         WHERE oi.order_id = ? AND o.customer_id = ?
         ORDER BY s.shop_name ASC"
    );
    if (!$stmt) { return array(); }
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $customer_id);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = $r;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// Verifies that $seller_id actually appears in $order_id and that the
// order belongs to $customer_id. Used to reject tampered POSTs where the
// customer changes the hidden seller_id to one who isn't in the order.
function dispute_seller_in_order($conn, $order_id, $customer_id, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT 1
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE oi.order_id = ? AND oi.seller_id = ? AND o.customer_id = ?
         LIMIT 1"
    );
    if (!$stmt) { return false; }
    mysqli_stmt_bind_param($stmt, "iii", $order_id, $seller_id, $customer_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $ok  = (bool)mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $ok;
}
