<?php
$oid = isset($order['order_id']) ? (int)$order['order_id'] : (isset($order['id']) ? (int)$order['id'] : 0);
$page_title = 'Order #' . $oid;
?>
<?php include APP . '/views/layouts/header.php'; ?>

<div style="margin-bottom:0.75rem;">
    <a href="<?php echo BASE_URL; ?>?c=customer&a=orders" class="text-muted" style="font-size:0.88rem;">&larr; Back to Orders</a>
</div>

<div class="page-header">
    <h1>Order #<?php echo $oid; ?></h1>
</div>

<!-- Order Info Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Order Information</h3>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.6rem 2rem; font-size:0.92rem;">
        <div>
            <span class="text-muted">Order Date:</span><br>
            <strong><?php echo sanitize(date('d M Y, H:i', strtotime($order['created_at']))); ?></strong>
        </div>
        <div>
            <span class="text-muted">Status:</span><br>
            <?php
            $status = strtolower($order['status']);
            $badge_map = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','processing'=>'badge-processing','shipped'=>'badge-shipped','delivered'=>'badge-delivered','cancelled'=>'badge-cancelled','returned'=>'badge-returned'];
            $bc = isset($badge_map[$status]) ? $badge_map[$status] : 'badge-pending';
            ?>
            <span class="badge <?php echo $bc; ?>" id="orderStatusBadge"><?php echo sanitize(ucfirst($order['status'])); ?></span>
        </div>
        <div>
            <span class="text-muted">Payment Method:</span><br>
            <strong><?php echo sanitize(ucwords(str_replace('_', ' ', $order['payment_method']))); ?></strong>
        </div>
        <div>
            <span class="text-muted">Shipping Address:</span><br>
            <strong><?php echo sanitize($order['shipping_address']); ?></strong>
        </div>
        <?php if (!empty($order['zone_name'])): ?>
        <div>
            <span class="text-muted">Delivery Zone:</span><br>
            <strong><?php echo sanitize($order['zone_name']); ?></strong>
            <?php if (!empty($order['estimated_days'])): ?>
            <span class="text-muted">(Est. <?php echo (int)$order['estimated_days']; ?> day<?php echo $order['estimated_days'] > 1 ? 's' : ''; ?>)</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($order['delivery_status'])): ?>
        <div>
            <span class="text-muted">Delivery Update:</span><br>
            <?php
            $ds = strtolower($order['delivery_status']);
            $ds_label_map = array(
                'assigned'   => 'Assigned to courier',
                'picked_up'  => 'Picked up by courier',
                'in_transit' => 'Out for delivery',
                'delivered'  => 'Delivered',
                'failed'     => 'Delivery failed',
            );
            $ds_color_map = array(
                'assigned' => '#6c757d', 'picked_up' => '#17a2b8',
                'in_transit' => '#fd7e14', 'delivered' => '#28a745',
                'failed' => '#dc3545',
            );
            $ds_label = isset($ds_label_map[$ds]) ? $ds_label_map[$ds] : ucfirst($ds);
            $ds_color = isset($ds_color_map[$ds]) ? $ds_color_map[$ds] : '#6c757d';
            ?>
            <strong style="color:<?php echo $ds_color; ?>;"><?php echo sanitize($ds_label); ?></strong>
            <?php if (!empty($order['delivery_agent_name'])): ?>
                <div class="text-muted" style="font-size:0.85rem;">
                    Agent: <?php echo sanitize($order['delivery_agent_name']); ?>
                    <?php if (!empty($order['delivery_updated_at'])): ?>
                        &middot; <?php echo sanitize(date('d M, H:i', strtotime($order['delivery_updated_at']))); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Status Progress Bar -->
