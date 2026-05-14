<?php $flash = get_flash(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitize($page_title) . ' — ShopHub' : 'ShopHub'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="logo">ShopHub</a>
        <ul class="nav-links">
            <?php if (!isset($_SESSION['uid'])): ?>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="nav-link">Browse</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=login" class="nav-link">Login</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=register" class="nav-link">Register</a></li>
            <?php elseif ($_SESSION['role'] === 'customer'): ?>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="nav-link">Browse</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=orders" class="nav-link">My Orders</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=wishlist" class="nav-link">Wishlist</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=disputes" class="nav-link">Disputes</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=profile" class="nav-link">Profile</a></li>
                <li>
                    <a href="<?php echo BASE_URL; ?>?c=customer&a=cart" class="nav-link cart-link">
                        Cart
                        <?php
                        $cart_qty = 0;
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $pid => $qty) {
                                $cart_qty += (int)$qty;
                            }
                        }
                        if ($cart_qty > 0): ?>
                        <span class="cart-count"><?php echo $cart_qty; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=logout" class="nav-link">Logout (<?php echo sanitize($_SESSION['uname']); ?>)</a></li>
            <?php elseif ($_SESSION['role'] === 'seller'): ?>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=dashboard" class="nav-link">Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=products" class="nav-link">Products</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=orders" class="nav-link">Orders</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=analytics" class="nav-link">Analytics</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=coupons" class="nav-link">Coupons</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=disputes" class="nav-link">Disputes</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=logout" class="nav-link">Logout (<?php echo sanitize($_SESSION['uname']); ?>)</a></li>
            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                <li><a href="<?php echo BASE_URL; ?>?c=admin&a=dashboard" class="nav-link">Admin Panel</a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=logout" class="nav-link">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<?php if ($flash): ?>
<div class="flash-<?php echo sanitize($flash['type']); ?>"><?php echo sanitize($flash['msg']); ?></div>
<?php endif; ?>
<div class="container main-wrap">
