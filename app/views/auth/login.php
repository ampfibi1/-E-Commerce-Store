<?php $page_title = 'Login'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div style="max-width:440px; margin:2.5rem auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title text-center">Sign In to ShopHub</h2>
        </div>

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

        <hr class="divider">

        <p class="text-center" style="font-size:0.92rem;">
            Don't have an account?
            <a href="<?php echo BASE_URL; ?>?c=auth&a=register">Register as Customer</a>
            &nbsp;|&nbsp;
            <a href="<?php echo BASE_URL; ?>?c=auth&a=seller_register">Register as Seller</a>
        </p>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
