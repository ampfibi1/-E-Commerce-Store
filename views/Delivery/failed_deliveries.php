<?php
$page_title = 'Failed Deliveries';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1>Failed Deliveries</h1>
        <p class="text-muted">Review failed attempts and reassign to another agent.</p>
    </div>

    <div class="dashboard-section">
        <h2>All Failed (<?php echo count($failed_deliveries); ?>)</h2>
        <?php if (empty($failed_deliveries)): ?>
            <p class="empty-state">No failed deliveries. Great work!</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Agent</th>
                    <th>Zone</th>
                    <th>Amount</th>
                    <th>Failed At</th>
                    <th>Failure Reason</th>
                    <th>Reassign</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($failed_deliveries as $d): ?>
                <tr>
                    <td>#<?php echo (int)$d['order_id']; ?></td>
                    <td><?php echo sanitize($d['agent_name']); ?></td>
                    <td><?php echo sanitize($d['zone_name']); ?></td>
                    <td>&#2547; <?php echo number_format((float)$d['total_amount'], 2); ?></td>
                    <td><?php echo sanitize(date('d M Y, H:i', strtotime($d['updated_at']))); ?></td>
                    <td>
                        <span class="text-danger">
                            <?php echo $d['failure_reason'] ? sanitize($d['failure_reason']) : '&mdash;'; ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST"
                              action="<?php echo BASE_URL; ?>?c=delivery&a=reassign"
                              class="reassign-form"
                              novalidate
                              onsubmit="return confirm('Reassign this delivery?');">
                            <input type="hidden" name="assignment_id" value="<?php echo (int)$d['assignment_id']; ?>">
                            <select name="agent_id" class="form-control">
                                <option value="0">-- Agent --</option>
                                <?php foreach ($agents as $agent): ?>
                                <option value="<?php echo (int)$agent['id']; ?>">
                                    <?php echo sanitize($agent['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-warning">Reassign</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
