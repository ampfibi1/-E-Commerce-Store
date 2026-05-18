<?php
function getAllSellers($conn) {
    $sql = "SELECT s.id, s.user_id, s.shop_name, s.is_approved, u.name, u.email FROM sellers s JOIN users u ON s.user_id = u.id";
    $result = mysqli_query($conn, $sql);
    $sellers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sellers[] = $row;
    }
    return $sellers;
}

function approveSeller($sellerId,$conn) {
    $sql = "UPDATE sellers SET is_approved = 1 WHERE id = $sellerId";
    mysqli_query($conn, $sql);
}

function rejectSeller($sellerId,$conn) {
    $sql = "DELETE FROM sellers WHERE id = $sellerId";
    mysqli_query($conn, $sql);
}

function suspendSeller($sellerId,$conn) {
    $sql = "UPDATE sellers SET is_approved = 0 WHERE id = $sellerId";
    mysqli_query($conn, $sql);
}

function reactivateSeller($sellerId,$conn) {
    $sql = "UPDATE sellers SET is_approved = 1 WHERE id = $sellerId";
    mysqli_query($conn, $sql);
}
?>