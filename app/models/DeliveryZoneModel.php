<?php

function zone_get_all($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM delivery_zones ORDER BY zone_name ASC"
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function zone_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM delivery_zones WHERE id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}
