<?php

function analytics_revenue($conn, $seller_id, $period = 'month') {
    if ($period === 'day') {
        $date_expr  = "DATE(o.created_at)";
        $where_date = "o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($period === 'week') {
        $date_expr  = "YEARWEEK(o.created_at, 1)";
        $where_date = "o.created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)";
    } else {
        // month (default)
        $date_expr  = "DATE_FORMAT(o.created_at, '%Y-%m')";
        $where_date = "o.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
    }

    $sql = "SELECT {$date_expr} AS label,
                   SUM(oi.quantity * oi.unit_price) AS revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.seller_id = ?
              AND {$where_date}
              AND o.status NOT IN ('cancelled', 'returned')
            GROUP BY {$date_expr}
            ORDER BY {$date_expr} ASC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'label'   => $row['label'],
            'revenue' => (float)$row['revenue'],
        ];
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function analytics_top_products($conn, $seller_id, $limit = 5) {
    $stmt = mysqli_prepare($conn,
        "SELECT p.name,
                SUM(oi.quantity) AS total_sold,
                SUM(oi.quantity * oi.unit_price) AS total_revenue
         FROM order_items oi
         JOIN products p ON oi.product_id  = p.id
         JOIN orders   o ON oi.order_id    = o.id
         WHERE oi.seller_id = ?
           AND o.status NOT IN ('cancelled', 'returned')
         GROUP BY p.id, p.name
         ORDER BY total_revenue DESC
         LIMIT ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $seller_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'name'          => $row['name'],
            'total_sold'    => (int)$row['total_sold'],
            'total_revenue' => (float)$row['total_revenue'],
        ];
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function analytics_order_volume($conn, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT DATE(o.created_at) AS date,
                COUNT(DISTINCT o.id) AS order_count
         FROM orders o
         JOIN order_items oi ON oi.order_id = o.id AND oi.seller_id = ?
         WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY DATE(o.created_at)
         ORDER BY DATE(o.created_at) ASC"
    );
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'date'  => $row['date'],
            'count' => (int)$row['order_count'],
        ];
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function analytics_earnings($conn, $seller_id, $period = 'month') {
    // Get commission rate for this seller
    $sel = mysqli_prepare($conn,
        "SELECT commission_rate FROM sellers WHERE id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($sel, "i", $seller_id);
    mysqli_stmt_execute($sel);
    $sel_result      = mysqli_stmt_get_result($sel);
    $seller_row      = mysqli_fetch_assoc($sel_result);
    mysqli_stmt_close($sel);

    $commission_rate = $seller_row ? (float)$seller_row['commission_rate'] : 0.0;

    if ($period === 'day') {
        $where_date = "DATE(o.created_at) = CURDATE()";
    } elseif ($period === 'week') {
        $where_date = "YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
    } else {
        // month (default)
        $where_date = "DATE_FORMAT(o.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
    }

    $sql = "SELECT SUM(oi.quantity * oi.unit_price) AS gross
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.seller_id = ?
              AND {$where_date}
              AND o.status NOT IN ('cancelled', 'returned')";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $gross      = $row ? (float)$row['gross'] : 0.0;
    $commission = $gross * ($commission_rate / 100);
    $net        = $gross - $commission;

    return [
        'gross'           => $gross,
        'commission'      => $commission,
        'net'             => $net,
        'commission_rate' => $commission_rate,
    ];
}
