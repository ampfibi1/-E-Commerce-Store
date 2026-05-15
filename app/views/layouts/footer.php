</div><!-- /.main-wrap -->
<footer>
    <div class="container">
        <div class="foot-grid">
            <div class="foot-brand">
                <h3><?php icon_shop(20); ?> ShopHub</h3>
                <p>A multi-vendor marketplace built for buyers and sellers across Bangladesh.
                Discover, shop, sell — all in one place.</p>
            </div>
            <div class="foot-col">
                <h4>Shop</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>?c=customer&a=products">Browse all</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=customer&a=products">Categories</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=auth&a=login">My orders</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=auth&a=login">Wishlist</a></li>
                </ul>
            </div>
            <div class="foot-col">
                <h4>Sell</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>?c=auth&a=sellerRegister">Become a seller</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=auth&a=login">Seller dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=auth&a=login">Analytics</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=auth&a=login">Coupons</a></li>
                </ul>
            </div>
            <div class="foot-col">
                <h4>Support</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>?c=info&a=disputes">Open a dispute</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=info&a=help">Help center</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=info&a=contact">Contact us</a></li>
                    <li><a href="<?php echo BASE_URL; ?>?c=info&a=terms">Terms &amp; privacy</a></li>
                </ul>
            </div>
        </div>
        <div class="foot-bottom">
            &copy; <?php echo date('Y'); ?> ShopHub. All rights reserved.
        </div>
    </div>
</footer>
<script src="<?php echo BASE_URL; ?>js/validation.js"></script>
<script src="<?php echo BASE_URL; ?>js/interactions.js"></script>
</body>
</html>
