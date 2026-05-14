<?php $page_title = 'My Orders'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div class="page-header">
    <h1>My Orders</h1>
</div>

<?php if (!empty($orders)): ?>

<div class="card" style="padding:0; overflow:hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <?php
            $status = strtolower($order['status']);
            $badge_map = [
                'pending'    => 'badge-pending',
                'confirmed'  => 'badge-confirmed',
                'processing' => 'badge-processing',
                'shipped'    => 'badge-shipped',
                'delivered'  => 'badge-delivered',
                'cancelled'  => 'badge-cancelled',
                'returned'   => 'badge-returned',
            ];
            $bc = isset($badge_map[$status]) ? $badge_map[$status] : 'badge-pending';
            ?>
            <tr>
                <td><strong>#<?php echo (int)$order['id']; ?></strong></td>
                <td><?php echo sanitize(date('d M Y', strtotime($order['created_at']))); ?></td>
                <td><strong>৳<?php echo number_format((float)$order['total_amount'], 2); ?></strong></td>
                <td><?php echo sanitize(ucwords(str_replace('_', ' ', $order['payment_method']))); ?></td>
                <td>
                    <span class="badge <?php echo $bc; ?>"><?php echo sanitize(ucfirst($order['status'])); ?></span>
                </td>
                <td>
                    <a href="<?php echo BASE_URL; ?>?c=customer&a=orderDetail&id=<?php echo (int)$order['id']; ?>"
                       class="btn btn-primary btn-small">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>

<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon">&#128230;</div>
        <p>You have not placed any orders yet.</p>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-primary" style="margin-top:1rem;">Start Shopping</a>
    </div>
</div>

<?php endif; ?>

<?php include APP . '/views/layouts/footer.php'; ?>
