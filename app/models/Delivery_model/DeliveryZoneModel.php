<?php
// ============================================================
// app/models/DeliveryZoneModel.php
// Procedural model functions for delivery_zones table
// ============================================================

function delivery_zone_get_all($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, zone_name, delivery_fee, estimated_days, created_at
         FROM delivery_zones
         ORDER BY zone_name ASC");
    mysqli_stmt_execute($stmt);
    $res   = mysqli_stmt_get_result($stmt);
    $zones = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $zones[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $zones;
}

function delivery_zone_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, zone_name, delivery_fee, estimated_days
         FROM delivery_zones
         WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $zone = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $zone;
}

function delivery_zone_insert($conn, $zone_name, $delivery_fee, $estimated_days) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO delivery_zones (zone_name, delivery_fee, estimated_days)
         VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sdi', $zone_name, $delivery_fee, $estimated_days);
    mysqli_stmt_execute($stmt);
    $new_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $new_id;
}

function delivery_zone_update($conn, $id, $zone_name, $delivery_fee, $estimated_days) {
    $stmt = mysqli_prepare($conn,
        "UPDATE delivery_zones
         SET zone_name = ?, delivery_fee = ?, estimated_days = ?
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sdii', $zone_name, $delivery_fee, $estimated_days, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function delivery_zone_delete($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "DELETE FROM delivery_zones WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ----------------------------------------------------------
// Aliases for legacy CustomerController calls
// ----------------------------------------------------------
function zone_get_all($conn)      { return delivery_zone_get_all($conn); }
function zone_get_by_id($conn,$id){ return delivery_zone_get_by_id($conn,$id); }
