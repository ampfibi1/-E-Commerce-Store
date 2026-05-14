<?php $page_title = '404 — Page Not Found'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div style="max-width:520px; margin:4rem auto; text-align:center;">
    <div class="card" style="padding:3rem 2rem;">
        <div style="font-size:5rem; font-weight:900; color:#e0e8f5; line-height:1; margin-bottom:0.5rem;">404</div>
        <h2 style="color:#333; margin-bottom:0.5rem;">Page Not Found</h2>
        <p class="text-muted" style="margin-bottom:1.5rem;">
            Sorry, the page you are looking for does not exist or has been moved.
        </p>
        <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
            <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-primary">
                Browse Products
            </a>
            <?php if (isset($_SESSION['uid']) && $_SESSION['role'] === 'customer'): ?>
            <a href="<?php echo BASE_URL; ?>?c=customer&a=dashboard" class="btn btn-secondary">
                My Dashboard
            </a>
            <?php elseif (isset($_SESSION['uid']) && $_SESSION['role'] === 'seller'): ?>
            <a href="<?php echo BASE_URL; ?>?c=seller&a=dashboard" class="btn btn-secondary">
                Seller Dashboard
            </a>
            <?php else: ?>
            <a href="<?php echo BASE_URL; ?>?c=auth&a=login" class="btn btn-secondary">
                Login
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
