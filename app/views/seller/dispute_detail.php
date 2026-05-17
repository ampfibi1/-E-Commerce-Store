<?php $page_title = 'Dispute #' . (int)$dispute['id']; include APP . '/views/layouts/header.php'; ?>

<div style="max-width:780px;margin:0 auto;">
    <a href="<?php echo BASE_URL; ?>?c=seller&a=disputes" class="btn btn-secondary btn-small" style="margin-bottom:1rem;">&larr; Back to all disputes</a>

    <div class="dashboard-section">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
            <div>
                <h1 style="margin:0;">Dispute #<?php echo (int)$dispute['id']; ?></h1>
                <p style="color:#6b7a99;margin:0.25rem 0 0;">Filed on <?php echo sanitize(substr($dispute['created_at'], 0, 10)); ?></p>
            </div>
            <span class="badge badge-<?php echo $dispute['status'] === 'resolved' ? 'success' : 'warning'; ?>" style="font-size:0.85rem;padding:0.5rem 1rem;">
                <?php echo sanitize($dispute['status']); ?>
            </span>
        </div>

        <h2 style="font-size:1rem;color:#6b7a99;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-top:1.5rem;">Customer</h2>
        <p><b><?php echo sanitize($dispute['customer_name']); ?></b> &middot; <?php echo sanitize($dispute['customer_email']); ?></p>

        <h2 style="font-size:1rem;color:#6b7a99;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-top:1.25rem;">Order</h2>
        <p>
            <b>Order #<?php echo (int)$dispute['order_id']; ?></b><br>
            Total: &#2547; <?php echo number_format((float)$dispute['total_amount'], 2); ?><br>
            Placed: <?php echo sanitize(substr($dispute['order_date'], 0, 10)); ?><br>
            Shipping to: <?php echo nl2br(sanitize($dispute['shipping_address'])); ?>
        </p>

        <h2 style="font-size:1rem;color:#6b7a99;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-top:1.25rem;">Customer's complaint</h2>
        <div style="background:#f9fafc;border-left:3px solid #2c7be5;padding:0.85rem 1rem;border-radius:4px;">
            <?php echo nl2br(sanitize($dispute['description'])); ?>
        </div>

        <h2 style="font-size:1rem;color:#6b7a99;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-top:1.25rem;">Admin note</h2>
        <?php if (!empty($dispute['admin_note'])): ?>
            <div style="background:#fff3cd;border-left:3px solid #856404;padding:0.85rem 1rem;border-radius:4px;">
                <?php echo nl2br(sanitize($dispute['admin_note'])); ?>
            </div>
        <?php else: ?>
            <p style="color:#aaa;">The admin has not posted a resolution note yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
