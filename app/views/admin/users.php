<?php
session_start();
$users = $_SESSION['users'] ?? [];
$search = $_SESSION['users_search'] ?? '';
$role = $_SESSION['users_role'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">

<body>

<?php include 'sidebar.php'; ?>

<div class="main_content">
    <h1>Users</h1>
    <div class="filters">
        <form action="<?php echo BASE_URL; ?>?c=admin&amp;a=users" method="POST">
            <input type="text" name="search" id="search_user"
                placeholder="Search by name or email" value="<?= $search ?>"
            >
            <select name="role">
                <option value="">All Roles</option>
                <option value="customer"
                    <?= ($role == 'customer') ? 'selected' : '' ?>>
                    Customer
                </option>
                <option value="delivery_manager"
                    <?= ($role == 'delivery_manager') ? 'selected' : '' ?>>
                    Delivery Manager
                </option>
            </select>
            <input type="submit" value="Search" class="btn"><br>
            <span id="suggestion_box"></span>
        </form>

    </div>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th>Action</th>
        </tr>

        <?php foreach ($users as $user): ?>
        <tr id="userRow<?= $user['id'] ?>">
            <td><?= $user['id'] ?></td>
            <td><?= $user['name'] ?></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['phone'] ?></td>
            <td><?= $user['role']?></td>
            <td class="status">
                <?= $user['is_active'] ? 'Yes' : 'No' ?>
            </td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <?php if ($user['is_active']): ?>
                    <a href="#"
                       class="actionLink btn deactivate"
                       data-id="<?= $user['id'] ?>"
                       data-action="deactivate">
                        Deactivate
                    </a>
                <?php else: ?>
                    <a href="#"
                       class="actionLink btn reactivate"
                       data-id="<?= $user['id'] ?>"
                       data-action="reactivate">
                        Reactivate
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<script src="<?php echo BASE_URL; ?>../app/views/admin/JS/user.js"></script>
</body>
</html>