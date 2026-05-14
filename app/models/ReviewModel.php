<?php

function review_get_by_product($conn, $product_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT r.*, u.name AS customer_name, u.profile_pic AS customer_pic
         FROM reviews r
         JOIN users u ON r.customer_id = u.id
         WHERE r.product_id = ?
         ORDER BY r.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function review_get_by_seller($conn, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT r.*, p.name AS product_name, u.name AS customer_name
         FROM reviews r
         JOIN products p ON r.product_id  = p.id AND p.seller_id = ?
         JOIN users    u ON r.customer_id = u.id
         ORDER BY r.created_at DESC"
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

function review_create($conn, $data) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO reviews (product_id, order_id, customer_id, rating, review_text)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiiis",
        $data['product_id'],
        $data['order_id'],
        $data['customer_id'],
        $data['rating'],
        $data['review_text']
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function review_update($conn, $id, $data) {
    $stmt = mysqli_prepare($conn,
        "UPDATE reviews SET rating = ?, review_text = ? WHERE id = ? AND customer_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "isii",
        $data['rating'],
        $data['review_text'],
        $id,
        $data['customer_id']
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function review_delete($conn, $id, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "DELETE FROM reviews WHERE id = ? AND customer_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $id, $customer_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function review_seller_reply($conn, $id, $reply, $seller_id) {
    // Verify the seller owns the product being reviewed
    $chk = mysqli_prepare($conn,
        "SELECT r.id FROM reviews r
         JOIN products p ON r.product_id = p.id AND p.seller_id = ?
         WHERE r.id = ?
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
        "UPDATE reviews SET seller_reply = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "si", $reply, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function review_can_review($conn, $customer_id, $product_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE o.customer_id = ? AND oi.product_id = ? AND oi.item_status = 'delivered'
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "ii", $customer_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row && (int)$row['cnt'] > 0;
}

function review_already_reviewed($conn, $customer_id, $product_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM reviews WHERE customer_id = ? AND product_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $customer_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row && (int)$row['cnt'] > 0;
}
