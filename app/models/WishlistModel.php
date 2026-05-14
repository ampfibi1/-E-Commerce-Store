<?php

function wishlist_get($conn, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT w.*, p.name, p.price, p.primary_image_path, p.is_available,
                p.stock_qty, p.seller_id, s.shop_name
         FROM wishlists w
         JOIN products p ON w.product_id  = p.id
         JOIN sellers  s ON p.seller_id   = s.id
         WHERE w.customer_id = ?
         ORDER BY w.added_at DESC"
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

function wishlist_add($conn, $customer_id, $product_id) {
    $stmt = mysqli_prepare($conn,
        "INSERT IGNORE INTO wishlists (customer_id, product_id) VALUES (?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ii", $customer_id, $product_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function wishlist_remove($conn, $customer_id, $product_id) {
    $stmt = mysqli_prepare($conn,
        "DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $customer_id, $product_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function wishlist_is_in($conn, $customer_id, $product_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "ii", $customer_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (bool)$row;
}
