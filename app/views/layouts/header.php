<?php
$flash = get_flash();
require_once APP . '/views/layouts/icons.php';
?>
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
    <div class="container nav-inner">
        <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="logo">
            <span class="logo-icon"><?php icon_shop(24); ?></span>
            ShopHub
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Open menu"><?php icon_menu(); ?></button>
        <ul class="nav-links" id="navLinks">
            <?php if (!isset($_SESSION['uid'])): ?>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="nav-link"><?php icon_search(); ?><span>Browse</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=login" class="nav-link"><?php icon_user(); ?><span>Login</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=register" class="nav-link nav-link-cta"><span>Register</span></a></li>
            <?php elseif ($_SESSION['role'] === 'customer'): ?>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="nav-link"><?php icon_search(); ?><span>Browse</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=orders" class="nav-link"><?php icon_box(); ?><span>Orders</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=wishlist" class="nav-link"><?php icon_heart(); ?><span>Wishlist</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=disputes" class="nav-link"><?php icon_alert(); ?><span>Disputes</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=customer&a=profile" class="nav-link"><?php icon_user(); ?><span>Profile</span></a></li>
                <li>
                    <a href="<?php echo BASE_URL; ?>?c=customer&a=cart" class="nav-link cart-link">
                        <?php icon_cart(); ?><span>Cart</span>
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
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=logout" class="nav-link nav-link-muted"><?php icon_logout(); ?><span>Logout</span></a></li>
            <?php elseif ($_SESSION['role'] === 'seller'): ?>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=dashboard" class="nav-link"><?php icon_dashboard(); ?><span>Dashboard</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=products" class="nav-link"><?php icon_box(); ?><span>Products</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=orders" class="nav-link"><?php icon_truck(); ?><span>Orders</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=analytics" class="nav-link"><?php icon_chart(); ?><span>Analytics</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=coupons" class="nav-link"><?php icon_tag(); ?><span>Coupons</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=seller&a=disputes" class="nav-link"><?php icon_alert(); ?><span>Disputes</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=logout" class="nav-link nav-link-muted"><?php icon_logout(); ?><span>Logout</span></a></li>
            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                <li><a href="<?php echo BASE_URL; ?>?c=admin&a=dashboard" class="nav-link"><?php icon_shield(); ?><span>Admin</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>?c=auth&a=logout" class="nav-link nav-link-muted"><?php icon_logout(); ?><span>Logout</span></a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<?php if ($flash): ?>
<div class="toast toast-<?php echo sanitize($flash['type']); ?>" id="serverToast" data-msg="<?php echo sanitize($flash['msg']); ?>" data-type="<?php echo sanitize($flash['type']); ?>">
    <?php echo sanitize($flash['msg']); ?>
</div>
<?php endif; ?>

<div class="container main-wrap">
