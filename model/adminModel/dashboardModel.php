<?php
    function withoutDB(){
        // Simulated data for demonstration
        $buyerCount = 1500;
        $sellerCount = 300;
        $activeSellers = 250;
        $ordersToday = 75;
        $revenueThisMonth = 12500.75;
        
        return [
           'buyer_count' => (int)$buyerCount,
           'seller_count' => (int)$sellerCount,
           'active_sellers' => (int)$activeSellers,
           'orders_today' => (int)$ordersToday,
           'revenue_this_month' => (float)$revenueThisMonth
        ];
    }

    // function getDashboardData() {
    //     $conn = connectDB();

    //     // Get total buyers
    //     $buyerCount = 0;
    //     $result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role='buyer'");
    //     if ($result) {
    //         $row = $result->fetch_assoc();
    //         $buyerCount = $row['count'];
    //     }

    //     // Get total sellers
    //     $sellerCount = 0;
    //     $result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role='seller'");
    //     if ($result) {
    //         $row = $result->fetch_assoc();
    //         $sellerCount = $row['count'];
    //     }

    //     // Get active sellers
    //     $activeSellers = 0;
    //     $result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role='seller' AND status='active'");
    //     if ($result) {
    //         $row = $result->fetch_assoc();
    //         $activeSellers = $row['count'];
    //     }

    //     // Get orders today
    //     $ordersToday = 0;
    //     $today = date('Y-m-d');
    //     $result = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE DATE(created_at)='$today'");
    //     if ($result) {
    //         $row = $result->fetch_assoc();
    //         $ordersToday = $row['count'];
    //     }

    //     // Get revenue this month
    //     $revenueThisMonth = 0;
    //     $firstDayOfMonth = date('Y-m-01');
    //     $lastDayOfMonth = date('Y-m-t');
    //     $result = $conn->query("SELECT SUM(total_amount) AS revenue FROM orders WHERE DATE(created_at) BETWEEN '$firstDayOfMonth' AND '$lastDayOfMonth'");
    //     if ($result) {
    //         $row = $result->fetch_assoc();
    //         $revenueThisMonth = $row['revenue'] ?? 0;
    //     }

    //     closeDB($conn);

    //     return [
    //         'buyer_count' => (int)$buyerCount,
    //         'seller_count' => (int)$sellerCount,
    //         'active_sellers' => (int)$activeSellers,
    //         'orders_today' => (int)$ordersToday,
    //         'revenue_this_month' => (float)$revenueThisMonth
    //     ];
    // }
?>