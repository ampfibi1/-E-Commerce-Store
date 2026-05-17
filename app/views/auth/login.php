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
            <p style="color:#6b7a99;font-size:0.88rem;margin:0 0 0.75rem;">Don't have an account?</p>
            <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
                <a href="<?php echo BASE_URL; ?>?c=auth&a=register"
                   style="flex:1;min-width:130px;text-align:center;padding:0.5rem 1rem;border:1.5px solid #2c7be5;color:#2c7be5;border-radius:8px;font-weight:600;font-size:0.88rem;text-decoration:none;transition:all 0.15s;"
                   onmouseover="this.style.background='#2c7be5';this.style.color='#fff';"
                   onmouseout="this.style.background='';this.style.color='#2c7be5';">
                    Customer Account
                </a>
                <a href="<?php echo BASE_URL; ?>?c=auth&a=sellerRegister"
                   style="flex:1;min-width:130px;text-align:center;padding:0.5rem 1rem;border:1.5px solid #28a745;color:#28a745;border-radius:8px;font-weight:600;font-size:0.88rem;text-decoration:none;transition:all 0.15s;"
                   onmouseover="this.style.background='#28a745';this.style.color='#fff';"
                   onmouseout="this.style.background='';this.style.color='#28a745';">
                    Seller Account
                </a>
            </div>
        </div>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
