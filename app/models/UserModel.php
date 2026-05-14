<?php

function user_get_by_email($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function user_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function user_create($conn, $data) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssss",
        $data['name'],
        $data['email'],
        $data['password_hash'],
        $data['phone'],
        $data['role']
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function user_update($conn, $id, $data) {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sssi",
        $data['name'],
        $data['email'],
        $data['phone'],
        $id
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function user_update_password($conn, $id, $hash) {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET password_hash = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "si", $hash, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function user_update_pic($conn, $id, $path) {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET profile_pic = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "si", $path, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
