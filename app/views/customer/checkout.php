<?php $page_title = 'Checkout'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div class="page-header">
    <h1>Checkout</h1>
</div>

<?php if (!empty($errors) && isset($errors['general'])): ?>
<div class="alert alert-danger"><?php echo sanitize($errors['general']); ?></div>
<?php endif; ?>

<form method="POST"
      action="<?php echo BASE_URL; ?>?c=customer&a=checkout"
      id="checkoutForm"
      novalidate
      onsubmit="return validateCheckoutForm()">

<div class="checkout-layout">

    <!-- Left: Checkout Form -->
    <div class="checkout-form-section">

        <!-- Shipping Address -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Shipping Address</h3>
            </div>

            <?php if (!empty($addresses)): ?>
            <div class="address-options" style="margin-bottom:0.75rem;">
                <?php foreach ($addresses as $addr): ?>
                <label>
                    <input type="radio" name="shipping_address_id"
                           value="<?php echo (int)$addr['id']; ?>"
                           <?php echo ($addr['is_default'] ? 'checked' : ''); ?>>
                    <strong><?php echo sanitize($addr['label']); ?></strong>:
                    <?php echo sanitize($addr['address_line']); ?>,
                    <?php echo sanitize($addr['city']); ?> <?php echo sanitize($addr['zip']); ?>
                </label>
                <?php endforeach; ?>
                <label>
                    <input type="radio" name="shipping_address_id" value="0" id="newAddressRadio">
                    Enter a new address
                </label>
            </div>
            <?php endif; ?>

            <div id="newAddressFields" style="<?php echo empty($addresses) ? '' : 'display:none;'; ?>">
                <div class="form-group">
                    <label class="form-label" for="new_address">New Delivery Address</label>
                    <textarea
                        id="new_address"
                        name="new_address"
                        class="form-control"
                        rows="2"
                        placeholder="Enter full delivery address..."
                    ><?php echo isset($old['new_address']) ? sanitize($old['new_address']) : ''; ?></textarea>
                </div>
            </div>

            <span class="err" id="err_shipping_address">
                <?php echo (!empty($errors['shipping_address'])) ? sanitize($errors['shipping_address']) : ''; ?>
            </span>
        </div>

        <!-- Delivery Zone -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Delivery Zone</h3>
            </div>

            <div class="form-group">
                <label class="form-label" for="zone_id">Select Delivery Zone</label>
                <select id="zone_id" name="zone_id" class="form-control" onchange="updateDeliveryFee(this)">
                    <option value="">-- Select zone --</option>
                    <?php if (!empty($zones)): ?>
                        <?php foreach ($zones as $zone): ?>
                        <option
                            value="<?php echo (int)$zone['id']; ?>"
                            data-fee="<?php echo (float)$zone['delivery_fee']; ?>"
                            data-days="<?php echo (int)$zone['estimated_days']; ?>"
                            <?php echo (isset($old['zone_id']) && $old['zone_id'] == $zone['id']) ? 'selected' : ''; ?>
                        >
                            <?php echo sanitize($zone['zone_name']); ?>
                            &mdash; ৳<?php echo number_format((float)$zone['delivery_fee'], 2); ?>
                            (<?php echo (int)$zone['estimated_days']; ?> day<?php echo $zone['estimated_days'] > 1 ? 's' : ''; ?>)
                        </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <span class="err" id="err_zone_id">
                    <?php echo (!empty($errors['zone_id'])) ? sanitize($errors['zone_id']) : ''; ?>
                </span>
            </div>

            <div id="deliveryEstimate" style="font-size:0.88rem; color:#666; display:none;">
                Estimated delivery: <strong id="estimateDays"></strong> days
            </div>
        </div>

        <!-- Payment Method -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Payment Method</h3>
            </div>

            <div class="payment-options">
                <label>
                    <input type="radio" name="payment_method" value="cash_on_delivery"
                        <?php echo (!isset($old['payment_method']) || $old['payment_method'] === 'cash_on_delivery') ? 'checked' : ''; ?>>
                    &#128181; Cash on Delivery
                </label>
                <label>
                    <input type="radio" name="payment_method" value="card"
                        <?php echo (isset($old['payment_method']) && $old['payment_method'] === 'card') ? 'checked' : ''; ?>>
                    &#128179; Card (pay on delivery)
                </label>
            </div>
            <span class="err" id="err_payment_method">
                <?php echo (!empty($errors['payment_method'])) ? sanitize($errors['payment_method']) : ''; ?>
            </span>
        </div>

        <!-- Coupon Code -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Coupon Code <span class="text-muted" style="font-weight:400; font-size:0.88rem;">(Optional)</span></h3>
            </div>

            <div class="coupon-row">
                <input
                    type="text"
                    id="couponCode"
                    name="coupon_code"
                    class="form-control"
                    placeholder="Enter coupon code"
                    value="<?php echo isset($old['coupon_code']) ? sanitize($old['coupon_code']) : ''; ?>"
                >
                <button type="button" class="btn btn-secondary" onclick="applyCoupon()">Apply</button>
            </div>
            <div id="couponMsg" class="coupon-status"></div>
            <!-- Hidden fields set by AJAX -->
            <input type="hidden" id="coupon_id" name="coupon_id" value="<?php echo isset($old['coupon_id']) ? (int)$old['coupon_id'] : '0'; ?>">
            <input type="hidden" id="discount_amount" name="discount_amount" value="<?php echo isset($old['discount_amount']) ? (float)$old['discount_amount'] : '0'; ?>">
        </div>

    </div><!-- /.checkout-form-section -->

    <!-- Right: Order Summary -->
    <div class="checkout-summary-section">
        <div class="card checkout-summary">
            <div class="card-header">
                <h3 class="card-title">Order Summary</h3>
            </div>

            <?php if (!empty($cart_items)): ?>
                <?php foreach ($cart_items as $item): ?>
                <div class="summary-row">
                    <span><?php echo sanitize($item['name']); ?> &times;<?php echo (int)$item['qty']; ?></span>
                    <span>৳<?php echo number_format((float)$item['subtotal'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="summary-row" style="margin-top:0.5rem; padding-top:0.5rem; border-top:2px solid #eee;">
                <span>Subtotal</span>
                <span>৳<?php echo number_format((float)$subtotal, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery Fee</span>
                <span id="summaryDeliveryFee">৳0.00</span>
            </div>
            <div class="summary-row" id="discountRow" style="display:none;">
                <span>Discount</span>
                <span id="summaryDiscount" style="color:#28a745;">-৳0.00</span>
            </div>
            <div class="summary-total-row">
                <span>Total</span>
                <span id="summaryTotal">৳<?php echo number_format((float)$subtotal, 2); ?></span>
            </div>

            <div style="margin-top:1.25rem;">
                <button type="submit" class="btn btn-primary w-100" style="font-size:1rem; padding:0.75rem;">
                    Place Order
                </button>
            </div>
        </div>
    </div>

</div><!-- /.checkout-layout -->

</form>

<script>
var cartSubtotal = <?php echo (float)$subtotal; ?>;
var currentDeliveryFee = 0;
var currentDiscount = 0;

function updateDeliveryFee(selectEl) {
    var opt = selectEl.options[selectEl.selectedIndex];
    var fee = parseFloat(opt.getAttribute('data-fee')) || 0;
    var days = opt.getAttribute('data-days') || '';
    currentDeliveryFee = fee;

    var feeEl = document.getElementById('summaryDeliveryFee');
    if (feeEl) { feeEl.textContent = '৳' + fee.toFixed(2); }

    var estEl = document.getElementById('deliveryEstimate');
    var estDaysEl = document.getElementById('estimateDays');
    if (estEl && days) {
        estEl.style.display = 'block';
        if (estDaysEl) { estDaysEl.textContent = days; }
    } else if (estEl) {
        estEl.style.display = 'none';
    }

    recalcTotal();
}

function recalcTotal() {
    var total = cartSubtotal + currentDeliveryFee - currentDiscount;
    if (total < 0) { total = 0; }
    var totalEl = document.getElementById('summaryTotal');
    if (totalEl) { totalEl.textContent = '৳' + total.toFixed(2); }
}

function applyCoupon() {
    var code = document.getElementById('couponCode').value;
    var msgEl = document.getElementById('couponMsg');
    if (!code || code.trim() === '') {
        msgEl.textContent = 'Please enter a coupon code.';
        msgEl.className = 'coupon-status invalid';
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL; ?>../api/validate_coupon.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.valid) {
                    currentDiscount = parseFloat(resp.discount_amount) || 0;
                    document.getElementById('coupon_id').value = resp.coupon_id || 0;
                    document.getElementById('discount_amount').value = currentDiscount;
                    msgEl.textContent = resp.message || 'Coupon applied!';
                    msgEl.className = 'coupon-status valid';
                    var discRow = document.getElementById('discountRow');
                    var discEl = document.getElementById('summaryDiscount');
                    if (discRow) { discRow.style.display = 'flex'; }
                    if (discEl) { discEl.textContent = '-৳' + currentDiscount.toFixed(2); }
                    recalcTotal();
                } else {
                    currentDiscount = 0;
                    document.getElementById('coupon_id').value = 0;
                    document.getElementById('discount_amount').value = 0;
                    msgEl.textContent = resp.message || 'Invalid coupon.';
                    msgEl.className = 'coupon-status invalid';
                    var discRow2 = document.getElementById('discountRow');
                    if (discRow2) { discRow2.style.display = 'none'; }
                    recalcTotal();
                }
            } catch (e) {
                msgEl.textContent = 'Could not validate coupon. Please try again.';
                msgEl.className = 'coupon-status invalid';
            }
        }
    };
    xhr.send('coupon_code=' + encodeURIComponent(code.trim()) + '&subtotal=' + cartSubtotal);
}

/* Show/hide new address textarea */
var newAddrRadio = document.getElementById('newAddressRadio');
if (newAddrRadio) {
    var addressRadios = document.querySelectorAll('input[name="shipping_address_id"]');
    for (var i = 0; i < addressRadios.length; i++) {
        addressRadios[i].addEventListener('change', function() {
            var fields = document.getElementById('newAddressFields');
            if (fields) {
                if (this.value === '0') {
                    fields.style.display = 'block';
                } else {
                    fields.style.display = 'none';
                }
            }
        });
    }
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
