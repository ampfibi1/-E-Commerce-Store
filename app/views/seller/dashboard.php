<?php
$page_title = 'Seller Dashboard';
include APP . '/views/layouts/header.php';
?>

<div class="seller-dashboard">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo sanitize($seller['shop_name']); ?></h1>
        <p class="text-muted">Here is an overview of your store today.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon pending-icon">&#128216;</div>
            <div class="stat-info">
                <h3><?php echo (int)$pending_count; ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon earnings-icon">&#128176;</div>
            <div class="stat-info">
                <h3>&#2547; <?php echo number_format($earnings['gross'], 2); ?></h3>
                <p>Today's Gross Earnings</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stock-icon">&#128230;</div>
            <div class="stat-info">
                <h3><?php echo count($low_stock); ?></h3>
                <p>Low Stock Items</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon payout-icon">&#128181;</div>
            <div class="stat-info">
                <h3>&#2547; <?php echo number_format($earnings['net'], 2); ?></h3>
                <p>Net Payout Today</p>
                <small>Commission: <?php echo number_format($earnings['commission_rate'], 1); ?>%</small>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <?php if (!empty($low_stock)): ?>
    <div class="dashboard-section">
        <h2>Low Stock Alerts</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Stock Qty</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($low_stock as $i => $product): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo sanitize($product['name']); ?></td>
                    <td>
                        <span class="badge badge-danger"><?php echo (int)$product['stock_qty']; ?></span>
                    </td>
                    <td>
                        <a href="?c=seller&a=editProduct&id=<?php echo (int)$product['id']; ?>" class="btn btn-sm btn-warning">Update Stock</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="dashboard-section">
        <h2>Low Stock Alerts</h2>
        <p class="empty-state">All products are sufficiently stocked.</p>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="dashboard-section">
        <h2>Quick Actions</h2>
        <div class="quick-actions">
            <a href="?c=seller&a=addProduct" class="quick-action-card">
                <span class="qa-icon">&#10133;</span>
                <span>Add Product</span>
            </a>
            <a href="?c=seller&a=orders" class="quick-action-card">
                <span class="qa-icon">&#128220;</span>
                <span>View Orders</span>
            </a>
            <a href="?c=seller&a=coupons" class="quick-action-card">
                <span class="qa-icon">&#127991;</span>
                <span>Manage Coupons</span>
            </a>
            <a href="?c=seller&a=analytics" class="quick-action-card">
                <span class="qa-icon">&#128200;</span>
                <span>Analytics</span>
            </a>
        </div>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
