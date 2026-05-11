<?php
session_start();
$report = $_SESSION['report'] ?? [
    'total_orders' => 0,
    'total_revenue' => 0,
    'commission' => 0,
    'new_sellers' => 0,
    'new_customers' => 0
];
$month = $_SESSION['report_month'] ?? date('Y-m');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="CSS/allmainContent.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main_content" >
        <h1>Monthly Reports</h1>
        <form action="../../controller/adminController/reportsController.php" method="post">
            <input type="month" name="month" value="<?= $month ?>" required>
            <input type="submit" value="Generate Report">
        </form>
        <div class="report">
            <h2>Report for <?= date('F Y', strtotime($month . '-01')) ?></h2>
            <p>Total Orders: <?= $report['total_orders'] ?></p>
            <p>Total Revenue: $<?= number_format($report['total_revenue'], 2) ?></p>
            <p>Commission Earned: $<?= number_format($report['commission'], 2) ?></p>
            <p>New Sellers: <?= $report['new_sellers'] ?></p>
            <p>New Customers: <?= $report['new_customers'] ?></p>
            <button class="print" onclick="window.print()">Export as Printable</button>
        </div>
    </div>
</body>
</html>