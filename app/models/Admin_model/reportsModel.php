<?php
function generateMonthlyReport($month,$conn) {
    $start = $month . '-01';
    $end = date('Y-m-t', strtotime($start));

    // Total orders
    $totalOrders = 0;
    $sql = "SELECT COUNT(*) AS count FROM orders WHERE DATE(created_at) BETWEEN '$start' AND '$end'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $totalOrders = $row['count'];

    // Total revenue
    $totalRevenue = 0;
    $sql = "SELECT SUM(total_amount) AS revenue FROM orders WHERE DATE(created_at) BETWEEN '$start' AND '$end'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $totalRevenue = $row['revenue'] ?? 0;

    // Commission earned
    $commission = 0;
    $sql = "SELECT SUM((oi.unit_price * oi.quantity) * s.commission_rate / 100) AS commission FROM order_items oi JOIN orders o ON oi.order_id = o.id JOIN sellers s ON oi.seller_id = s.id WHERE DATE(o.created_at) BETWEEN '$start' AND '$end'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $commission = $row['commission'] ?? 0;

    // New sellers
    $newSellers = 0;
    $sql = "SELECT COUNT(*) AS count FROM sellers WHERE DATE(created_at) BETWEEN '$start' AND '$end'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $newSellers = $row['count'];

    // New customers
    $newCustomers = 0;
    $sql = "SELECT COUNT(*) AS count FROM users WHERE role='customer' AND DATE(created_at) BETWEEN '$start' AND '$end'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $newCustomers = $row['count'];

    return [
        'total_orders' => $totalOrders,
        'total_revenue' => (float)$totalRevenue,
        'commission' => (float)$commission,
        'new_sellers' => $newSellers,
        'new_customers' => $newCustomers
    ];
}
?>