<?php $page_title = 'My Disputes'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div class="page-header">
    <h1>My Disputes</h1>
</div>

<!-- Existing Disputes Table -->
<div class="card" style="padding:0; overflow:hidden; margin-bottom:1.5rem;">
    <div class="card-header" style="padding:1rem 1.25rem;">
        <h3 class="card-title">Submitted Disputes</h3>
    </div>

    <?php if (!empty($disputes)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Order</th>
                <th>Description</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($disputes as $d): ?>
            <?php
            $d_status = strtolower($d['status'] ?? 'open');
            $d_badge = ($d_status === 'resolved') ? 'badge-resolved' : (($d_status === 'open') ? 'badge-open' : 'badge-pending');
            $did = isset($d['dispute_id']) ? (int)$d['dispute_id'] : (isset($d['id']) ? (int)$d['id'] : 0);
            ?>
            <tr>
                <td><?php echo $did; ?></td>
                <td><strong>#<?php echo isset($d['order_id']) ? (int)$d['order_id'] : '—'; ?></strong></td>
                <td>
                    <div style="margin-bottom:0.35rem;"><b>You:</b>
                        <?php echo sanitize(mb_strlen($d['description']) > 80 ? mb_substr($d['description'], 0, 80) . '...' : $d['description']); ?>
                    </div>
                    <?php if (!empty($d['seller_response'])): ?>
                        <?php
                        $sa = isset($d['seller_action']) ? $d['seller_action'] : 'none';
                        $sa_color = ($sa === 'accepted') ? '#28a745' : (($sa === 'rejected') ? '#dc3545' : '#2c7be5');
                        $sa_label = ($sa === 'accepted') ? 'Accepted' : (($sa === 'rejected') ? 'Rejected' : 'Replied');
                        ?>
                        <div style="background:#f9fafc;border-left:3px solid <?php echo $sa_color; ?>;padding:0.4rem 0.6rem;border-radius:3px;font-size:0.88rem;">
                            <b style="color:<?php echo $sa_color; ?>;"><?php echo $sa_label; ?> &mdash; Seller:</b>
                            <?php echo sanitize(mb_strlen($d['seller_response']) > 100 ? mb_substr($d['seller_response'], 0, 100) . '...' : $d['seller_response']); ?>
                        </div>
                    <?php else: ?>
                        <div style="color:#aaa;font-size:0.85rem;">Seller hasn't replied yet.</div>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge <?php echo $d_badge; ?>">
                        <?php echo sanitize(ucfirst($d['status'] ?? 'Open')); ?>
                    </span>
                </td>
                <td><?php echo sanitize(date('d M Y', strtotime($d['created_at']))); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state" style="padding:2rem;">
        <div class="empty-state-icon">&#128196;</div>
        <p>You have not raised any disputes yet.</p>
    </div>
    <?php endif; ?>
</div>

<!-- New Dispute Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Raise a New Dispute</h3>
    </div>

    <?php if (!empty($errors) && isset($errors['general'])): ?>
    <div class="alert alert-danger"><?php echo sanitize($errors['general']); ?></div>
    <?php endif; ?>

    <p style="font-size:0.9rem; color:#666; margin-bottom:1rem;">
        If you have an issue with a seller or order, please select the related order and describe the problem.
        Our team will review your dispute and respond within 2-3 business days.
    </p>

    <form id="disputeForm"
          method="POST"
          action="<?php echo BASE_URL; ?>?c=customer&a=disputes"
          novalidate
          onsubmit="return validateDisputeForm()">

        <div class="form-group">
            <label class="form-label" for="order_id">Related Order</label>
            <select id="order_id" name="order_id" class="form-control">
                <option value="">-- Select an order --</option>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $ord): ?>
                    <?php $oid = isset($ord['order_id']) ? (int)$ord['order_id'] : (isset($ord['id']) ? (int)$ord['id'] : 0); ?>
                    <option value="<?php echo $oid; ?>"
                        <?php echo (isset($old['order_id']) && (int)$old['order_id'] === $oid) ? 'selected' : ''; ?>>
                        Order #<?php echo $oid; ?>
                        &mdash;
                        <?php echo sanitize(date('d M Y', strtotime($ord['created_at']))); ?>
                        &mdash;
                        ৳<?php echo number_format((float)$ord['total_amount'], 2); ?>
                    </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <span class="err" id="err_order_id">
                <?php echo (!empty($errors['order_id'])) ? sanitize($errors['order_id']) : ''; ?>
            </span>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Describe the Issue</label>
            <textarea
                id="description"
                name="description"
                class="form-control"
                rows="5"
                placeholder="Please explain the problem in detail — what happened, what you expected, and what went wrong..."
            ><?php echo isset($old['description']) ? sanitize($old['description']) : ''; ?></textarea>
            <span class="err" id="err_description">
                <?php echo (!empty($errors['description'])) ? sanitize($errors['description']) : ''; ?>
            </span>
        </div>

        <button type="submit" class="btn btn-primary">Submit Dispute</button>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=orders" class="btn btn-secondary">View My Orders</a>

    </form>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
