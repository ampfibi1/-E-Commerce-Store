<?php

function product_get_all($conn, $filters = array()) {
    $conditions = [];
    $params     = [];
    $types      = '';

    $base_sql = "SELECT p.*,
                        s.shop_name,
                        c.name AS category_name,
                        IFNULL((SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.id), 0) AS avg_rating
                 FROM products p
                 JOIN sellers    s ON p.seller_id    = s.id
                 JOIN categories c ON p.category_id  = c.id";

    if (!empty($filters['seller_id'])) {
        $conditions[] = "p.seller_id = ?";
        $params[]     = $filters['seller_id'];
        $types       .= 'i';
    }

    if (!empty($filters['category_id'])) {
        $conditions[] = "p.category_id = ?";
        $params[]     = $filters['category_id'];
        $types       .= 'i';
    }

    if (!empty($filters['keyword'])) {
        $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $kw           = '%' . $filters['keyword'] . '%';
        $params[]     = $kw;
        $params[]     = $kw;
        $types       .= 'ss';
    }

    if (isset($filters['min_price']) && $filters['min_price'] !== '') {
        $conditions[] = "p.price >= ?";
        $params[]     = $filters['min_price'];
        $types       .= 'd';
    }

    if (isset($filters['max_price']) && $filters['max_price'] !== '') {
        $conditions[] = "p.price <= ?";
        $params[]     = $filters['max_price'];
        $types       .= 'd';
    }

    if (!empty($filters['available_only'])) {
        $conditions[] = "p.is_available = 1";
    }

    $sql = $base_sql;
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // min_rating filter applied as HAVING after grouping on the subquery alias
    if (isset($filters['min_rating']) && $filters['min_rating'] !== '') {
        $sql   .= " HAVING avg_rating >= ?";
        $params[] = $filters['min_rating'];
        $types   .= 'd';
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!empty($types)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function product_get_by_id($conn, $id) {
    $sql  = "SELECT p.*,
                    s.shop_name,
                    c.name AS category_name,
                    IFNULL((SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.id), 0) AS avg_rating
             FROM products p
             JOIN sellers    s ON p.seller_id    = s.id
             JOIN categories c ON p.category_id  = c.id
             WHERE p.id = ?
             LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function product_get_by_seller($conn, $seller_id) {
    $sql  = "SELECT p.*,
                    c.name AS category_name,
                    IFNULL((SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.id), 0) AS avg_rating
             FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE p.seller_id = ?
             ORDER BY p.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
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

function product_create($conn, $data) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO products (seller_id, category_id, name, description, price, stock_qty, primary_image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iissids",
        $data['seller_id'],
        $data['category_id'],
        $data['name'],
        $data['description'],
        $data['price'],
        $data['stock_qty'],
        $data['primary_image_path']
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function product_update($conn, $id, $data) {
    $stmt = mysqli_prepare($conn,
        "UPDATE products
         SET category_id = ?, name = ?, description = ?, price = ?,
             stock_qty = ?, primary_image_path = ?, is_available = ?
         WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "issdiisi",
        $data['category_id'],
        $data['name'],
        $data['description'],
        $data['price'],
        $data['stock_qty'],
        $data['primary_image_path'],
        $data['is_available'],
        $id
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function product_delete($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function product_update_stock($conn, $id, $qty) {
    $stmt = mysqli_prepare($conn, "UPDATE products SET stock_qty = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $qty, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function product_toggle_availability($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "UPDATE products SET is_available = NOT is_available WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function product_get_images($conn, $product_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM product_images WHERE product_id = ? ORDER BY display_order ASC"
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

function product_add_image($conn, $product_id, $path, $order) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO product_images (product_id, image_path, display_order) VALUES (?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isi", $product_id, $path, $order);
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function product_delete_images($conn, $product_id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM product_images WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function product_get_avg_rating($conn, $product_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT AVG(rating) AS avg_rating FROM reviews WHERE product_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row ? (float)$row['avg_rating'] : 0.0;
}

function product_get_low_stock($conn, $seller_id, $threshold = 5) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM products
         WHERE seller_id = ? AND stock_qty <= ? AND is_available = 1
         ORDER BY stock_qty ASC"
    );
    mysqli_stmt_bind_param($stmt, "ii", $seller_id, $threshold);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function product_has_pending_orders($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM order_items
         WHERE product_id = ? AND item_status IN ('pending','confirmed')"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row && (int)$row['cnt'] > 0;
}
