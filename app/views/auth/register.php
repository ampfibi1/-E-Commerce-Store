<?php $page_title = 'Register'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div style="max-width:500px; margin:2.5rem auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title text-center">Create Customer Account</h2>
        </div>

        <?php if (!empty($errors) && is_array($errors) && isset($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo sanitize($errors['general']); ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="<?php echo BASE_URL; ?>?c=auth&a=register" novalidate onsubmit="return validateRegisterForm()">

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

            <div class="form-group" style="margin-top:1.25rem;">
                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </div>

        </form>

        <hr class="divider">

        <p class="text-center" style="font-size:0.92rem;">
            Already have an account? <a href="<?php echo BASE_URL; ?>?c=auth&a=login">Sign in</a>
        </p>
        <p class="text-center" style="font-size:0.92rem;">
            Want to sell? <a href="<?php echo BASE_URL; ?>?c=auth&a=seller_register">Register as Seller</a>
        </p>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
