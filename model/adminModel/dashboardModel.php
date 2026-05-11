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

    function getDashboardData($conn){
        
        //total buyers
        $buyerCount = 0;
        $sql = "SELECT COUNT(*) AS count FROM users WHERE role='customer'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $buyerCount = $row['count'];
        }

        //total sellers
        $sellerCount = 0;
        $sql = "SELECT COUNT(*) AS count FROM sellers";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $sellerCount = $row['count'];
        }

        //active sellers (approved)
        $activeSellers = 0;
        $sql = "SELECT COUNT(*) AS count FROM sellers WHERE is_approved=1";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $activeSellers = $row['count'];
        }

        //orders today
        $ordersToday = 0;
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) AS count FROM orders WHERE DATE(created_at)='$today'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $ordersToday = $row['count'];
        }

        //revenue this month
        $revenueThisMonth = 0;
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        $sql = "SELECT SUM(total_amount) AS revenue FROM orders WHERE DATE(created_at) BETWEEN '$firstDayOfMonth' AND '$lastDayOfMonth'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $revenueThisMonth = $row['revenue'] ?? 0;
        }

        return [
            'buyer_count' => (int)$buyerCount,
            'seller_count' => (int)$sellerCount,
            'active_sellers' => (int)$activeSellers,
            'orders_today' => (int)$ordersToday,
            'revenue_this_month' => (float)$revenueThisMonth
        ];
    }
?>