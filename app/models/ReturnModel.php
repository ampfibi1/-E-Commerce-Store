<?php

function return_create($conn, $data) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO return_requests (order_id, order_item_id, customer_id, reason)
         VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiis",
        $data['order_id'],
        $data['order_item_id'],
        $data['customer_id'],
        $data['reason']
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);

    if ($id) {
        $upd = mysqli_prepare($conn,
            "UPDATE orders SET status = 'return_requested' WHERE id = ?"
        );
        mysqli_stmt_bind_param($upd, "i", $data['order_id']);
        mysqli_stmt_execute($upd);
        mysqli_stmt_close($upd);
    }

    return $id;
}

function return_get_by_customer($conn, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT rr.*, oi.quantity, oi.unit_price, oi.item_status,
                p.name AS product_name, p.primary_image_path
         FROM return_requests rr
         JOIN order_items oi ON rr.order_item_id = oi.id
         JOIN products    p  ON oi.product_id    = p.id
         WHERE rr.customer_id = ?
         ORDER BY rr.created_at DESC"
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

function return_get_by_seller($conn, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT rr.*, p.name AS product_name, p.primary_image_path,
                oi.quantity, oi.unit_price, oi.item_status,
                u.name AS customer_name, u.email AS customer_email
         FROM return_requests rr
         JOIN order_items oi ON rr.order_item_id = oi.id AND oi.seller_id = ?
         JOIN products    p  ON oi.product_id    = p.id
         JOIN users       u  ON rr.customer_id   = u.id
         ORDER BY rr.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function return_update_status($conn, $id, $status, $seller_id) {
    // Verify the seller owns the order_item linked to this return request
    $chk = mysqli_prepare($conn,
        "SELECT rr.id FROM return_requests rr
         JOIN order_items oi ON rr.order_item_id = oi.id AND oi.seller_id = ?
         WHERE rr.id = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($chk, "ii", $seller_id, $id);
    mysqli_stmt_execute($chk);
    $chk_result = mysqli_stmt_get_result($chk);
    $found      = mysqli_fetch_assoc($chk_result);
    mysqli_stmt_close($chk);

    if (!$found) {
        return false;
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE return_requests SET status = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
