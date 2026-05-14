<?php $page_title = 'Login'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div class="auth-shell">
    <div class="auth-hero">
        <h1>Welcome back to ShopHub</h1>
        <p class="lead">
            Sign in to track your orders, manage your wishlist, and pick up where you left off.
            One account works for both shopping and selling.
        </p>
        <ul class="benefit-list">
            <li>
                <span class="bi"><?php icon_truck(20); ?></span>
                <span><b>Fast delivery</b> — pick your zone at checkout and get an ETA upfront.</span>
            </li>
            <li>
                <span class="bi"><?php icon_shield(20); ?></span>
                <span><b>Buyer protection</b> — every order is covered with returns &amp; disputes.</span>
            </li>
            <li>
                <span class="bi"><?php icon_tag(20); ?></span>
                <span><b>Seller coupons</b> — apply codes at checkout and save instantly.</span>
            </li>
            <li>
                <span class="bi"><?php icon_heart(20); ?></span>
                <span><b>Wishlist anywhere</b> — save products to a list and find them later.</span>
            </li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <h2>Sign in</h2>
        <p class="sub">Use the email you registered with.</p>

        <?php if (!empty($errors) && is_array($errors) && isset($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo sanitize($errors['general']); ?></div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="<?php echo BASE_URL; ?>?c=auth&a=login" novalidate onsubmit="return validateLoginForm()">

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input
                    type="text"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?php echo isset($old['email']) ? sanitize($old['email']) : ''; ?>"
                    placeholder="you@example.com"
                    autocomplete="username"
                >
                <span class="err" id="err_email">
                    <?php echo (!empty($errors['email'])) ? sanitize($errors['email']) : ''; ?>
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Your password"
                    autocomplete="current-password"
                >
                <span class="err" id="err_password">
                    <?php echo (!empty($errors['password'])) ? sanitize($errors['password']) : ''; ?>
                </span>
            </div>

            <div class="form-group" style="margin-top:1.25rem;">
                <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </div>
        </form>

        <div class="auth-foot">
            Don't have an account?
            <a href="<?php echo BASE_URL; ?>?c=auth&a=register">Register as Customer</a>
            &nbsp;·&nbsp;
            <a href="<?php echo BASE_URL; ?>?c=auth&a=sellerRegister">Register as Seller</a>
        </div>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