<?php
$status_steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
$current_status = strtolower($order['status']);
$current_idx = array_search($current_status, $status_steps);
if ($current_idx === false) { $current_idx = -1; }
?>
<?php if ($current_status !== 'cancelled' && $current_status !== 'returned'): ?>
<div class="order-status-bar">
    <?php foreach ($status_steps as $idx => $step): ?>
        <?php
        $step_class = '';
        if ($idx < $current_idx) { $step_class = 'done'; }
        elseif ($idx === $current_idx) { $step_class = 'active'; }
        ?>
        <div class="status-step <?php echo $step_class; ?>">
            <div class="status-step-circle"><?php echo $idx + 1; ?></div>
            <div class="status-step-label"><?php echo ucfirst($step); ?></div>
        </div>
        <?php if ($idx < count($status_steps) - 1): ?>
        <div class="status-connector <?php echo ($idx < $current_idx) ? 'done' : ''; ?>"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php elseif ($current_status === 'cancelled'): ?>
<div class="alert alert-danger">This order has been cancelled.</div>
<?php elseif ($current_status === 'returned'): ?>
<div class="alert alert-info">A return has been requested for this order.</div>
<?php endif; ?>

<!-- Items Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Order Items</h3>
    </div>
    <table class="table" id="orderItemsTable">
        <thead>
            <tr>
                <th>Product</th>
                <th>Seller</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
                <th>Item Status</th>
                <th>Tracking Note</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                <?php
                $iid = isset($item['order_item_id']) ? (int)$item['order_item_id'] : (isset($item['id']) ? (int)$item['id'] : 0);
                $i_status = strtolower($item['item_status'] ?? 'pending');
                $i_bc = isset($badge_map[$i_status]) ? $badge_map[$i_status] : 'badge-pending';
                ?>
                <tr data-item-id="<?php echo $iid; ?>">
                    <td>
                        <?php echo sanitize($item['product_name']); ?>
                    </td>
                    <td class="text-muted" style="font-size:0.85rem;">
                        <?php echo sanitize($item['shop_name'] ?? '—'); ?>
                    </td>
                    <td><?php echo (int)$item['quantity']; ?></td>
                    <td>৳<?php echo number_format((float)$item['unit_price'], 2); ?></td>
                    <td>৳<?php echo number_format((float)($item['unit_price'] * $item['quantity']), 2); ?></td>
                    <td>
                        <span class="badge <?php echo $i_bc; ?> item-status-badge">
                            <?php echo sanitize(ucfirst($i_status)); ?>
                        </span>
                    </td>
                    <td class="item-tracking text-muted" style="font-size:0.85rem;">
                        <?php echo !empty($item['tracking_note']) ? sanitize($item['tracking_note']) : '—'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Order Totals -->
<div class="card">
    <table class="table" style="max-width:320px; margin-left:auto;">
        <?php if (!empty($order['subtotal'])): ?>
        <tr>
            <td>Subtotal</td>
            <td style="text-align:right;">৳<?php echo number_format((float)$order['subtotal'], 2); ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($order['discount_amount']) && (float)$order['discount_amount'] > 0): ?>
        <tr>
            <td style="color:#28a745;">Discount</td>
            <td style="text-align:right; color:#28a745;">-৳<?php echo number_format((float)$order['discount_amount'], 2); ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($order['delivery_fee'])): ?>
        <tr>
            <td>Delivery Fee</td>
            <td style="text-align:right;">৳<?php echo number_format((float)$order['delivery_fee'], 2); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td><strong>Total</strong></td>
            <td style="text-align:right;"><strong style="color:#2c7be5; font-size:1.05rem;">৳<?php echo number_format((float)$order['total_amount'], 2); ?></strong></td>
        </tr>
    </table>
</div>

<!-- Cancel Order -->
<?php if (in_array($current_status, array('pending', 'confirmed'))): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Cancel Order</h3>
    </div>
    <p style="font-size:0.9rem; color:#666;">You can cancel this order while it is still pending or confirmed.</p>
    <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=order_detail&id=<?php echo $oid; ?>"
          onsubmit="return confirm('Are you sure you want to cancel this order?')">
        <input type="hidden" name="action" value="cancel">
        <button type="submit" class="btn btn-danger">Cancel This Order</button>
    </form>
</div>
<?php endif; ?>

