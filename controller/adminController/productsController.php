<?php
session_start();

require_once '../../model/connection.php';
require_once '../../model/adminModel/productsModel.php';

$conn = conn_open();

if (isset($_GET['action']) && $_GET['action'] == 'remove') {

    $id = intval($_GET['id']);
    removeProduct($id, $conn);

    header("Location: ../../controller/adminController/productsController.php");
    exit();
}

$search = $_POST['search'] ?? '';
$category = $_POST['category'] ?? '';
$seller = $_POST['seller'] ?? '';


$products = getAllProducts($search, $category, $seller, $conn);
$categories = getCategoriesForFilter($conn);
$sellers = getSellersForFilter($conn);

conn_close($conn);

$_SESSION['products'] = $products;
$_SESSION['categories_filter'] = $categories;
$_SESSION['sellers_filter'] = $sellers;
$_SESSION['search'] = $search;
$_SESSION['category'] = $category;
$_SESSION['seller'] = $seller;

header("Location: ../../views/admin/products.php");
exit();
?>