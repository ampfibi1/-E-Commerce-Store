<?php

function address_get_by_customer($conn, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM customer_addresses
         WHERE customer_id = ?
         ORDER BY is_default DESC, id ASC"
    );
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function address_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM customer_addresses WHERE id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function address_create($conn, $data) {
    // Check if this is the first address for the customer
    $cnt_stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM customer_addresses WHERE customer_id = ?"
    );
    mysqli_stmt_bind_param($cnt_stmt, "i", $data['customer_id']);
    mysqli_stmt_execute($cnt_stmt);
    $cnt_result = mysqli_stmt_get_result($cnt_stmt);
    $cnt_row    = mysqli_fetch_assoc($cnt_result);
    mysqli_stmt_close($cnt_stmt);

    $is_default = ($cnt_row && (int)$cnt_row['cnt'] === 0) ? 1 : 0;

    $stmt = mysqli_prepare($conn,
        "INSERT INTO customer_addresses (customer_id, label, address_line, city, zip, is_default)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "issssi",
        $data['customer_id'],
        $data['label'],
        $data['address_line'],
        $data['city'],
        $data['zip'],
        $is_default
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function address_update($conn, $id, $customer_id, $data) {
    $stmt = mysqli_prepare($conn,
        "UPDATE customer_addresses
         SET label = ?, address_line = ?, city = ?, zip = ?
         WHERE id = ? AND customer_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssssii",
        $data['label'],
        $data['address_line'],
        $data['city'],
        $data['zip'],
        $id,
        $customer_id
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function address_delete($conn, $id, $customer_id) {
    $stmt = mysqli_prepare($conn,
        "DELETE FROM customer_addresses WHERE id = ? AND customer_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $id, $customer_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function address_set_default($conn, $customer_id, $id) {
    $clr = mysqli_prepare($conn,
        "UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?"
    );
    mysqli_stmt_bind_param($clr, "i", $customer_id);
    mysqli_stmt_execute($clr);
    mysqli_stmt_close($clr);

    $set = mysqli_prepare($conn,
        "UPDATE customer_addresses SET is_default = 1 WHERE id = ? AND customer_id = ?"
    );
    mysqli_stmt_bind_param($set, "ii", $id, $customer_id);
    $ok = mysqli_stmt_execute($set);
    mysqli_stmt_close($set);
    return $ok;
}
