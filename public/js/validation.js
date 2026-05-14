/* ============================================================
   ShopHub — validation.js
   Legacy JS: var, XMLHttpRequest, no arrow functions,
   no template literals, no let/const
   ============================================================ */

/* ---- Utility functions ----------------------------------- */

function showErr(id, msg) {
    var el = document.getElementById(id);
    if (el) {
        el.textContent = msg;
    }
}

function clearErrs(formId) {
    var form = document.getElementById(formId);
    if (!form) { return; }
    var spans = form.querySelectorAll('.err');
    for (var i = 0; i < spans.length; i++) {
        spans[i].textContent = '';
    }
}

function isEmpty(val) {
    return val === null || val === undefined || val.trim() === '';
}

function isValidEmail(val) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(val.trim());
}

function isValidPhone(val) {
    var re = /^01[0-9]{9}$/;
    return re.test(val.trim());
}

function isStrongPassword(val) {
    return val.length >= 8;
}

function isNumber(val) {
    return !isNaN(parseFloat(val)) && isFinite(val);
}

function isPositive(val) {
    return isNumber(val) && parseFloat(val) > 0;
}

function isNonNegative(val) {
    return isNumber(val) && parseFloat(val) >= 0;
}

function getVal(id) {
    var el = document.getElementById(id);
    return el ? el.value : '';
}

/* ---- validateLoginForm ----------------------------------- */
function validateLoginForm() {
    clearErrs('loginForm');
    var ok = true;
    var email = getVal('email');
    var password = getVal('password');

    if (isEmpty(email)) {
        showErr('err_email', 'Email is required.');
        ok = false;
    } else if (!isValidEmail(email)) {
        showErr('err_email', 'Please enter a valid email address.');
        ok = false;
    }

    if (isEmpty(password)) {
        showErr('err_password', 'Password is required.');
        ok = false;
    }

    return ok;
}

/* ---- validateRegisterForm -------------------------------- */
function validateRegisterForm() {
    clearErrs('registerForm');
    var ok = true;
    var name = getVal('name');
    var email = getVal('email');
    var phone = getVal('phone');
    var password = getVal('password');
    var confirmPassword = getVal('confirm_password');

    if (isEmpty(name)) {
        showErr('err_name', 'Full name is required.');
        ok = false;
    }

    if (isEmpty(email)) {
        showErr('err_email', 'Email is required.');
        ok = false;
    } else if (!isValidEmail(email)) {
        showErr('err_email', 'Please enter a valid email address.');
        ok = false;
    }

    if (isEmpty(phone)) {
        showErr('err_phone', 'Phone number is required.');
        ok = false;
    } else if (!isValidPhone(phone)) {
        showErr('err_phone', 'Enter a valid 11-digit Bangladesh phone number (e.g. 01xxxxxxxxx).');
        ok = false;
    }

    if (isEmpty(password)) {
        showErr('err_password', 'Password is required.');
        ok = false;
    } else if (!isStrongPassword(password)) {
        showErr('err_password', 'Password must be at least 8 characters.');
        ok = false;
    }

    if (isEmpty(confirmPassword)) {
        showErr('err_confirm_password', 'Please confirm your password.');
        ok = false;
    } else if (password !== confirmPassword) {
        showErr('err_confirm_password', 'Passwords do not match.');
        ok = false;
    }

    return ok;
}

/* ---- validateSellerRegisterForm -------------------------- */
function validateSellerRegisterForm() {
    clearErrs('sellerRegForm');
    var ok = true;
    var name = getVal('name');
    var email = getVal('email');
    var phone = getVal('phone');
    var password = getVal('password');
    var confirmPassword = getVal('confirm_password');
    var shopName = getVal('shop_name');
    var shopDesc = getVal('shop_description');
    var address = getVal('address');

    if (isEmpty(name)) {
        showErr('err_name', 'Full name is required.');
        ok = false;
    }

    if (isEmpty(email)) {
        showErr('err_email', 'Email is required.');
        ok = false;
    } else if (!isValidEmail(email)) {
        showErr('err_email', 'Please enter a valid email address.');
        ok = false;
    }

    if (isEmpty(phone)) {
        showErr('err_phone', 'Phone number is required.');
        ok = false;
    } else if (!isValidPhone(phone)) {
        showErr('err_phone', 'Enter a valid 11-digit Bangladesh phone number (e.g. 01xxxxxxxxx).');
        ok = false;
    }

    if (isEmpty(password)) {
        showErr('err_password', 'Password is required.');
        ok = false;
    } else if (!isStrongPassword(password)) {
        showErr('err_password', 'Password must be at least 8 characters.');
        ok = false;
    }

    if (isEmpty(confirmPassword)) {
        showErr('err_confirm_password', 'Please confirm your password.');
        ok = false;
    } else if (password !== confirmPassword) {
        showErr('err_confirm_password', 'Passwords do not match.');
        ok = false;
    }

    if (isEmpty(shopName)) {
        showErr('err_shop_name', 'Shop name is required.');
        ok = false;
    }

    if (isEmpty(shopDesc)) {
        showErr('err_shop_description', 'Shop description is required.');
        ok = false;
    }

    if (isEmpty(address)) {
        showErr('err_address', 'Shop address is required.');
        ok = false;
    }

    return ok;
}

