<?php
session_start();

$coupons = $_SESSION['coupons'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main_content" >
        <h1>Platform Coupons</h1>
        <form action="<?php echo BASE_URL; ?>?c=admin&amp;a=coupons" method="post"
            style="background: white; padding: 20px;">
            <h3>Add Coupon</h3>
            <input type="text" name="code" placeholder="Code">
            <input type="text" name="discount_pct" placeholder="Discount %">
            <input type="text" name="max_uses" placeholder="Max Uses">
            <input type="text" name="valid_until" placeholder="YYYY-MM-DD HH:MM">
            <button type="submit" name="add_coupon">Add</button>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Discount %</th>
                <th>Max Uses</th>
                <th>Uses</th>
                <th>Valid Until</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($coupons as $coupon): ?>
            <tr>
                <td><?= $coupon['id'] ?></td>
                <td><?= $coupon['code'] ?></td>
                <td><?= $coupon['discount_pct'] ?>%</td>
                <td><?= $coupon['max_uses'] ?></td>
                <td><?= $coupon['uses_count'] ?></td>
                <td><?= $coupon['valid_until'] ?></td>
                <td><?= $coupon['is_active'] ? 'Yes' : 'No' ?></td>
                <td>
                    <?php if ($coupon['is_active']): ?>
                    <form action="<?php echo BASE_URL; ?>?c=admin&amp;a=coupons" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $coupon['id'] ?>">
                        <button type="submit" name="deactivate">Deactivate</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>