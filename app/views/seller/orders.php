<?php
$page_title = 'My Orders';
include APP . '/views/layouts/header.php';
?>

<div class="seller-orders">
    <h1>Orders</h1>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="?c=seller&a=orders&status=all"
           class="tab <?php echo ($filter_status === 'all') ? 'active' : ''; ?>">All</a>
        <a href="?c=seller&a=orders&status=pending"
           class="tab <?php echo ($filter_status === 'pending') ? 'active' : ''; ?>">Pending</a>
        <a href="?c=seller&a=orders&status=confirmed"
           class="tab <?php echo ($filter_status === 'confirmed') ? 'active' : ''; ?>">Confirmed</a>
        <a href="?c=seller&a=orders&status=shipped"
           class="tab <?php echo ($filter_status === 'shipped') ? 'active' : ''; ?>">Shipped</a>
        <a href="?c=seller&a=orders&status=delivered"
           class="tab <?php echo ($filter_status === 'delivered') ? 'active' : ''; ?>">Delivered</a>
    </div>

    <?php if (!empty($orders)): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Zone</th>
                <th># Items</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo (int)$order['id']; ?></td>
                <td><?php echo sanitize($order['customer_name']); ?></td>
                <td><?php echo sanitize($order['created_at']); ?></td>
                <td>&#2547; <?php echo number_format($order['total'], 2); ?></td>
                <td><?php echo sanitize($order['zone']); ?></td>
                <td><?php echo (int)$order['item_count']; ?></td>
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
                <td>
                    <a href="?c=seller&a=orderDetail&id=<?php echo (int)$order['id']; ?>" class="btn btn-sm btn-info">View Detail</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <p>No orders found<?php echo ($filter_status !== 'all') ? ' with status "' . sanitize($filter_status) . '"' : ''; ?>.</p>
    </div>
    <?php endif; ?>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