/* ---- validateProfileInfoForm ----------------------------- */
function validateProfileInfoForm() {
    clearErrs('profileInfoForm');
    var ok = true;
    var name = getVal('name');
    var email = getVal('email');
    var phone = getVal('phone');

    if (isEmpty(name)) {
        showErr('err_name', 'Full name is required.');
        ok = false;
    }

    if (isEmpty(email)) {
        showErr('err_email', 'Email is required.');
        ok = false;
    } else if (!isValidEmail(email)) {
        showErr('err_email', 'Please enter a valid email address.');
        ok = false;
    }

    if (isEmpty(phone)) {
        showErr('err_phone', 'Phone number is required.');
        ok = false;
    } else if (!isValidPhone(phone)) {
        showErr('err_phone', 'Enter a valid 11-digit Bangladesh phone number.');
        ok = false;
    }

    return ok;
}

/* ---- validatePasswordForm -------------------------------- */
function validatePasswordForm() {
    clearErrs('passwordForm');
    var ok = true;
    var current = getVal('current_password');
    var newPass = getVal('new_password');
    var confirm = getVal('confirm_password');

    if (isEmpty(current)) {
        showErr('err_current_password', 'Current password is required.');
        ok = false;
    }

    if (isEmpty(newPass)) {
        showErr('err_new_password', 'New password is required.');
        ok = false;
    } else if (!isStrongPassword(newPass)) {
        showErr('err_new_password', 'New password must be at least 8 characters.');
        ok = false;
    }

    if (isEmpty(confirm)) {
        showErr('err_confirm_password', 'Please confirm your new password.');
        ok = false;
    } else if (newPass !== confirm) {
        showErr('err_confirm_password', 'Passwords do not match.');
        ok = false;
    }

    return ok;
}

/* ---- validateAddressForm --------------------------------- */
function validateAddressForm() {
    clearErrs('addressForm');
    var ok = true;
    var label = getVal('label');
    var addressLine = getVal('address_line');
    var city = getVal('city');
    var zip = getVal('zip');

    if (isEmpty(label)) {
        showErr('err_label', 'Address label is required (e.g. Home, Office).');
        ok = false;
    }

    if (isEmpty(addressLine)) {
        showErr('err_address_line', 'Address line is required.');
        ok = false;
    }

    if (isEmpty(city)) {
        showErr('err_city', 'City is required.');
        ok = false;
    }

    if (isEmpty(zip)) {
        showErr('err_zip', 'ZIP / postal code is required.');
        ok = false;
    }

    return ok;
}

/* ---- validateCheckoutForm -------------------------------- */
function validateCheckoutForm() {
    clearErrs('checkoutForm');
    var ok = true;
    var zoneEl = document.getElementById('zone_id');
    var zoneVal = zoneEl ? zoneEl.value : '';
    var paymentEls = document.querySelectorAll('input[name="payment_method"]');
    var paymentSelected = false;

    if (isEmpty(zoneVal) || zoneVal === '0' || zoneVal === '') {
        showErr('err_zone_id', 'Please select a delivery zone.');
        ok = false;
    }

    for (var i = 0; i < paymentEls.length; i++) {
        if (paymentEls[i].checked) {
            paymentSelected = true;
            break;
        }
    }

    if (!paymentSelected) {
        showErr('err_payment_method', 'Please select a payment method.');
        ok = false;
    }

    return ok;
}

