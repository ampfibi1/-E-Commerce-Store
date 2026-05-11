<?php

function getSuggestions($search, $conn)
{
    $search = mysqli_real_escape_string($conn, $search);
    $sql = "SELECT name FROM products WHERE name LIKE '%$search%' LIMIT 10";
    $result = mysqli_query($conn, $sql);
    $suggestions = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $suggestions[] = $row['name'];
        }
    }

    return $suggestions;
}

function getAllProducts($search, $category, $seller, $conn)
{
    $sql = "SELECT 
            p.id,
            p.name,
            p.price,
            p.stock_qty,
            p.is_available,
            c.name AS category_name,
            s.shop_name,
            u.name AS seller_name
        FROM products p
        JOIN categories c
            ON p.category_id = c.id
        JOIN sellers s
            ON p.seller_id = s.id
        JOIN users u
            ON s.user_id = u.id
        WHERE 1=1
    ";
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND p.name LIKE '%$search%'";
    }

    if (!empty($category)) {
        $category = intval($category);
        $sql .= " AND p.category_id = $category";
    }

    if (!empty($seller)) {
        $seller = intval($seller);
        $sql .= " AND p.seller_id = $seller";
    }
    $sql .= " ORDER BY p.id DESC";
    $result = mysqli_query($conn, $sql);

    $products = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    return $products;
}

function getCategoriesForFilter($conn)
{
    $sql = "SELECT id, name FROM categories ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    $categories = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }

    return $categories;
}

function getSellersForFilter($conn)
{
    $sql = "SELECT 
                s.id,
                u.name
            FROM sellers s
            JOIN users u
                ON s.user_id = u.id
            ORDER BY u.name ASC
    ";

    $result = mysqli_query($conn, $sql);
    $sellers = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sellers[] = $row;
        }
    }

    return $sellers;
}


function removeProduct($id, $conn)
{
    $id = intval($id);
    $sql = "DELETE FROM products WHERE id = $id";
    return mysqli_query($conn, $sql);
}

?>