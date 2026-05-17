<?php
$page_title = 'Delivery Reports';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1>Delivery Reports</h1>
        <p class="text-muted">Generated: <?php echo date('d M Y, H:i'); ?></p>
    </div>

    <!-- Today's Summary -->
    <div class="dashboard-section">
        <h2>Today's Summary (<?php echo date('d M Y'); ?>)</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stock-icon">&#128202;</div>
                <div class="stat-info">
                    <h3><?php echo (int)$daily_summary['total']; ?></h3>
                    <p>Total</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon earnings-icon">&#9989;</div>
                <div class="stat-info">
                    <h3><?php echo (int)$daily_summary['delivered']; ?></h3>
                    <p>Delivered</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon payout-icon">&#10060;</div>
                <div class="stat-info">
                    <h3><?php echo (int)$daily_summary['failed']; ?></h3>
                    <p>Failed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending-icon">&#9203;</div>
                <div class="stat-info">
                    <h3><?php echo (int)$daily_summary['pending']; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Summary -->
    <div class="dashboard-section">
        <h2>Weekly Summary (Last 7 Days)</h2>
        <?php if (empty($weekly_summary)): ?>
            <p class="empty-state">No data for the past 7 days.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Assignments</th>
                    <th>Delivered</th>
                    <th>Failed</th>
                    <th>Success Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($weekly_summary as $row): ?>
                <?php
                    $rate = ($row['total'] > 0) ? round(($row['delivered'] / $row['total']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo sanitize(date('d M Y (D)', strtotime($row['delivery_date']))); ?></td>
                    <td><?php echo (int)$row['total']; ?></td>
                    <td><span class="badge badge-success"><?php echo (int)$row['delivered']; ?></span></td>
                    <td><span class="badge badge-danger"><?php echo (int)$row['failed']; ?></span></td>
                    <td>
                        <div class="progress-row">
                            <div class="progress-bar"><div class="progress-fill" style="width:<?php echo $rate; ?>%;"></div></div>
                            <span><?php echo $rate; ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Agent Performance -->
    <div class="dashboard-section">
        <h2>Agent Performance</h2>
        <?php if (empty($agent_performance)): ?>
            <p class="empty-state">No agent data available.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Agent Name</th>
                    <th>Vehicle</th>
                    <th>Total Assigned</th>
                    <th>Delivered</th>
                    <th>Failed</th>
                    <th>Active</th>
                    <th>Success Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agent_performance as $row): ?>
                <?php $rate = ($row['total_assigned'] > 0) ? round(($row['total_delivered'] / $row['total_assigned']) * 100, 1) : 0; ?>
                <tr>
                    <td><?php echo sanitize($row['agent_name']); ?></td>
                    <td><?php echo sanitize($row['vehicle_type']); ?></td>
                    <td><?php echo (int)$row['total_assigned']; ?></td>
                    <td><span class="badge badge-success"><?php echo (int)$row['total_delivered']; ?></span></td>
                    <td><span class="badge badge-danger"><?php echo (int)$row['total_failed']; ?></span></td>
                    <td><span class="badge badge-info"><?php echo (int)$row['total_active']; ?></span></td>
                    <td><?php echo $rate; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Zone Performance -->
    <div class="dashboard-section">
        <h2>Zone Performance</h2>
        <?php if (empty($zone_performance)): ?>
            <p class="empty-state">No zone data available.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Zone Name</th>
                    <th>Fee</th>
                    <th>Est. Days</th>
                    <th>Total Deliveries</th>
                    <th>Delivered</th>
                    <th>Failed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zone_performance as $row): ?>
                <tr>
                    <td><?php echo sanitize($row['zone_name']); ?></td>
                    <td>&#2547; <?php echo number_format((float)$row['delivery_fee'], 2); ?></td>
                    <td><?php echo (int)$row['estimated_days']; ?></td>
                    <td><?php echo (int)$row['total_deliveries']; ?></td>
                    <td><span class="badge badge-success"><?php echo (int)$row['delivered_count']; ?></span></td>
                    <td><span class="badge badge-danger"><?php echo (int)$row['failed_count']; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
