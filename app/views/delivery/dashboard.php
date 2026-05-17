<?php
$page_title = 'Delivery Dashboard';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo sanitize($_SESSION['user_name']); ?></h1>
        <p class="text-muted">Logistics overview for <?php echo date('l, d F Y'); ?>.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon pending-icon">&#128230;</div>
            <div class="stat-info">
                <h3><?php echo (int)$pending_dispatch; ?></h3>
                <p>Pending Dispatch</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stock-icon">&#128666;</div>
            <div class="stat-info">
                <h3><?php echo (int)$active_deliveries; ?></h3>
                <p>Active Deliveries</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon earnings-icon">&#9989;</div>
            <div class="stat-info">
                <h3><?php echo (int)$delivered_today; ?></h3>
                <p>Delivered Today</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-section">
        <h2>Quick Actions</h2>
        <div class="quick-actions">
            <a href="<?php echo BASE_URL; ?>?c=delivery&a=dispatch" class="btn btn-primary">Assign Delivery</a>
            <a href="<?php echo BASE_URL; ?>?c=delivery&a=agent_add" class="btn btn-secondary">Add Agent</a>
            <a href="<?php echo BASE_URL; ?>?c=delivery&a=zone_add" class="btn btn-secondary">Add Zone</a>
            <a href="<?php echo BASE_URL; ?>?c=delivery&a=failed" class="btn btn-warning">Failed Deliveries</a>
        </div>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
