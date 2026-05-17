<?php
$page_title = 'Return Requests';
include APP . '/views/layouts/header.php';
?>

<div class="seller-returns">
    <h1>Return Requests</h1>

    <?php if (!empty($returns)): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Return #</th>
                <th>Order #</th>
                <th>Product</th>
                <th>Customer</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($returns as $ret): ?>
            <tr>
                <td>#<?php echo (int)$ret['id']; ?></td>
                <td>
                    <a href="?c=seller&a=orderDetail&id=<?php echo (int)$ret['order_id']; ?>">#<?php echo (int)$ret['order_id']; ?></a>
                </td>
                <td><?php echo sanitize($ret['product_name']); ?></td>
                <td><?php echo sanitize($ret['customer_name']); ?></td>
                <td>
                    <?php
                    $reason = $ret['reason'];
                    if (strlen($reason) > 60) {
                        $reason = substr($reason, 0, 60) . '...';
                    }
                    echo sanitize($reason);
                    ?>
                </td>
                <td>
                    <?php
                    $retStatus = $ret['status'];
                    $badgeClass = 'badge-secondary';
                    if ($retStatus === 'pending')   $badgeClass = 'badge-warning';
                    if ($retStatus === 'approved')  $badgeClass = 'badge-success';
                    if ($retStatus === 'rejected')  $badgeClass = 'badge-danger';
                    if ($retStatus === 'completed') $badgeClass = 'badge-primary';
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo sanitize(ucfirst($retStatus)); ?></span>
                </td>
                <td><?php echo sanitize($ret['created_at']); ?></td>
                <td>
                    <?php if ($ret['status'] === 'pending'): ?>
                    <!-- Approve -->
                    <form action="?c=seller&a=returns" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="return_id" value="<?php echo (int)$ret['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-success"
                                onclick="return confirm('Approve this return request?')">Approve</button>
                    </form>
                    <!-- Reject -->
                    <form action="?c=seller&a=returns" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="return_id" value="<?php echo (int)$ret['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Reject this return request?')">Reject</button>
                    </form>
                    <?php else: ?>
                    <span class="text-muted">No action</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <p>No return requests at this time.</p>
    </div>
    <?php endif; ?>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
