<?php
session_start();

$sellers = $_SESSION['commission_sellers'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../app/views/admin/CSS/allmainContent.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../app/views/admin/CSS/settings.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main_content" >
        <h1>Settings</h1>
        <h2>Seller Commission Rates</h2>
        <table>
            <tr>
                <th>Seller</th>
                <th>Current Rate (%)</th>
                <th>Update</th>
            </tr>
            <?php foreach ($sellers as $seller): ?>
            <tr>
                <td><?= htmlspecialchars($seller['name']) ?></td>
                <td><?= $seller['commission_rate'] ?>%</td>
                <td>
                    <form action="<?php echo BASE_URL; ?>?c=admin&amp;a=settings" method="post">
                        <input type="hidden" name="seller_id" value="<?= $seller['id'] ?>">
                        <input type="number" step="0.01" name="commission_rate" value="<?= $seller['commission_rate'] ?>" required>
                        <button type="submit" name="update_commission">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>