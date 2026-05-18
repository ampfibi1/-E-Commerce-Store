<?php

function order_create($conn, $data) {
    $coupon_id = isset($data['coupon_id']) ? $data['coupon_id'] : null;

    $stmt = mysqli_prepare($conn,
        "INSERT INTO orders
             (customer_id, shipping_address, zone_id, payment_method,
              subtotal, discount_amount, delivery_fee, total_amount, coupon_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isisddddi",
        $data['customer_id'],
        $data['shipping_address'],
        $data['zone_id'],
        $data['payment_method'],
        $data['subtotal'],
        $data['discount_amount'],
        $data['delivery_fee'],
        $data['total_amount'],
        $coupon_id
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function order_add_items($conn, $order_id, $items) {
    foreach ($items as $item) {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO order_items (order_id, product_id, seller_id, quantity, unit_price)
             VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "iiiid",
            $order_id,
            $item['product_id'],
            $item['seller_id'],
            $item['quantity'],
            $item['unit_price']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $upd = mysqli_prepare($conn,
            "UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($upd, "ii", $item['quantity'], $item['product_id']);
        mysqli_stmt_execute($upd);
        mysqli_stmt_close($upd);
    }
    return true;
}

function order_get_by_customer($conn, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT o.*, z.zone_name
         FROM orders o
         JOIN delivery_zones z ON o.zone_id = z.id
         WHERE o.customer_id = ?
         ORDER BY o.created_at DESC"
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

function order_get_by_id($conn, $order_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT o.*, z.zone_name,
                u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                da.status      AS delivery_status,
                da.updated_at  AS delivery_updated_at,
                ag.name        AS delivery_agent_name
         FROM orders o
         JOIN delivery_zones z ON o.zone_id      = z.id
         JOIN users          u ON o.customer_id  = u.id
         LEFT JOIN delivery_assignments da ON da.order_id = o.id
         LEFT JOIN delivery_agents      ag ON ag.id       = da.agent_id
         WHERE o.id = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function order_get_items($conn, $order_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT oi.*, p.name AS product_name, p.primary_image_path,
                s.shop_name
         FROM order_items oi
         JOIN products p ON oi.product_id = p.id
         JOIN sellers  s ON oi.seller_id  = s.id
         WHERE oi.order_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function order_update_status($conn, $order_id, $status) {
    $stmt = mysqli_prepare($conn,
        "UPDATE orders SET status = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function order_get_by_seller($conn, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT o.*, z.zone_name AS zone, o.total_amount AS total,
                u.name AS customer_name,
                COUNT(oi2.id) AS item_count
         FROM orders o
         JOIN order_items    oi  ON o.id      = oi.order_id  AND oi.seller_id = ?
         JOIN delivery_zones z   ON o.zone_id = z.id
         JOIN users          u   ON o.customer_id = u.id
         JOIN order_items    oi2 ON o.id      = oi2.order_id AND oi2.seller_id = ?
         GROUP BY o.id, z.zone_name, u.name
         ORDER BY o.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "ii", $seller_id, $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function order_get_items_by_seller($conn, $order_id, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT oi.*, p.name AS product_name, p.primary_image_path
         FROM order_items oi
         JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ? AND oi.seller_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function order_item_update_status($conn, $item_id, $status, $note = '') {
    $stmt = mysqli_prepare($conn,
        "UPDATE order_items SET item_status = ?, status_note = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssi", $status, $note, $item_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        // Roll up: parent order.status = the "lowest" item_status across all
        // its items, so confirming the last pending item flips the order to
        // confirmed, shipping the last confirmed item flips it to shipped, etc.
        $stmt = mysqli_prepare($conn, "SELECT order_id FROM order_items WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $item_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if ($row) {
            order_recompute_status($conn, (int)$row['order_id']);
        }
    }
    return $ok;
}

// Recompute orders.status from the current item_status values.
// Rule: take the earliest-stage status across all items
// (pending < confirmed < shipped < delivered).
function order_recompute_status($conn, $order_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT MIN(FIELD(item_status,'pending','confirmed','shipped','delivered')) AS min_rank
         FROM order_items WHERE order_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if (!$row || $row['min_rank'] === null) { return; }

    $map = array(1 => 'pending', 2 => 'confirmed', 3 => 'shipped', 4 => 'delivered');
    $new_status = isset($map[(int)$row['min_rank']]) ? $map[(int)$row['min_rank']] : null;
    if (!$new_status) { return; }

    // Don't downgrade orders that have already moved beyond the item lifecycle
    // (e.g. cancelled, returned).
    $stmt = mysqli_prepare($conn,
        "UPDATE orders SET status = ?
         WHERE id = ? AND status NOT IN ('cancelled','return_requested','returned')"
    );
    mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function order_can_cancel($conn, $order_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT status FROM orders WHERE id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$row) {
        return false;
    }
    return in_array($row['status'], ['pending', 'confirmed']);
}
