<?php
session_start();

if($_SESSION['user_role'] !== 'admin'){
    header('Location: ../../controller/indexController.php');
    exit();
}


$sellers = $_SESSION['sellers'] ?? [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sellers</title>
    <link rel="stylesheet" href="CSS/allmainContent.css">
    <link rel="stylesheet" href="CSS/sellers.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main_content" >
        <h1>Seller Management</h1>
        <table>
            <thead>
                <tr>
                     <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Shop Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sellers as $seller): ?>
                    <tr id="sellerRow<?= $seller['id'] ?>">
                        <td><?php echo $seller['id']; ?></td>
                        <td><?php echo $seller['name']; ?></td>
                        <td><?php echo $seller['email']; ?></td>
                        <td><?php echo $seller['shop_name']; ?></td>
                        <td class="status"><?php echo $seller['is_approved'] ? 'Approved' : 'Pending'; ?></td>
                        <td>

                        <?php if (!$seller['is_approved']): ?>
                        
                            <a href="#"
                               class="actionLink"
                               data-action="approve"
                               data-id="<?= $seller['id'] ?>">
                               Approve
                            </a>
                        
                            <hr>
                        
                            <a href="#"
                               class="actionLink"
                               data-action="reject"
                               data-id="<?= $seller['id'] ?>">
                               Reject
                            </a>
                        
                        <?php else: ?>
                        
                            <a href="#"
                               class="actionLink"
                               data-action="suspend"
                               data-id="<?= $seller['id'] ?>">
                               Suspend
                            </a>
                        
                            <hr>
                        
                            <a href="#"
                               class="actionLink"
                               data-action="reactivate"
                               data-id="<?= $seller['id'] ?>">
                               Reactivate
                            </a>
                        
                        <?php endif; ?>
                        
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="JS/sellers.js"></script>
</body>
</html>