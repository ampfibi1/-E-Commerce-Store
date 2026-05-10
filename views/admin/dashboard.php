<?php
session_start();

$data = $_SESSION['dashboard'] ?? [
    'buyer_count' => 0,
    'seller_count' => 0,
    'active_sellers' => 0,
    'orders_today' => 0,
    'revenue_this_month' => 0
];

$total = $data['buyer_count'] + $data['seller_count'];
?>
<h1>Admin Dashboard</h1>

<p>Total Users: <?= $total ?> (Buyers: <?= $data['buyer_count'] ?>, Sellers: <?= $data['seller_count'] ?>)</p>
<p>Active Sellers: <?= $data['active_sellers'] ?></p>
<p>Orders Today: <?= $data['orders_today'] ?></p>
<p>Revenue: $<?= number_format($data['revenue_this_month'], 2) ?></p>