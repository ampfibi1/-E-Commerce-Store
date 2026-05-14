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
