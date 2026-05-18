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

        <h2 style="font-size:1rem;color:#6b7a99;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-top:1.25rem;">Your response</h2>
        <?php
        $seller_action = isset($dispute['seller_action']) ? $dispute['seller_action'] : 'none';
        ?>
        <?php if (!empty($dispute['seller_response'])): ?>
            <?php
            if ($seller_action === 'accepted') { $accent = '#28a745'; $bg = '#e7f5ee'; $label = 'Accepted'; }
            elseif ($seller_action === 'rejected') { $accent = '#dc3545'; $bg = '#fbeaec'; $label = 'Rejected'; }
            else { $accent = '#2c7be5'; $bg = '#eaf2fb'; $label = ''; }
            ?>
            <div style="background:<?php echo $bg; ?>;border-left:3px solid <?php echo $accent; ?>;padding:0.85rem 1rem;border-radius:4px;">
                <?php if ($label !== ''): ?>
                    <div style="font-weight:600;color:<?php echo $accent; ?>;margin-bottom:0.4rem;text-transform:uppercase;letter-spacing:0.04em;font-size:0.85rem;">
                        <?php echo $label; ?>
                    </div>
                <?php endif; ?>
                <?php echo nl2br(sanitize($dispute['seller_response'])); ?>
                <div style="color:#6b7a99;font-size:0.8rem;margin-top:0.5rem;">
                    Submitted <?php echo sanitize(substr((string)$dispute['seller_responded_at'], 0, 16)); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($dispute['status'] !== 'resolved'): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>?c=seller&a=disputeDetail&id=<?php echo (int)$dispute['id']; ?>" style="margin-top:0.75rem;">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><?php echo sanitize($errors['general']); ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="form-label" for="seller_response">
                        <?php echo !empty($dispute['seller_response']) ? 'Update your response' : 'Reply to this dispute'; ?>
                    </label>
                    <textarea
                        id="seller_response"
                        name="seller_response"
                        class="form-control"
                        rows="4"
                        placeholder="Explain what happened — if accepting, mention refund/replacement; if rejecting, state why the claim is invalid..."
                    ><?php echo isset($_POST['seller_response']) ? sanitize($_POST['seller_response']) : ''; ?></textarea>
                    <?php if (!empty($errors['seller_response'])): ?>
                        <span class="err"><?php echo sanitize($errors['seller_response']); ?></span>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <button type="submit" name="action" value="accepted" class="btn btn-success">
                        &#10003; Accept (resolve)
                    </button>
                    <button type="submit" name="action" value="rejected" class="btn btn-danger"
                            onclick="return confirm('Reject this dispute? It will be escalated to the admin for review.');">
                        &#10007; Reject (escalate)
                    </button>
                </div>
                <p style="color:#6b7a99;font-size:0.8rem;margin-top:0.5rem;">
                    <b>Accept</b> closes the dispute as resolved. <b>Reject</b> keeps it open and flags it for the admin.
                </p>
            </form>
        <?php endif; ?>

        <h2 style="font-size:1rem;color:#6b7a99;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-top:1.5rem;">Admin note</h2>
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