/* ---- validateProductForm --------------------------------- */
function validateProductForm() {
    clearErrs('productForm');
    var ok = true;
    var name = getVal('name');
    var description = getVal('description');
    var price = getVal('price');
    var stockQty = getVal('stock_qty');
    var categoryEl = document.getElementById('category_id');
    var categoryVal = categoryEl ? categoryEl.value : '';

    if (isEmpty(name)) {
        showErr('err_name', 'Product name is required.');
        ok = false;
    }

    if (isEmpty(description)) {
        showErr('err_description', 'Product description is required.');
        ok = false;
    }

    if (isEmpty(price)) {
        showErr('err_price', 'Price is required.');
        ok = false;
    } else if (!isPositive(price)) {
        showErr('err_price', 'Price must be a positive number.');
        ok = false;
    }

    if (isEmpty(stockQty)) {
        showErr('err_stock_qty', 'Stock quantity is required.');
        ok = false;
    } else if (!isNonNegative(stockQty)) {
        showErr('err_stock_qty', 'Stock quantity cannot be negative.');
        ok = false;
    }

    if (isEmpty(categoryVal) || categoryVal === '0' || categoryVal === '') {
        showErr('err_category_id', 'Please select a category.');
        ok = false;
    }

    /* Only check for primary image on add form */
    var primaryImageRequired = document.getElementById('primary_image_required');
    if (primaryImageRequired) {
        var fileEl = document.getElementById('primary_image');
        if (fileEl && (!fileEl.files || fileEl.files.length === 0)) {
            showErr('err_primary_image', 'Please upload a primary product image.');
            ok = false;
        }
    }

    return ok;
}

/* ---- validateCouponForm ---------------------------------- */
function validateCouponForm() {
    clearErrs('couponForm');
    var ok = true;
    var code = getVal('code');
    var discountPct = getVal('discount_pct');
    var validUntil = getVal('valid_until');

    if (isEmpty(code)) {
        showErr('err_code', 'Coupon code is required.');
        ok = false;
    } else {
        var alphanumRe = /^[A-Z0-9]+$/i;
        if (!alphanumRe.test(code.trim())) {
            showErr('err_code', 'Coupon code may only contain letters and numbers.');
            ok = false;
        }
    }

    if (isEmpty(discountPct)) {
        showErr('err_discount_pct', 'Discount percentage is required.');
        ok = false;
    } else {
        var pct = parseFloat(discountPct);
        if (isNaN(pct) || pct < 1 || pct > 100) {
            showErr('err_discount_pct', 'Discount must be between 1 and 100.');
            ok = false;
        }
    }

    if (isEmpty(validUntil)) {
        showErr('err_valid_until', 'Expiry date is required.');
        ok = false;
    } else {
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var expiry = new Date(validUntil);
        if (expiry <= today) {
            showErr('err_valid_until', 'Expiry date must be in the future.');
            ok = false;
        }
    }

    return ok;
}

/* ---- validateReviewForm ---------------------------------- */
function validateReviewForm() {
    clearErrs('reviewForm');
    var ok = true;
    var ratingEls = document.querySelectorAll('select[name="rating"], input[name="rating"]');
    var ratingVal = '';

    if (ratingEls.length > 0) {
        ratingVal = ratingEls[0].value;
    }

    if (isEmpty(ratingVal) || ratingVal === '0' || ratingVal === '') {
        showErr('err_rating', 'Please select a rating (1-5 stars).');
        ok = false;
    } else {
        var r = parseInt(ratingVal, 10);
        if (isNaN(r) || r < 1 || r > 5) {
            showErr('err_rating', 'Rating must be between 1 and 5.');
            ok = false;
        }
    }

    var reviewText = getVal('review_text');
    if (isEmpty(reviewText)) {
        showErr('err_review_text', 'Review text is required.');
        ok = false;
    }

    return ok;
}

/* ---- validateDisputeForm --------------------------------- */
function validateDisputeForm() {
    clearErrs('disputeForm');
    var ok = true;
    var orderEl = document.getElementById('order_id');
    var orderVal = orderEl ? orderEl.value : '';
    var description = getVal('description');

    if (isEmpty(orderVal) || orderVal === '0' || orderVal === '') {
        showErr('err_order_id', 'Please select an order.');
        ok = false;
    }

    if (isEmpty(description)) {
        showErr('err_description', 'Dispute description is required.');
        ok = false;
    }

    return ok;
}

/* ---- validateReturnForm ---------------------------------- */
function validateReturnForm() {
    clearErrs('returnForm');
    var ok = true;
    var itemEl = document.getElementById('order_item_id');
    var itemVal = itemEl ? itemEl.value : '';
    var reason = getVal('reason');

    if (isEmpty(itemVal) || itemVal === '0' || itemVal === '') {
        showErr('err_order_item_id', 'Please select an order item.');
        ok = false;
    }

    if (isEmpty(reason)) {
        showErr('err_reason', 'Return reason is required.');
        ok = false;
    }

    return ok;
}
