<?php $page_title = 'My Dashboard'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div class="page-header">
    <h1>My Dashboard</h1>
</div>

<!-- Welcome Card -->
<div class="card" style="background: linear-gradient(135deg, #2c7be5 0%, #1a5cbf 100%); color:#fff;">
    <h2 style="color:#fff; font-size:1.4rem; margin-bottom:0.3rem;">
        Welcome back, <?php echo sanitize($user['name']); ?>!
    </h2>
    <p style="color:rgba(255,255,255,0.85); margin:0; font-size:0.95rem;">
        Manage your orders, wishlist, and profile all in one place.
    </p>
</div>

<!-- Quick Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-value"><?php echo (int)($total_orders ?? count($recent_orders ?? [])); ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <div class="stat-card" style="border-top-color:#28a745;">
        <div class="stat-value"><?php echo (int)$wishlist_count; ?></div>
        <div class="stat-label">Wishlist Items</div>
    </div>
    <div class="stat-card" style="border-top-color:#ffc107;">
        <div class="stat-value">
            <?php
            $pending = 0;
            if (!empty($recent_orders)) {
                foreach ($recent_orders as $ord) {
                    if (strtolower($ord['status']) === 'pending') {
                        $pending++;
                    }
                }
            }
            echo $pending;
            ?>
        </div>
        <div class="stat-label">Pending Orders</div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Orders</h3>
    </div>

    <?php if (!empty($recent_orders)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
            <tr>
                <td><strong>#<?php echo (int)$order['id']; ?></strong></td>
                <td><?php echo sanitize(date('d M Y', strtotime($order['created_at']))); ?></td>
                <td><strong>৳<?php echo number_format((float)$order['total_amount'], 2); ?></strong></td>
                <td>
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
                    $badge_class = isset($badge_map[$status]) ? $badge_map[$status] : 'badge-pending';
                    ?>
                    <span class="badge <?php echo $badge_class; ?>"><?php echo sanitize($order['status']); ?></span>
                </td>
                <td>
                    <a href="<?php echo BASE_URL; ?>?c=customer&a=orderDetail&id=<?php echo (int)$order['id']; ?>" class="btn btn-primary btn-small">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="margin-top:0.75rem;">
        <a href="<?php echo BASE_URL; ?>?c=customer&a=orders" class="btn btn-secondary btn-small">View All Orders</a>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">&#128722;</div>
        <p>You have not placed any orders yet.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Links -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Links</h3>
    </div>
    <div style="display:flex; gap:0.85rem; flex-wrap:wrap;">
        <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-primary">Browse Products</a>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=wishlist" class="btn btn-secondary">My Wishlist (<?php echo (int)$wishlist_count; ?>)</a>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=orders" class="btn btn-secondary">All Orders</a>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=addresses" class="btn btn-secondary">Manage Addresses</a>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=profile" class="btn btn-secondary">Edit Profile</a>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=disputes" class="btn btn-secondary">My Disputes</a>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
