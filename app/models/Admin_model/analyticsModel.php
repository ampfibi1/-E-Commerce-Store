<?php

function getAnalyticsData($conn) {
    // Gross merchandise value
    $gmv = 0;
    $sql = "SELECT SUM(total_amount) AS gmv FROM orders";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $gmv = $row['gmv'] ?? 0;
    }

    // Platform commission earned
    $commission = 0;
    $sql = "SELECT SUM((o.total_amount - o.discount_amount) * s.commission_rate / 100) AS commission FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN sellers s ON oi.seller_id = s.id";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $commission = $row['commission'] ?? 0;
    }

    // Top performing sellers
    $topSellers = [];
    $sql = "SELECT u.name, SUM(oi.unit_price * oi.quantity) AS revenue FROM order_items oi JOIN sellers s ON oi.seller_id = s.id JOIN users u ON s.user_id = u.id GROUP BY s.id ORDER BY revenue DESC LIMIT 5";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $topSellers[] = $row;
    }

    // Top selling categories
    $topCategories = [];
    $sql = "SELECT c.name, SUM(oi.unit_price * oi.quantity) AS revenue FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN categories c ON p.category_id = c.id GROUP BY c.id ORDER BY revenue DESC LIMIT 5";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $topCategories[] = $row;
    }

    return [
        'gmv' => (float)$gmv,
        'commission' => (float)$commission,
        'top_sellers' => $topSellers,
        'top_categories' => $topCategories
    ];
}
?>