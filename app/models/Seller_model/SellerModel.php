<?php

function seller_get_by_user_id($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM sellers WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function seller_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM sellers WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function seller_create($conn, $data) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO sellers (user_id, shop_name, shop_description, shop_logo_path, address)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "issss",
        $data['user_id'],
        $data['shop_name'],
        $data['shop_description'],
        $data['shop_logo_path'],
        $data['address']
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function seller_update($conn, $seller_id, $data) {
    $stmt = mysqli_prepare($conn,
        "UPDATE sellers SET shop_name = ?, shop_description = ?, address = ?, shop_logo_path = ?
         WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssssi",
        $data['shop_name'],
        $data['shop_description'],
        $data['address'],
        $data['shop_logo_path'],
        $seller_id
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Returns earnings summary for a seller in the given period: 'day', 'week', 'month', 'all'.
// Shape: ['gross' => float, 'commission' => float, 'commission_rate' => float, 'net' => float]
function analytics_earnings($conn, $seller_id, $period = 'day') {
    $rate = 10.00;
    $stmt = mysqli_prepare($conn, "SELECT commission_rate FROM sellers WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $rate = (float)$row['commission_rate'];
    }
    mysqli_stmt_close($stmt);

    $where = "oi.seller_id = ?";
    if ($period === 'day') {
        $where .= " AND DATE(o.created_at) = CURDATE()";
    } elseif ($period === 'week') {
        $where .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($period === 'month') {
        $where .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }

    $sql = "SELECT COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS gross
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE $where";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    $gross = (float)($row['gross'] ?? 0);
    $commission = $gross * ($rate / 100);
    $net = $gross - $commission;

    return array(
        'gross'           => $gross,
        'commission'      => $commission,
        'commission_rate' => $rate,
        'net'             => $net,
    );
}

// Daily revenue series for the seller over the given period.
// Returns array of { label: 'YYYY-MM-DD', revenue: float }
function analytics_revenue($conn, $seller_id, $period = 'month') {
    $interval = ($period === 'day') ? 1 : (($period === 'week') ? 7 : 30);
    $sql = "SELECT DATE(o.created_at) AS label,
                   COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.seller_id = ?
              AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(o.created_at)
            ORDER BY DATE(o.created_at) ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $seller_id, $interval);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = array('label' => $r['label'], 'revenue' => (float)$r['revenue']);
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// Top N selling products for the seller.
// Returns array of { name, total_sold, total_revenue }
function analytics_top_products($conn, $seller_id, $limit = 5) {
    $limit = (int)$limit;
    if ($limit <= 0) { $limit = 5; }
    $sql = "SELECT p.name,
                   COALESCE(SUM(oi.quantity), 0) AS total_sold,
                   COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS total_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.seller_id = ?
            GROUP BY p.id, p.name
            ORDER BY total_sold DESC
            LIMIT $limit";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = $r;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// Daily order count series.
// Returns array of { date: 'YYYY-MM-DD', count: int }
function analytics_order_volume($conn, $seller_id, $period = 'month') {
    $interval = ($period === 'day') ? 1 : (($period === 'week') ? 7 : 30);
    $sql = "SELECT DATE(o.created_at) AS date,
                   COUNT(DISTINCT o.id) AS count
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            WHERE oi.seller_id = ?
              AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(o.created_at)
            ORDER BY DATE(o.created_at) ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $seller_id, $interval);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = array('date' => $r['date'], 'count' => (int)$r['count']);
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// Seller acts on a dispute: 'accepted' (resolves it) or 'rejected' (escalates).
// Always scoped to seller_id so a seller cannot touch another seller's dispute.
// $action: 'accepted' | 'rejected'
function dispute_seller_respond($conn, $dispute_id, $seller_id, $response, $action) {
    if ($action !== 'accepted' && $action !== 'rejected') {
        return false;
    }
    $new_status = ($action === 'accepted') ? 'resolved' : 'open';

    $stmt = mysqli_prepare($conn,
        "UPDATE disputes
         SET seller_response = ?,
             seller_responded_at = NOW(),
             seller_action = ?,
             status = ?
         WHERE id = ? AND seller_id = ?"
    );
    if (!$stmt) { return false; }
    mysqli_stmt_bind_param($stmt, "sssii", $response, $action, $new_status, $dispute_id, $seller_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// One dispute scoped to this seller (security: must match seller_id).
function dispute_get_one_for_seller($conn, $dispute_id, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT d.*,
                u.name  AS customer_name,
                u.email AS customer_email,
                o.created_at      AS order_date,
                o.total_amount    AS total_amount,
                o.shipping_address AS shipping_address
         FROM disputes d
         LEFT JOIN users  u ON d.customer_id = u.id
         LEFT JOIN orders o ON d.order_id    = o.id
         WHERE d.id = ? AND d.seller_id = ?
         LIMIT 1"
    );
    if (!$stmt) { return null; }
    mysqli_stmt_bind_param($stmt, "ii", $dispute_id, $seller_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

// Returns disputes filed against this seller.
function dispute_get_by_seller($conn, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT d.*, u.name AS customer_name
         FROM disputes d
         LEFT JOIN users u ON d.customer_id = u.id
         WHERE d.seller_id = ?
         ORDER BY d.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}
