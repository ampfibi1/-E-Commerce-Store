<?php
session_start();

$categories = $_SESSION['categories'] ?? [];
$parents = $_SESSION['parents'] ?? [];
$error = $_SESSION['error'] ?? "";
$editCat = $_SESSION['editCat'] ?? null;

// clear flash messages
unset($_SESSION['error'], $_SESSION['editCat']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>

    <link rel="stylesheet" href="CSS/allmainContent.css">
    <link rel="stylesheet" href="CSS/categories.css">
</head>

<body>
<?php include 'sidebar.php'; ?>

<div class="main_content">
    <h1>Categories</h1>

    <!-- ADD / EDIT FORM -->
    <form action="../../controller/adminController/categoriesController.php" method="post" id="catForm">
        <h3><?= $editCat ? "Edit Category" : "Add Category" ?></h3>

        <?php if ($editCat): ?>
            <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
        <?php endif; ?>

        <input type="text" id="name" name="name" placeholder="Name"
               value="<?= $editCat['name'] ?? '' ?>">

        <textarea id="description" name="description" placeholder="Description"><?= $editCat['description'] ?? '' ?></textarea>

        <select name="parent_id">
            <option value="">Parent Category</option>
            <?php foreach ($parents as $parent): ?>
                <option value="<?= $parent['id'] ?>"
                    <?= isset($editCat) && $editCat['parent_id'] == $parent['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($parent['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <span class="error" style="color: red;"><?= $error ?></span>

        <?php if ($editCat): ?>
            <input type="submit" name="edit_category" value="Update">
            <a href="categories.php">Cancel</a>
        <?php else: ?>
            <input type="submit" name="add_category" value="Add">
        <?php endif; ?>
    </form>

    <h3>Existing Categories</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Parent</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= $cat['id'] ?></td>
            <td><?php echo $cat['name']; ?></td>
            <td><?php echo $cat['description']; ?></td>
            <td><?= $cat['parent_name'] ?: 'Root' ?></td>
            <td>
                <a href="../../controller/adminController/categoriesController.php?action=edit&id=<?= $cat['id'] ?>"
                   style="background:green;color:white;padding:5px;">Edit</a>

                <a href="../../controller/adminController/categoriesController.php?action=delete&id=<?= $cat['id'] ?>"
                   style="background:red;color:white;padding:5px;">
                   Delete
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>