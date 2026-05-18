<?php
// Sidebar is included from AdminController via the front controller, so BASE_URL is defined.
$base = defined('BASE_URL') ? BASE_URL : '../../public/';
?>
<div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="<?php echo $base; ?>?c=admin&a=dashboard">Dashboard</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=sellers">Seller Management</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=categories">Categories</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=products">Products</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=orders">Orders</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=users">Users</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=disputes">Disputes</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=coupons">Coupons</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=analytics">Analytics</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=reports">Reports</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=announcements">Announcements</a></li>
            <li><a href="<?php echo $base; ?>?c=admin&a=settings">Settings</a></li>

            <hr>

            <li><a href="<?php echo $base; ?>?c=auth&a=logout">Logout</a></li>
        </ul>
</div>
