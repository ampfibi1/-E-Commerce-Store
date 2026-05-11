<?php
session_start();
$data = $_SESSION['analytics'] ?? [
    'gmv' => 0,
    'commission' => 0,
    'top_sellers' => [],
    'top_categories' => []
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <style>
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat { background: white; padding: 20px; border-radius: 5px; flex: 1; text-align: center; }
    </style>
    <link rel="stylesheet" href="CSS/allmainContent.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main_content" >
        <h1>Platform Analytics</h1>
        <div class="stats">
            <div class="stat">
                <h3>Gross Merchandise Value</h3>
                <p>$<?= number_format($data['gmv'], 2) ?></p>
            </div>
            <div class="stat">
                <h3>Platform Commission Earned</h3>
                <p>$<?= number_format($data['commission'], 2) ?></p>
            </div>
        </div>
        <h2>Top Performing Sellers</h2>
        <table>
            <tr>
                <th>Seller</th>
                <th>Revenue</th>
            </tr>
            <?php foreach ($data['top_sellers'] as $seller): ?>
            <tr>
                <td><?= htmlspecialchars($seller['name']) ?></td>
                <td>$<?= number_format($seller['revenue'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h2>Top Selling Categories</h2>
        <table>
            <tr>
                <th>Category</th>
                <th>Revenue</th>
            </tr>
            <?php foreach ($data['top_categories'] as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td>$<?= number_format($cat['revenue'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>    
    </div>
</body>
</html>