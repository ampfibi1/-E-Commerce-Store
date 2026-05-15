<?php

?>
<style>
    .sidebar {
    width: 200px;
    background-color: #f0f0f0;
    padding: 20px;
    height: 100vh;;
    position: fixed;
    overflow-y: auto; 
}
.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar ul li {
    margin: 8px 0;
}

.sidebar ul li a {
    display: block;
    padding: 5px;
    background: highlight;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

</style>

<div class="sidebar" style="width:200px; background:#f0f0f0;">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="../../controller/adminController/dashboardController.php">Dashboard</a></li>
            <li><a href="../../controller/adminController/sellersController.php">Seller Management</a></li>
            <li><a href="../../controller/adminController/categoriesController.php">Categories</a></li>
            <li><a href="../../controller/adminController/productsController.php">Products</a></li>
            <li><a href="../../controller/adminController/ordersController.php">Orders</a></li>
            <li><a href="../../controller/adminController/usersController.php">Users</a></li>
            <li><a href="../../controller/adminController/disputesController.php">Disputes</a></li>
            <li><a href="../../controller/adminController/couponsController.php">Coupons</a></li>
            <li><a href="../../controller/adminController/analyticsController.php">Analytics</a></li>
            <li><a href="../../controller/adminController/reportsController.php">Reports</a></li>
            <li><a href="../../controller/adminController/announcementsController.php">Announcements</a></li>
            <li><a href="../../controller/adminController/settingsController.php">Settings</a></li>
            
            <hr></hr>

            <li><a id="logout" href="../../controller/indexController.php">Logout</a></li>
        </ul>
</div>
