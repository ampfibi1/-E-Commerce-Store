<?php
$page_title = 'Ready for Dispatch';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1>Ready for Dispatch</h1>
        <p class="text-muted">Assign delivery agents to shipped orders.</p>
    </div>

    <!-- Assignment form -->
    <div class="dashboard-section form-section">
        <h2>Assign Delivery Agent</h2>
        <form method="POST"
              action="<?php echo BASE_URL; ?>?c=delivery&a=dispatch"
              id="assignForm"
              novalidate
              onsubmit="return validateAssignForm()">

            <div class="form-row">
                <div class="form-group">
                    <label for="order_id">Select Order</label>
                    <select id="order_id" name="order_id" class="form-control">
                        <option value="0">-- Select Order --</option>
                        <?php foreach ($orders as $order): ?>
                        <option value="<?php echo (int)$order['id']; ?>">
                            Order #<?php echo (int)$order['id']; ?> &mdash;
                            &#2547; <?php echo number_format((float)$order['total_amount'], 2); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="err" id="err_order_id"><?php echo isset($errors['order_id']) ? sanitize($errors['order_id']) : ''; ?></span>
                </div>

                <div class="form-group">
                    <label for="agent_id">Select Agent</label>
                    <select id="agent_id" name="agent_id" class="form-control">
                        <option value="0">-- Select Agent --</option>
                        <?php foreach ($agents as $agent): ?>
                        <option value="<?php echo (int)$agent['id']; ?>">
                            <?php echo sanitize($agent['name']); ?> (<?php echo sanitize($agent['vehicle_type']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="err" id="err_agent_id"><?php echo isset($errors['agent_id']) ? sanitize($errors['agent_id']) : ''; ?></span>
                </div>

                <div class="form-group">
                    <label for="zone_id">Select Zone</label>
                    <select id="zone_id" name="zone_id" class="form-control">
                        <option value="0">-- Select Zone --</option>
                        <?php foreach ($zones as $zone): ?>
                        <option value="<?php echo (int)$zone['id']; ?>">
                            <?php echo sanitize($zone['zone_name']); ?>
                            (&#2547; <?php echo number_format((float)$zone['delivery_fee'],2); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="err" id="err_zone_id"><?php echo isset($errors['zone_id']) ? sanitize($errors['zone_id']) : ''; ?></span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Assign Agent</button>
            </div>
        </form>
    </div>

    <!-- Unassigned orders table -->
    <div class="dashboard-section">
        <h2>Unassigned Shipped Orders (<?php echo count($orders); ?>)</h2>
        <?php if (empty($orders)): ?>
            <p class="empty-state">No orders pending dispatch. All caught up!</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Total Amount</th>
                    <th>Order Status</th>
                    <th>Ordered At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo (int)$order['id']; ?></td>
                    <td><?php echo (int)$order['user_id']; ?></td>
                    <td>&#2547; <?php echo number_format((float)$order['total_amount'], 2); ?></td>
                    <td><span class="badge badge-warning">Shipped</span></td>
                    <td><?php echo sanitize(date('d M Y, H:i', strtotime($order['created_at']))); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
