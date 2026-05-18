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

function dispute_seller_for_order($conn, $order_id, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT oi.seller_id
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE oi.order_id = ? AND o.customer_id = ?
         LIMIT 1"
    );
    if (!$stmt) { return 0; }
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $customer_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? (int)$row['seller_id'] : 0;
}