<!-- Return Request -->
<?php if ($current_status === 'delivered' && !empty($items)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Request a Return</h3>
    </div>
    <p style="font-size:0.9rem; color:#666;">Select an item and provide a reason for the return.</p>

    <form id="returnForm"
          method="POST"
          action="<?php echo BASE_URL; ?>?c=customer&a=order_detail&id=<?php echo $oid; ?>"
          novalidate
          onsubmit="return validateReturnForm()">
        <input type="hidden" name="action" value="return_request">

        <div class="form-group">
            <label class="form-label" for="order_item_id">Select Item</label>
            <select id="order_item_id" name="order_item_id" class="form-control" style="max-width:400px;">
                <option value="">-- Select an item --</option>
                <?php foreach ($items as $item): ?>
                <?php $iid2 = isset($item['order_item_id']) ? (int)$item['order_item_id'] : (isset($item['id']) ? (int)$item['id'] : 0); ?>
                <option value="<?php echo $iid2; ?>"><?php echo sanitize($item['product_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <span class="err" id="err_order_item_id"></span>
        </div>

        <div class="form-group">
            <label class="form-label" for="reason">Return Reason</label>
            <textarea id="reason" name="reason" class="form-control" rows="3"
                      placeholder="Please describe why you are returning this item..."></textarea>
            <span class="err" id="err_reason"></span>
        </div>

        <button type="submit" class="btn btn-warning">Submit Return Request</button>
    </form>
</div>
<?php endif; ?>

<!-- AJAX Polling for Live Status Updates -->
<script>
(function() {
    var orderId = <?php echo $oid; ?>;
    var terminalStatuses = ['delivered', 'cancelled', 'returned'];
    var currentStatus = '<?php echo addslashes($current_status); ?>';

    if (terminalStatuses.indexOf(currentStatus) !== -1) {
        return; /* No polling needed for terminal states */
    }

    function pollStatus() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '<?php echo BASE_URL; ?>../ajax/order_status.php?order_id=' + orderId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.error) { return; }

                    /* Update order status badge */
                    if (data.status) {
                        var badge = document.getElementById('orderStatusBadge');
                        if (badge) {
                            var badgeClasses = {
                                'pending': 'badge-pending',
                                'confirmed': 'badge-confirmed',
                                'processing': 'badge-processing',
                                'shipped': 'badge-shipped',
                                'delivered': 'badge-delivered',
                                'cancelled': 'badge-cancelled',
                                'returned': 'badge-returned'
                            };
                            var newClass = badgeClasses[data.status.toLowerCase()] || 'badge-pending';
                            badge.className = 'badge ' + newClass;
                            badge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                        }
                        currentStatus = data.status.toLowerCase();
                    }

                    /* Update item statuses */
                    if (data.items && data.items.length > 0) {
                        for (var i = 0; i < data.items.length; i++) {
                            var item = data.items[i];
                            var row = document.querySelector('tr[data-item-id="' + item.order_item_id + '"]');
                            if (row) {
                                var statusBadge = row.querySelector('.item-status-badge');
                                if (statusBadge && item.item_status) {
                                    var iStatus = item.item_status.toLowerCase();
                                    var iBadgeClasses = {
                                        'pending': 'badge-pending',
                                        'confirmed': 'badge-confirmed',
                                        'processing': 'badge-processing',
                                        'shipped': 'badge-shipped',
                                        'delivered': 'badge-delivered',
                                        'cancelled': 'badge-cancelled'
                                    };
                                    var iNewClass = iBadgeClasses[iStatus] || 'badge-pending';
                                    statusBadge.className = 'badge ' + iNewClass + ' item-status-badge';
                                    statusBadge.textContent = iStatus.charAt(0).toUpperCase() + iStatus.slice(1);
                                }
                                var trackingCell = row.querySelector('.item-tracking');
                                if (trackingCell && item.tracking_note) {
                                    trackingCell.textContent = item.tracking_note;
                                }
                            }
                        }
                    }

                    /* Stop polling if terminal */
                    if (terminalStatuses.indexOf(currentStatus) === -1) {
                        setTimeout(pollStatus, 15000);
                    }
                } catch (e) {
                    /* JSON parse error — retry later */
                    setTimeout(pollStatus, 30000);
                }
            }
        };
        xhr.send();
    }

    /* First poll after 15 seconds */
    setTimeout(pollStatus, 15000);
})();
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
