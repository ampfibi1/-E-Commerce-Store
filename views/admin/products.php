<?php
session_start();

if($_SESSION['user_role'] !== 'admin'){
    header('Location: ../../controller/indexController.php');
    exit();
}


$products = $_SESSION['products'] ?? [];
$categories = $_SESSION['categories_filter'] ?? [];
$sellers = $_SESSION['sellers_filter'] ?? [];

$search = $_SESSION['search'] ?? '';
$category = $_SESSION['category'] ?? '';
$seller = $_SESSION['seller'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="CSS/allmainContent.css">
    <link rel="stylesheet" href="CSS/filter.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main_content">
    <h1>Products</h1>

    <div class="filters">
        <form action="../../controller/adminController/productsController.php" method="POST">
            <input type="text" id="search" name="search" placeholder="Search product name" value="<?= $search ?>">
            <select name="category">
                <option value="">All Categories</option>

                <?php foreach ($categories as $cat): ?>
                    <option 
                        value="<?= $cat['id'] ?>"
                        <?= ($category == $cat['id']) ? 'selected' : '' ?>
                    >
                        <?php echo $cat['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="seller">
                <option value="">All Sellers</option>

                <?php foreach ($sellers as $sel): ?>
                    <option 
                        value="<?= $sel['id'] ?>"
                        <?= ($seller == $sel['id']) ? 'selected' : '' ?>
                    >
                        <?= $sel['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" name="filter" value="Filter" class="btn">
            <br>
            <span id="suggestion_box"></span>
        </form>
    </div>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Available</th>
            <th>Category</th>
            <th>Seller</th>
            <th>Shop</th>
            <th>Action</th>
        </tr>
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $prod): ?>
                <tr>
                    <td><?= $prod['id'] ?></td>
                    <td><?= $prod['name']?></td>
                    <td>$<?= number_format($prod['price'], 2) ?></td>
                    <td><?= $prod['stock_qty'] ?></td>
                    <td>
                        <?= ($prod['is_available']) ? 'Yes' : 'No' ?>
                    </td>
                    <td><?= htmlspecialchars($prod['category_name']) ?></td>
                    <td><?= htmlspecialchars($prod['seller_name']) ?></td>
                    <td><?= htmlspecialchars($prod['shop_name']) ?></td>
                    <td>
                        <a 
                            href="../../controller/adminController/productsController.php?action=remove&id=<?= $prod['id'] ?>"
                        >
                            Remove
                        </a>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>
            <tr>
                <td colspan="9">No products found.</td>
            </tr>

        <?php endif; ?>

    </table>
</div>
<script src="JS/searchSuggetion.js"></script>
</body>
</html>