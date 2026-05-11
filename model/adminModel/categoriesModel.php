<?php

function getAllCategories($conn) {
    $sql = "SELECT c1.id, c1.name, c1.description, c1.parent_id,
                   c2.name AS parent_name
            FROM categories c1
            LEFT JOIN categories c2 ON c1.parent_id = c2.id
            ORDER BY c1.id DESC";

    $result = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}


function getParentCategories($conn) {
    $sql = "SELECT id, name FROM categories WHERE parent_id IS NULL";
    return mysqli_query($conn, $sql)->fetch_all(MYSQLI_ASSOC);
}


function getCategoryById($id, $conn) {
    $sql = "SELECT * FROM categories WHERE id = $id";
    return mysqli_fetch_assoc(mysqli_query($conn, $sql));
}


function addCategory($name, $description, $parentId, $conn) {
    $parentId = $parentId ? $parentId : "NULL";

    $sql = "INSERT INTO categories (name, description, parent_id)
            VALUES ('$name', '$description', $parentId)";

    mysqli_query($conn, $sql);
}


function updateCategory($id, $name, $description, $parentId, $conn) {
    $parentSql = $parentId ? $parentId : "NULL";

    $sql = "UPDATE categories 
            SET name='$name', description='$description', parent_id=$parentSql
            WHERE id=$id";

    mysqli_query($conn, $sql);
}


function deleteCategory($id, $conn) {
    $sql = "SELECT COUNT(*) AS child_count 
            FROM categories 
            WHERE parent_id = $id";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row['child_count'] > 0) {
        return false; 
    }

    $deleteSql = "DELETE FROM categories WHERE id = $id";
    mysqli_query($conn, $deleteSql);

    return true;
}