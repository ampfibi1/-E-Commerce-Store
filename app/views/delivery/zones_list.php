<?php
$page_title = 'Delivery Zones';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1>Delivery Zones</h1>
        <p class="text-muted">Manage delivery areas, fees, and estimated delivery times.</p>
    </div>

    <div class="dashboard-section">
        <div class="section-head">
            <h2>All Zones (<?php echo count($zones); ?>)</h2>
            <a href="<?php echo BASE_URL; ?>?c=delivery&a=zone_add" class="btn btn-primary">+ Add Zone</a>
        </div>

        <?php if (empty($zones)): ?>
            <p class="empty-state">No zones found. Add your first delivery zone.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Zone Name</th>
                    <th>Delivery Fee</th>
                    <th>Estimated Days</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zones as $i => $zone): ?>
                <tr>
                    <td><?php echo (int)($i + 1); ?></td>
                    <td><?php echo sanitize($zone['zone_name']); ?></td>
                    <td>&#2547; <?php echo number_format((float)$zone['delivery_fee'], 2); ?></td>
                    <td><?php echo (int)$zone['estimated_days']; ?> day(s)</td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>?c=delivery&a=zone_edit&id=<?php echo (int)$zone['id']; ?>"
                           class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo BASE_URL; ?>?c=delivery&a=zone_delete&id=<?php echo (int)$zone['id']; ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this zone? This cannot be undone.');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
