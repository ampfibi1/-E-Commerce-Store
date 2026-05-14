<?php
$page_title = 'Manage Coupons';
include APP . '/views/layouts/header.php';
?>

<div class="seller-coupons">
    <h1>Manage Coupons</h1>

    <!-- Coupons Table -->
    <?php if (!empty($coupons)): ?>
    <div class="coupons-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount %</th>
                    <th>Max Uses</th>
                    <th>Used</th>
                    <th>Valid Until</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $coupon): ?>
                <tr id="coupon-row-<?php echo (int)$coupon['id']; ?>">
                    <td><?php echo sanitize($coupon['code']); ?></td>
                    <td><?php echo number_format($coupon['discount_pct'], 1); ?>%</td>
                    <td><?php echo ($coupon['max_uses'] == 0) ? 'Unlimited' : (int)$coupon['max_uses']; ?></td>
                    <td><?php echo (int)$coupon['uses_count']; ?></td>
                    <td><?php echo sanitize($coupon['valid_until']); ?></td>
                    <td>
                        <button type="button"
                                id="toggle-btn-<?php echo (int)$coupon['id']; ?>"
                                class="btn btn-sm <?php echo $coupon['is_active'] ? 'btn-success' : 'btn-secondary'; ?>"
                                onclick="toggleCoupon(<?php echo (int)$coupon['id']; ?>)">
                            <?php echo $coupon['is_active'] ? 'Active' : 'Inactive'; ?>
                        </button>
                    </td>
                    <td>
                        <form action="?c=seller&a=coupons&do=delete&id=<?php echo (int)$coupon['id']; ?>" method="POST" style="display:inline;"
                              onsubmit="return confirm('Delete this coupon?')">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <p>You have not created any coupons yet.</p>
    </div>
    <?php endif; ?>

    <!-- Add Coupon Form -->
    <div class="add-coupon-card">
        <h2>Add New Coupon</h2>

        <form action="?c=seller&a=coupons" method="POST" novalidate id="couponForm"
              onsubmit="return validateCouponForm()">

            <input type="hidden" name="action" value="add_coupon">

            <!-- Code -->
            <div class="form-group">
                <label for="code">Coupon Code</label>
                <input type="text" id="code" name="code" class="form-control"
                       value="<?php echo sanitize(isset($old['code']) ? $old['code'] : ''); ?>">
                <span class="err" id="err_code">
                    <?php echo isset($errors['code']) ? sanitize($errors['code']) : ''; ?>
                </span>
            </div>

            <!-- Discount Percentage -->
            <div class="form-group">
                <label for="discount_pct">Discount Percentage (%)</label>
                <input type="text" id="discount_pct" name="discount_pct" class="form-control"
                       value="<?php echo sanitize(isset($old['discount_pct']) ? $old['discount_pct'] : ''); ?>">
                <span class="err" id="err_discount_pct">
                    <?php echo isset($errors['discount_pct']) ? sanitize($errors['discount_pct']) : ''; ?>
                </span>
            </div>

            <!-- Max Uses -->
            <div class="form-group">
                <label for="max_uses">Max Uses <small>(0 = unlimited)</small></label>
                <input type="text" id="max_uses" name="max_uses" class="form-control"
                       value="<?php echo sanitize(isset($old['max_uses']) ? $old['max_uses'] : '0'); ?>">
                <span class="err" id="err_max_uses">
                    <?php echo isset($errors['max_uses']) ? sanitize($errors['max_uses']) : ''; ?>
                </span>
            </div>

            <!-- Valid Until -->
            <div class="form-group">
                <label for="valid_until">Valid Until</label>
                <input type="text" id="valid_until" name="valid_until" class="form-control"
                       value="<?php echo sanitize(isset($old['valid_until']) ? $old['valid_until'] : ''); ?>">
                <span class="err" id="err_valid_until">
                    <?php echo isset($errors['valid_until']) ? sanitize($errors['valid_until']) : ''; ?>
                </span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Coupon</button>
            </div>

        </form>
    </div>
</div>

<script>
function toggleCoupon(couponId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL; ?>../ajax/toggle_coupon.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                return;
            }
            var btn = document.getElementById('toggle-btn-' + couponId);
            if (btn) {
                if (response.is_active == 1) {
                    btn.innerHTML = 'Active';
                    btn.className = 'btn btn-sm btn-success';
                } else {
                    btn.innerHTML = 'Inactive';
                    btn.className = 'btn btn-sm btn-secondary';
                }
            }
        }
    };
    xhr.send('coupon_id=' + couponId + '&sid=<?php echo (int)$_SESSION['sid']; ?>');
}

function validateCouponForm() {
    var valid = true;

    var code         = document.getElementById('code').value;
    var discountPct  = document.getElementById('discount_pct').value;
    var maxUses      = document.getElementById('max_uses').value;
    var validUntil   = document.getElementById('valid_until').value;

    document.getElementById('err_code').innerHTML         = '';
    document.getElementById('err_discount_pct').innerHTML = '';
    document.getElementById('err_max_uses').innerHTML     = '';
    document.getElementById('err_valid_until').innerHTML  = '';

    if (code.trim() === '') {
        document.getElementById('err_code').innerHTML = 'Coupon code is required.';
        valid = false;
    }

    if (discountPct.trim() === '') {
        document.getElementById('err_discount_pct').innerHTML = 'Discount percentage is required.';
        valid = false;
    } else if (isNaN(discountPct) || parseFloat(discountPct) <= 0 || parseFloat(discountPct) > 100) {
        document.getElementById('err_discount_pct').innerHTML = 'Discount must be between 1 and 100.';
        valid = false;
    }

    if (maxUses.trim() === '') {
        document.getElementById('err_max_uses').innerHTML = 'Max uses is required. Enter 0 for unlimited.';
        valid = false;
    } else if (isNaN(maxUses) || parseInt(maxUses) < 0) {
        document.getElementById('err_max_uses').innerHTML = 'Max uses must be 0 or a positive number.';
        valid = false;
    }

    if (validUntil.trim() === '') {
        document.getElementById('err_valid_until').innerHTML = 'Valid until date is required.';
        valid = false;
    }

    return valid;
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
