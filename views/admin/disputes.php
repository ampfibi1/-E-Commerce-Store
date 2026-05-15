<?php
session_start();

if($_SESSION['user_role'] !== 'admin'){
    header('Location: ../../controller/indexController.php');
    exit();
}


$disputes = $_SESSION['disputes'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disputes</title>
    <link rel="stylesheet" href="CSS/allmainContent.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main_content" >
        <h1>Disputes</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Seller</th>
                <th>Order ID</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($disputes as $dispute): ?>
            <tr>
                <td><?= $dispute['id'] ?></td>
                <td><?= $dispute['customer_name'] ?></td>
                <td><?= $dispute['seller_name']?></td>
                <td><?= $dispute['order_id'] ?></td>
                <td><?= $dispute['description'] ?></td>
                <td><?= $dispute['status'] ?></td>
                <td><?= $dispute['created_at'] ?></td>
                <td>
                    <?php if ($dispute['status'] == 'open'): ?>
                    <form action="../controller/adminController/disputesController.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $dispute['id'] ?>">
                        <textarea name="note" placeholder="Resolution note"></textarea>
                        <button type="submit" name="resolve_dispute">Resolve</button>
                    </form>
                    <?php else: ?>
                    <?= $dispute['admin_note'] ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>    
    </div>
</body>
</html>