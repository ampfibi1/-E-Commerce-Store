<?php
$page_title = 'Active Deliveries';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1>Active Deliveries</h1>
        <p class="text-muted">Live status updates via AJAX &mdash; no page reload.</p>
    </div>

    <div class="dashboard-section">
        <h2>In Progress (<?php echo count($deliveries); ?>)</h2>
        <?php if (empty($deliveries)): ?>
            <p class="empty-state">No active deliveries at the moment.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Agent</th>
                    <th>Vehicle</th>
                    <th>Zone</th>
                    <th>Amount</th>
                    <th>Assigned</th>
                    <th>Time Since</th>
                    <th>Current Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $d): ?>
                <?php
                    $assigned_ts  = strtotime($d['assigned_at']);
                    $diff_seconds = time() - $assigned_ts;
                    $diff_hours   = floor($diff_seconds / 3600);
                    $diff_minutes = floor(($diff_seconds % 3600) / 60);
                    $time_since   = ($diff_hours > 0) ? $diff_hours . 'h ' . $diff_minutes . 'm' : $diff_minutes . 'm';

                    $badge_map = array(
                        'assigned'   => 'badge-info',
                        'picked_up'  => 'badge-warning',
                        'in_transit' => 'badge-info',
                        'delivered'  => 'badge-success',
                        'failed'     => 'badge-danger',
                    );
                    $badge_class = isset($badge_map[$d['status']]) ? $badge_map[$d['status']] : 'badge-secondary';
                ?>
                <tr id="row-<?php echo (int)$d['assignment_id']; ?>">
                    <td>#<?php echo (int)$d['order_id']; ?></td>
                    <td>
                        <?php echo sanitize($d['agent_name']); ?><br>
                        <small class="text-muted"><?php echo sanitize($d['agent_phone']); ?></small>
                    </td>
                    <td><?php echo sanitize($d['vehicle_type']); ?></td>
                    <td><?php echo sanitize($d['zone_name']); ?></td>
                    <td>&#2547; <?php echo number_format((float)$d['total_amount'], 2); ?></td>
                    <td><?php echo sanitize(date('d M, H:i', strtotime($d['assigned_at']))); ?></td>
                    <td><?php echo sanitize($time_since); ?> ago</td>
                    <td>
                        <span class="badge <?php echo $badge_class; ?> status-badge">
                            <?php echo sanitize(str_replace('_', ' ', $d['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <select class="form-control status-select"
                                id="select-<?php echo (int)$d['assignment_id']; ?>"
                                data-prev="<?php echo sanitize($d['status']); ?>"
                                onchange="ajaxUpdateStatus(<?php echo (int)$d['assignment_id']; ?>, this)">
                            <option value="assigned"   <?php echo ($d['status'] === 'assigned')   ? 'selected' : ''; ?>>Assigned</option>
                            <option value="picked_up"  <?php echo ($d['status'] === 'picked_up')  ? 'selected' : ''; ?>>Picked Up</option>
                            <option value="in_transit" <?php echo ($d['status'] === 'in_transit') ? 'selected' : ''; ?>>In Transit</option>
                            <option value="delivered"  <?php echo ($d['status'] === 'delivered')  ? 'selected' : ''; ?>>Delivered</option>
                            <option value="failed"     <?php echo ($d['status'] === 'failed')     ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
