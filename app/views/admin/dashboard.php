<?php
session_start();
$data = $_SESSION['dashboard'] ;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../app/views/admin/CSS/allmainContent.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../app/views/admin/CSS/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main_content" >
        <h1>Welcome to the Admin Dashboard</h1>
        <div class="stats">
            <div class="stat">
                <h3>Total Users</h3>
                <p><?php echo $data['buyer_count']; ?></p>
            </div>
            <div class="stat">
                <h3>Active Sellers</h3>
                <p><?php echo $data['active_sellers']; ?></p>
            </div>
            <div class="stat">
                <h3>Orders Today</h3>
                <p><?php echo $data['orders_today']; ?></p>
            </div>
            <div class="stat">
                <h3>Revenue This Month</h3>
                <p>$<?php echo number_format($data['revenue_this_month'], 2); ?></p>
            </div>
        </div>
    
    </div>
</body>
</html>