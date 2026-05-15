<?php
session_start();

if($_SESSION['user_role'] !== 'admin'){
    header('Location: ../../controller/indexController.php');
    exit();
}

$orders = $_SESSION['orders'] ?? [];
$sellers = $_SESSION['sellers_order'] ?? [];
$customers = $_SESSION['customers_order'] ?? [];

$status = $_SESSION['status'] ?? '';
$date_from = $_SESSION['date_from'] ?? '';
$date_to = $_SESSION['date_to'] ?? '';
$seller = $_SESSION['seller'] ?? '';
$customer = $_SESSION['customer'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="CSS/allmainContent.css">
    <link rel="stylesheet" href="CSS/filter.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main_content">

    <h1>Orders</h1>

    <div class="filters">
        <form action="../../controller/adminController/ordersController.php" method="POST">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending"
                    <?= ($status == 'pending') ? 'selected' : '' ?>>
                    Pending
                </option>
                <option value="confirmed"
                    <?= ($status == 'confirmed') ? 'selected' : '' ?>>
                    Confirmed
                </option>
                <option value="processing"
                    <?= ($status == 'processing') ? 'selected' : '' ?>>
                    Processing
                </option>
                <option value="shipped"
                    <?= ($status == 'shipped') ? 'selected' : '' ?>>
                    Shipped
                </option>
                <option value="delivered"
                    <?= ($status == 'delivered') ? 'selected' : '' ?>>
                    Delivered
                </option>
                <option value="cancelled"
                    <?= ($status == 'cancelled') ? 'selected' : '' ?>>
                    Cancelled
                </option>

            </select>

            <input type="date" name="date_from" value="<?= $date_from ?>">
            <input type="date" name="date_to" value="<?= $date_to ?>">

            <select name="seller">
                <option value="">All Sellers</option>
                <?php foreach ($sellers as $sel): ?>
                    <option
                        value="<?= $sel['id'] ?>"
                        <?= ($seller == $sel['id']) ? 'selected' : '' ?>
                    >
                        <?= $sel['name']?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="customer">
                <option value="">All Customers</option>
                <?php foreach ($customers as $cust): ?>
                    <option
                        value="<?= $cust['id'] ?>"
                        <?= ($customer == $cust['id']) ? 'selected' : '' ?>
                    >
                        <?= $cust['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" value="Filter" class="btn">
        </form>

    </div>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Seller Shop</th>
        </tr>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td>
                        <?= $order['customer_name'] ?>
                    </td>
                    <td>
                        $<?= $order['total_amount'] ?>
                    </td>

                    <td> <?= $order['status'] ?></td>
                    <td><?= $order['created_at'] ?></td>
                    <td><?= $order['shop_name'] ?? 'Multiple' ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No Orders Found</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>