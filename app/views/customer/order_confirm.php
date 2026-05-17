<?php $page_title = 'Order Confirmed'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div style="max-width:700px; margin:2rem auto;">

    <div class="card text-center" style="padding:2rem;">
        <div style="font-size:4rem; color:#28a745; margin-bottom:0.75rem;">&#10003;</div>
        <h2 style="color:#28a745; margin-bottom:0.5rem;">Order Placed Successfully!</h2>
        <p class="text-muted">Thank you for shopping at ShopHub. Your order has been received.</p>
    </div>

    <!-- Order Summary -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Order Details</h3>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem 2rem; font-size:0.93rem; margin-bottom:1rem;">
            <div>
                <span class="text-muted">Order Number:</span><br>
                <strong>#<?php echo (int)$order['id']; ?></strong>
            </div>
            <div>
                <span class="text-muted">Order Date:</span><br>
                <strong><?php echo sanitize(date('d M Y, H:i', strtotime($order['created_at']))); ?></strong>
            </div>
            <div>
                <span class="text-muted">Payment Method:</span><br>
                <strong><?php echo sanitize(ucwords(str_replace('_', ' ', $order['payment_method']))); ?></strong>
            </div>
            <div>
                <span class="text-muted">Order Status:</span><br>
                <?php
                $status = strtolower($order['status']);
                $badge_map = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','processing'=>'badge-processing','shipped'=>'badge-shipped','delivered'=>'badge-delivered','cancelled'=>'badge-cancelled'];
                $bc = isset($badge_map[$status]) ? $badge_map[$status] : 'badge-pending';
                ?>
                <span class="badge <?php echo $bc; ?>"><?php echo sanitize(ucfirst($order['status'])); ?></span>
            </div>
            <div>
                <span class="text-muted">Shipping Address:</span><br>
                <strong><?php echo sanitize($order['shipping_address']); ?></strong>
            </div>
            <?php if (!empty($order['zone_name'])): ?>
            <div>
                <span class="text-muted">Delivery Zone:</span><br>
                <strong>
                    <?php echo sanitize($order['zone_name']); ?>
                    <?php if (!empty($order['estimated_days'])): ?>
                    &mdash; Est. <?php echo (int)$order['estimated_days']; ?> day<?php echo $order['estimated_days'] > 1 ? 's' : ''; ?>
                    <?php endif; ?>
                </strong>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Items Ordered</h3>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo sanitize($item['product_name']); ?></td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td>৳<?php echo number_format((float)$item['unit_price'], 2); ?></td>
                        <td>৳<?php echo number_format((float)($item['unit_price'] * $item['quantity']), 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <?php if (!empty($order['subtotal'])): ?>
                <tr>
                    <td colspan="3" style="text-align:right;">Subtotal</td>
                    <td>৳<?php echo number_format((float)$order['subtotal'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['discount_amount']) && (float)$order['discount_amount'] > 0): ?>
                <tr>
                    <td colspan="3" style="text-align:right; color:#28a745;">Discount</td>
                    <td style="color:#28a745;">-৳<?php echo number_format((float)$order['discount_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['delivery_fee'])): ?>
                <tr>
                    <td colspan="3" style="text-align:right;">Delivery Fee</td>
                    <td>৳<?php echo number_format((float)$order['delivery_fee'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:700;">Total</td>
                    <td style="font-weight:700; color:#2c7be5; font-size:1.05rem;">৳<?php echo number_format((float)$order['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Action Buttons -->
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin-top:0.5rem;">
        <a href="<?php echo BASE_URL; ?>?c=customer&a=orderDetail&id=<?php echo (int)$order['id']; ?>"
           class="btn btn-primary">Track Order</a>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=orders" class="btn btn-secondary">All My Orders</a>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-secondary">Continue Shopping</a>
    </div>

</div>

<?php include APP . '/views/layouts/footer.php'; ?>
