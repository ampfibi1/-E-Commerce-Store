<?php

function seller_get_by_user_id($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM sellers WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function seller_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM sellers WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function seller_create($conn, $data) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO sellers (user_id, shop_name, shop_description, shop_logo_path, address)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "issss",
        $data['user_id'],
        $data['shop_name'],
        $data['shop_description'],
        $data['shop_logo_path'],
        $data['address']
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function seller_update($conn, $seller_id, $data) {
    $stmt = mysqli_prepare($conn,
        "UPDATE sellers SET shop_name = ?, shop_description = ?, address = ?, shop_logo_path = ?
         WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssssi",
        $data['shop_name'],
        $data['shop_description'],
        $data['address'],
        $data['shop_logo_path'],
        $seller_id
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
