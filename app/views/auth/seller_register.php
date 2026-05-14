<?php $page_title = 'Register as Seller'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div style="max-width:580px; margin:2.5rem auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title text-center">Create Seller Account</h2>
            <p class="text-center text-muted" style="font-size:0.88rem; margin:0.25rem 0 0;">Start selling on ShopHub</p>
        </div>

        <?php if (!empty($errors) && is_array($errors) && isset($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo sanitize($errors['general']); ?></div>
        <?php endif; ?>

        <form id="sellerRegForm"
              method="POST"
              action="<?php echo BASE_URL; ?>?c=auth&a=seller_register"
              enctype="multipart/form-data"
              novalidate
              onsubmit="return validateSellerRegisterForm()">

            <!-- ---- Personal Information ---- -->
            <h3 style="font-size:1rem; color:#555; margin-bottom:0.85rem; padding-bottom:0.4rem; border-bottom:1px solid #eee;">
                Personal Information
            </h3>

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="<?php echo isset($old['name']) ? sanitize($old['name']) : ''; ?>"
                    placeholder="Your full name"
                    autocomplete="name"
                >
                <span class="err" id="err_name">
                    <?php echo (!empty($errors['name'])) ? sanitize($errors['name']) : ''; ?>
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input
                    type="text"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?php echo isset($old['email']) ? sanitize($old['email']) : ''; ?>"
                    placeholder="you@example.com"
                    autocomplete="email"
                >
                <span class="err" id="err_email">
                    <?php echo (!empty($errors['email'])) ? sanitize($errors['email']) : ''; ?>
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="form-control"
                    value="<?php echo isset($old['phone']) ? sanitize($old['phone']) : ''; ?>"
                    placeholder="01XXXXXXXXX"
                    autocomplete="tel"
                >
                <span class="err" id="err_phone">
                    <?php echo (!empty($errors['phone'])) ? sanitize($errors['phone']) : ''; ?>
                </span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Min. 8 characters"
                        autocomplete="new-password"
                    >
                    <span class="err" id="err_password">
                        <?php echo (!empty($errors['password'])) ? sanitize($errors['password']) : ''; ?>
                    </span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Repeat password"
                        autocomplete="new-password"
                    >
                    <span class="err" id="err_confirm_password">
                        <?php echo (!empty($errors['confirm_password'])) ? sanitize($errors['confirm_password']) : ''; ?>
                    </span>
                </div>
            </div>

            <!-- ---- Shop Information ---- -->
            <h3 style="font-size:1rem; color:#555; margin:1.25rem 0 0.85rem; padding-bottom:0.4rem; border-bottom:1px solid #eee;">
                Shop Information
            </h3>

            <div class="form-group">
                <label class="form-label" for="shop_name">Shop Name</label>
                <input
                    type="text"
                    id="shop_name"
                    name="shop_name"
                    class="form-control"
                    value="<?php echo isset($old['shop_name']) ? sanitize($old['shop_name']) : ''; ?>"
                    placeholder="Your shop name"
                >
                <span class="err" id="err_shop_name">
                    <?php echo (!empty($errors['shop_name'])) ? sanitize($errors['shop_name']) : ''; ?>
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="shop_description">Shop Description</label>
                <textarea
                    id="shop_description"
                    name="shop_description"
                    class="form-control"
                    rows="3"
                    placeholder="Describe your shop and what you sell..."
                ><?php echo isset($old['shop_description']) ? sanitize($old['shop_description']) : ''; ?></textarea>
                <span class="err" id="err_shop_description">
                    <?php echo (!empty($errors['shop_description'])) ? sanitize($errors['shop_description']) : ''; ?>
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Shop Address</label>
                <textarea
                    id="address"
                    name="address"
                    class="form-control"
                    rows="2"
                    placeholder="Full shop address..."
                ><?php echo isset($old['address']) ? sanitize($old['address']) : ''; ?></textarea>
                <span class="err" id="err_address">
                    <?php echo (!empty($errors['address'])) ? sanitize($errors['address']) : ''; ?>
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="shop_logo">Shop Logo <span class="text-muted">(Optional)</span></label>
                <input
                    type="file"
                    id="shop_logo"
                    name="shop_logo"
                    class="form-control"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                >
                <small class="text-muted">JPG, PNG, or GIF. Max 2MB.</small>
                <span class="err" id="err_shop_logo">
                    <?php echo (!empty($errors['shop_logo'])) ? sanitize($errors['shop_logo']) : ''; ?>
                </span>
            </div>

            <div class="form-group" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary w-100">Create Seller Account</button>
            </div>

        </form>

        <hr class="divider">

        <p class="text-center" style="font-size:0.92rem;">
            Already have an account? <a href="<?php echo BASE_URL; ?>?c=auth&a=login">Sign in</a>
        </p>
        <p class="text-center" style="font-size:0.92rem;">
            Want to shop instead? <a href="<?php echo BASE_URL; ?>?c=auth&a=register">Register as Customer</a>
        </p>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
