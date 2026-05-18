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
                    <label for="order_id">Select Order &amp; Seller</label>
                    <select id="order_id" name="order_id" class="form-control" onchange="syncZoneFromOrder()">
                        <option value="0:0" data-zone-id="0" data-zone-name="">-- Select Order --</option>
                        <?php foreach ($orders as $order): ?>
                        <option value="<?php echo (int)$order['id']; ?>:<?php echo (int)$order['seller_id']; ?>"
                                data-zone-id="<?php echo (int)$order['zone_id']; ?>"
                                data-zone-name="<?php echo sanitize($order['zone_name'] ?? ''); ?>"
                                data-zone-fee="<?php echo (float)($order['delivery_fee'] ?? 0); ?>">
                            Order #<?php echo (int)$order['id']; ?> &mdash;
                            <?php echo sanitize($order['shop_name'] ?? ('Seller #' . (int)$order['seller_id'])); ?>
                            (<?php echo (int)$order['item_count']; ?> item<?php echo (int)$order['item_count'] > 1 ? 's' : ''; ?>,
                             &#2547; <?php echo number_format((float)$order['seller_subtotal'], 2); ?>)
                            <?php if (!empty($order['zone_name'])): ?>
                                &mdash; <?php echo sanitize($order['zone_name']); ?>
                            <?php endif; ?>
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
                    <label for="zone_display">Delivery Zone</label>
                    <input type="text" id="zone_display" class="form-control" readonly
                           value="" placeholder="Auto-filled from selected order">
                    <input type="hidden" id="zone_id" name="zone_id" value="0">
                    <span class="err" id="err_zone_id"><?php echo isset($errors['zone_id']) ? sanitize($errors['zone_id']) : ''; ?></span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Assign Agent</button>
            </div>
        </form>
    </div>

    <script>
    function syncZoneFromOrder() {
        var orderSel = document.getElementById('order_id');
        var opt = orderSel.options[orderSel.selectedIndex];
        var zid  = opt.getAttribute('data-zone-id') || '0';
        var name = opt.getAttribute('data-zone-name') || '';
        var fee  = opt.getAttribute('data-zone-fee') || '';
        document.getElementById('zone_id').value = zid;
        var disp = '';
        if (zid !== '0' && name !== '') {
            disp = name + (fee ? ' (৳ ' + parseFloat(fee).toFixed(2) + ')' : '');
        }
        document.getElementById('zone_display').value = disp;
    }
    </script>

    <!-- Unassigned shipments table -->
    <div class="dashboard-section">
        <h2>Pending Dispatch (<?php echo count($orders); ?>)</h2>
        <?php if (empty($orders)): ?>
            <p class="empty-state">No shipments pending dispatch. All caught up!</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Seller</th>
                    <th>Items</th>
                    <th>Seller Subtotal</th>
                    <th>Zone</th>
                    <th>Ordered At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo (int)$order['id']; ?></td>
                    <td><?php echo sanitize($order['shop_name'] ?? ('Seller #' . (int)$order['seller_id'])); ?></td>
                    <td><?php echo (int)$order['item_count']; ?></td>
                    <td>&#2547; <?php echo number_format((float)$order['seller_subtotal'], 2); ?></td>
                    <td><?php echo sanitize($order['zone_name'] ?? '—'); ?></td>
                    <td><?php echo sanitize(date('d M Y, H:i', strtotime($order['created_at']))); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
