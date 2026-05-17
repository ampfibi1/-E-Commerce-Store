<?php $page_title = 'Register'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div class="auth-shell">
    <div class="auth-hero">
        <h1>Join ShopHub in seconds</h1>
        <p class="lead">
            Create a customer account to shop across hundreds of verified sellers,
            track every order in one place, and save items for later.
        </p>
        <ul class="benefit-list">
            <li>
                <span class="bi"><?php icon_check(20); ?></span>
                <span><b>Free to join</b> — no membership fees, no hidden charges.</span>
            </li>
            <li>
                <span class="bi"><?php icon_box(20); ?></span>
                <span><b>One place for all orders</b> — every purchase, every return, every refund.</span>
            </li>
            <li>
                <span class="bi"><?php icon_heart(20); ?></span>
                <span><b>Save before you buy</b> — build a wishlist and grab the best deals later.</span>
            </li>
            <li>
                <span class="bi"><?php icon_shield(20); ?></span>
                <span><b>Buyer protection</b> — file a dispute on any order, no questions asked.</span>
            </li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <h2>Create your account</h2>
        <p class="sub">All you need is your name, email, and a phone number.</p>

        <?php if (!empty($errors) && is_array($errors) && isset($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo sanitize($errors['general']); ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="<?php echo BASE_URL; ?>?c=auth&a=register" novalidate onsubmit="return validateRegisterForm()">

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control"
                    value="<?php echo isset($old['name']) ? sanitize($old['name']) : ''; ?>"
                    placeholder="Your full name" autocomplete="name">
                <span class="err" id="err_name"><?php echo (!empty($errors['name'])) ? sanitize($errors['name']) : ''; ?></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="text" id="email" name="email" class="form-control"
                    value="<?php echo isset($old['email']) ? sanitize($old['email']) : ''; ?>"
                    placeholder="you@example.com" autocomplete="email">
                <span class="err" id="err_email"><?php echo (!empty($errors['email'])) ? sanitize($errors['email']) : ''; ?></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control"
                    value="<?php echo isset($old['phone']) ? sanitize($old['phone']) : ''; ?>"
                    placeholder="01XXXXXXXXX" autocomplete="tel">
                <span class="err" id="err_phone"><?php echo (!empty($errors['phone'])) ? sanitize($errors['phone']) : ''; ?></span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Min. 8 characters" autocomplete="new-password">
                    <span class="err" id="err_password"><?php echo (!empty($errors['password'])) ? sanitize($errors['password']) : ''; ?></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                        placeholder="Repeat password" autocomplete="new-password">
                    <span class="err" id="err_confirm_password"><?php echo (!empty($errors['confirm_password'])) ? sanitize($errors['confirm_password']) : ''; ?></span>
                </div>
            </div>

            <div class="form-group" style="margin-top:1.25rem;">
                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </div>
        </form>

        <div class="auth-foot">
            Already have an account? <a href="<?php echo BASE_URL; ?>?c=auth&a=login">Sign in</a>
            &nbsp;·&nbsp;
            <a href="<?php echo BASE_URL; ?>?c=auth&a=sellerRegister">Register as Seller</a>
        </div>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
