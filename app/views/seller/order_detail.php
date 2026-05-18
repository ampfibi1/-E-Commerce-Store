<?php
$page_title = 'Order Detail #' . (int)$order['id'];
include APP . '/views/layouts/header.php';
?>

<div class="seller-order-detail">
    <div class="page-header">
        <h1>Order #<?php echo (int)$order['id']; ?></h1>
        <a href="?c=seller&a=orders" class="btn btn-secondary">&larr; Back to Orders</a>
    </div>

    <!-- Order Info -->
    <div class="order-info-grid">
        <div class="order-info-card">
            <h3>Order Information</h3>
            <table class="info-table">
                <tr>
                    <th>Order #</th>
                    <td><?php echo (int)$order['id']; ?></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><?php echo sanitize($order['created_at']); ?></td>
                </tr>
                <tr>
                    <th>Payment Method</th>
                    <td><?php echo sanitize($order['payment_method']); ?></td>
                </tr>
                <tr>
                    <th>Order Status</th>
                    <td>
                        <?php
                        $statusClass = 'badge-secondary';
                        if ($order['status'] === 'pending')   $statusClass = 'badge-warning';
                        if ($order['status'] === 'confirmed') $statusClass = 'badge-info';
                        if ($order['status'] === 'shipped')   $statusClass = 'badge-primary';
                        if ($order['status'] === 'delivered') $statusClass = 'badge-success';
                        if ($order['status'] === 'cancelled') $statusClass = 'badge-danger';
                        ?>
                        <span class="badge <?php echo $statusClass; ?>"><?php echo sanitize(ucfirst($order['status'])); ?></span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="order-info-card">
            <h3>Customer Information</h3>
            <table class="info-table">
                <tr>
                    <th>Name</th>
                    <td><?php echo sanitize($order['customer_name'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>Shipping Address</th>
                    <td><?php echo sanitize($order['shipping_address']); ?></td>
                </tr>
                <tr>
                    <th>Zone</th>
                    <td><?php echo sanitize($order['zone_name'] ?? ''); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Items from This Seller -->
    <div class="order-items-section">
        <h2>Your Items in This Order</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                    <th>Item Status</th>
                    <th>Tracking Note</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo sanitize($item['product_name']); ?></td>
                    <td><?php echo (int)$item['quantity']; ?></td>
                    <td>&#2547; <?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>&#2547; <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                    <td>
                        <?php
                        $itemStatusClass = 'badge-secondary';
                        if ($item['item_status'] === 'pending')   $itemStatusClass = 'badge-warning';
                        if ($item['item_status'] === 'confirmed') $itemStatusClass = 'badge-info';
                        if ($item['item_status'] === 'shipped')   $itemStatusClass = 'badge-primary';
                        if ($item['item_status'] === 'delivered') $itemStatusClass = 'badge-success';
                        ?>
                        <span class="badge <?php echo $itemStatusClass; ?>"><?php echo sanitize(ucfirst($item['item_status'])); ?></span>
                    </td>
                    <td>
                        <?php echo !empty($item['status_note']) ? sanitize($item['status_note']) : '<span class="text-muted">N/A</span>'; ?>
                    </td>
                    <td>
                        <?php if ($item['item_status'] === 'pending'): ?>
                        <!-- Confirm Item -->
                        <form action="?c=seller&a=orderDetail&id=<?php echo (int)$order['id']; ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="confirm_item">
                            <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-success">Confirm Item</button>
                        </form>

                        <?php elseif ($item['item_status'] === 'confirmed'): ?>
                        <!-- Mark Shipped with Tracking Note -->
                        <div class="ship-form">
                            <form action="?c=seller&a=orderDetail&id=<?php echo (int)$order['id']; ?>" method="POST">
                                <input type="hidden" name="action" value="ship_item">
                                <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                <div class="inline-form-group">
                                    <input type="text" name="tracking_note" class="form-control form-control-sm"
                                           placeholder="Tracking note (optional)">
                                    <button type="submit" class="btn btn-sm btn-primary">Mark Shipped</button>
                                </div>
                            </form>
                        </div>

                        <?php else: ?>
                        <span class="text-muted">No action</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Seller Total -->
        <div class="order-total">
            <?php
            $sellerTotal = 0;
            foreach ($items as $item) {
                $sellerTotal += $item['quantity'] * $item['unit_price'];
            }
            ?>
            <strong>Your Items Total: &#2547; <?php echo number_format($sellerTotal, 2); ?></strong>
        </div>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
