<?php
function getAllDisputes($conn) {
    
    $sql="SELECT d.id,d.customer_id,d.seller_id,d.order_id,
            d.description,d.status,d.admin_note,d.created_at,
            u1.name AS customer_name,
            u2.name AS seller_name,
            s.shop_name
        FROM disputes d
        JOIN users u1 
        ON d.customer_id = u1.id
        JOIN sellers s
        ON d.seller_id = s.id
        JOIN users u2
        ON s.user_id = u2.id;";
    $result = mysqli_query($conn, $sql);
    $disputes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $disputes[] = $row;
    }
    return $disputes;
}

function resolveDispute($id, $note,$conn) {
    $sql = "UPDATE disputes SET status = 'resolved', admin_note = '$note' WHERE id = $id";
    mysqli_query($conn, $sql);
}
?>