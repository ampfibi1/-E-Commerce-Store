<?php

function login($email, $password, $conn) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, role, password_hash FROM users WHERE email = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row && password_verify($password, $row['password_hash'])) {
        return $row;
    }
    return false;
}
