<?php

function dispute_get_by_customer($conn, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT d.*, s.shop_name, o.total_amount, o.created_at AS order_date
         FROM disputes d
         JOIN sellers s ON d.seller_id = s.id
         JOIN orders  o ON d.order_id  = o.id
         WHERE d.customer_id = ?
         ORDER BY d.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function dispute_create($conn, $data) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO disputes (customer_id, seller_id, order_id, description)
         VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiis",
        $data['customer_id'],
        $data['seller_id'],
        $data['order_id'],
        $data['description']
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function dispute_get_one_for_seller($conn, $id, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT d.*, u.name AS customer_name, u.email AS customer_email,
                o.total_amount, o.created_at AS order_date, o.shipping_address
         FROM disputes d
         JOIN users u ON d.customer_id = u.id
         JOIN orders o ON d.order_id = o.id
         WHERE d.id = ? AND d.seller_id = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $id, $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function dispute_get_by_seller($conn, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT d.*, u.name AS customer_name
         FROM disputes d
         JOIN users u ON d.customer_id = u.id
         WHERE d.seller_id = ?
         ORDER BY d.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    mysqli_stmt_close($stmt);
    return $rows;
}
