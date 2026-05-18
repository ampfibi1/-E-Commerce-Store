<?php

function getAllUsers($search, $role, $conn)
{
    $sql = "SELECT 
            id,
            name,
            email,
            phone,
            role,
            is_active,
            created_at
        FROM users
        WHERE role IN ('customer', 'delivery_manager')
    ";

    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
    }

    if (!empty($role)) {
        $role = mysqli_real_escape_string($conn, $role);
        $sql .= " AND role = '$role'";
    }
    $result = mysqli_query($conn, $sql);
    $users = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    return $users;
}


function deactivateUser($id, $conn)
{
    $id = intval($id);
    mysqli_query($conn, "UPDATE users SET is_active = 0 WHERE id = $id");
}

function reactivateUser($id, $conn)
{
    $id = intval($id);
    mysqli_query($conn, "UPDATE users SET is_active = 1 WHERE id = $id");
}

function getUserSuggestions($search, $conn)
{
    $search = mysqli_real_escape_string($conn, $search);
    $sql = "SELECT name 
        FROM users 
        WHERE role IN ('customer', 'delivery_manager') 
        AND (name LIKE '%$search%' OR email LIKE '%$search%')
        LIMIT 5
    ";
    $result = mysqli_query($conn, $sql);

    $suggestions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = $row['name'];
    }
    
    return $suggestions;
}
?>