<?php
function getSellersForCommission($conn) {
    $sql = "SELECT s.id, u.name, s.commission_rate FROM sellers s JOIN users u ON s.user_id = u.id";
    $result = mysqli_query($conn, $sql);
    $sellers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sellers[] = $row;
    }
    return $sellers;
}

function updateCommission($sellerId,$rate,$conn) {
    $sql = "UPDATE sellers SET commission_rate = $rate WHERE id = $sellerId";
    mysqli_query($conn, $sql);
}
?>