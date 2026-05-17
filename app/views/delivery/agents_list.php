<?php
$page_title = 'Delivery Agents';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1>Delivery Agents</h1>
        <p class="text-muted">Manage your delivery agents and their availability.</p>
    </div>

    <div class="dashboard-section">
        <div class="section-head">
            <h2>All Agents (<?php echo count($agents); ?>)</h2>
            <a href="<?php echo BASE_URL; ?>?c=delivery&a=agent_add" class="btn btn-primary">+ Add Agent</a>
        </div>

        <?php if (empty($agents)): ?>
            <p class="empty-state">No agents found. Add your first agent.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Vehicle Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agents as $i => $agent): ?>
                <tr>
                    <td><?php echo (int)($i + 1); ?></td>
                    <td><?php echo sanitize($agent['name']); ?></td>
                    <td><?php echo sanitize($agent['phone']); ?></td>
                    <td><?php echo sanitize($agent['vehicle_type']); ?></td>
                    <td>
                        <?php if ($agent['status'] === 'active'): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo sanitize(date('d M Y', strtotime($agent['created_at']))); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>?c=delivery&a=agent_edit&id=<?php echo (int)$agent['id']; ?>"
                           class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?php echo BASE_URL; ?>?c=delivery&a=agent_toggle&id=<?php echo (int)$agent['id']; ?>"
                           class="btn btn-sm <?php echo ($agent['status'] === 'active') ? 'btn-danger' : 'btn-success'; ?>"
                           onclick="return confirm('Toggle agent status?');">
                            <?php echo ($agent['status'] === 'active') ? 'Deactivate' : 'Activate'; ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
