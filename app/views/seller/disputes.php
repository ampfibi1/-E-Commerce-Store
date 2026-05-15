<?php $page_title = 'Disputes'; include APP . '/views/layouts/header.php'; ?>

<div style="max-width:1100px;margin:0 auto;">
    <h2 style="margin-bottom:0.25rem;">Customer Disputes</h2>
    <p style="color:#6b7a99;margin-bottom:1.5rem;">Disputes customers filed against your shop. Click a row to see the full case.</p>

    <?php if (empty($disputes)): ?>
        <div class="empty-state">
            <div class="empty-icon"><?php icon_shield(36); ?></div>
            <h3>No disputes</h3>
            <p>No customer has filed a dispute against your shop yet.</p>
        </div>
    <?php else: ?>
        <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.06);border:1px solid #e6ecf5;overflow:hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Order</th>
                        <th>Description (preview)</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($disputes as $d): ?>
                    <?php $url = BASE_URL . '?c=seller&a=disputeDetail&id=' . (int)$d['id']; ?>
                    <tr style="cursor:pointer;" onclick="window.location='<?php echo $url; ?>';">
                        <td>#<?php echo (int)$d['id']; ?></td>
                        <td><?php echo sanitize($d['customer_name']); ?></td>
                        <td>Order #<?php echo (int)$d['order_id']; ?></td>
                        <td style="max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo sanitize($d['description']); ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $d['status'] === 'resolved' ? 'success' : 'warning'; ?>">
                                <?php echo sanitize($d['status']); ?>
                            </span>
                        </td>
                        <td style="white-space:nowrap;"><?php echo sanitize(substr($d['created_at'], 0, 10)); ?></td>
                        <td><a href="<?php echo $url; ?>" class="btn btn-secondary btn-small">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
