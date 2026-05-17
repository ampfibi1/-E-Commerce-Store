<?php

function category_get_all($conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories ORDER BY parent_id IS NOT NULL, parent_id, name");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function category_get_top_level($conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function category_get_children($conn, $parent_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE parent_id = ? ORDER BY name");
    mysqli_stmt_bind_param($stmt, "i", $parent_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function category_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}
