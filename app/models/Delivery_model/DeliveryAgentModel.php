<?php
// ============================================================
// app/models/DeliveryAgentModel.php
// Procedural model functions for delivery_agents table
// Follows project convention: mysqli procedural only
// ============================================================

// ----------------------------------------------------------
// Get all agents
// ----------------------------------------------------------
function delivery_agent_get_all($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, phone, vehicle_type, status, created_at
         FROM delivery_agents
         ORDER BY created_at DESC");
    mysqli_stmt_execute($stmt);
    $res    = mysqli_stmt_get_result($stmt);
    $agents = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $agents[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $agents;
}

// ----------------------------------------------------------
// Get active agents only (for assignment dropdowns)
// ----------------------------------------------------------
function delivery_agent_get_active($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, phone, vehicle_type
         FROM delivery_agents
         WHERE status = 'active'
         ORDER BY name ASC");
    mysqli_stmt_execute($stmt);
    $res    = mysqli_stmt_get_result($stmt);
    $agents = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $agents[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $agents;
}

// ----------------------------------------------------------
// Get single agent by ID
// ----------------------------------------------------------
function delivery_agent_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, phone, vehicle_type, status, created_at
         FROM delivery_agents
         WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res   = mysqli_stmt_get_result($stmt);
    $agent = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $agent;
}

// ----------------------------------------------------------
// Insert new agent
// ----------------------------------------------------------
function delivery_agent_insert($conn, $name, $phone, $vehicle_type) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO delivery_agents (name, phone, vehicle_type, status)
         VALUES (?, ?, ?, 'active')");
    mysqli_stmt_bind_param($stmt, 'sss', $name, $phone, $vehicle_type);
    mysqli_stmt_execute($stmt);
    $new_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $new_id;
}

// ----------------------------------------------------------
// Update agent
// ----------------------------------------------------------
function delivery_agent_update($conn, $id, $name, $phone, $vehicle_type, $status) {
    $stmt = mysqli_prepare($conn,
        "UPDATE delivery_agents
         SET name = ?, phone = ?, vehicle_type = ?, status = ?
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $phone, $vehicle_type, $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ----------------------------------------------------------
// Set agent status (activate / deactivate)
// ----------------------------------------------------------
function delivery_agent_set_status($conn, $id, $status) {
    $stmt = mysqli_prepare($conn,
        "UPDATE delivery_agents SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
