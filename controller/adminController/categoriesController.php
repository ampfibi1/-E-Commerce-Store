<?php
session_start();

require_once '../../model/adminModel/categoriesModel.php';
require_once '../../model/connection.php';

$conn = conn_open();

$_SESSION['error'] = "";
if (isset($_GET['action']) && $_GET['action'] === "delete") {
    deleteCategory($_GET['id'], $conn);
}
if (isset($_GET['action']) && $_GET['action'] === "edit") {
    $id = intval($_GET['id']);
    $_SESSION['editCat'] = getCategoryById($id, $conn);
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $parentId = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    if ($name === "" || $description === "") {
        $_SESSION['error'] = "All fields are required.";
    } else {
        if (isset($_POST['add_category'])) {
            addCategory($name, $description, $parentId, $conn);
        }

        if (isset($_POST['edit_category'])) {
            updateCategory($_POST['id'], $name, $description, $parentId, $conn);
        }
    }
}
$categories = getAllCategories($conn);
$parents = getParentCategories($conn);

conn_close($conn);

$_SESSION['categories'] = $categories;
$_SESSION['parents'] = $parents;

header("Location: ../../views/admin/categories.php");
exit();