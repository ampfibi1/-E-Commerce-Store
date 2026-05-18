<?php
function getPlatformCoupons($conn) {
    $sql = "SELECT id, code, discount_pct, max_uses, uses_count, valid_until, is_active FROM coupons WHERE seller_id IS NULL OR seller_id = 0";
    $result = mysqli_query($conn, $sql);
    $coupons = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $coupons[] = $row;
    }
    return $coupons;
}

function addPlatformCoupon($code, $discountPct, $maxUses, $validUntil, $conn) {
    $sql = "INSERT INTO coupons (seller_id, code, discount_pct, max_uses, valid_until, is_active) VALUES (0, '$code', $discountPct, $maxUses, '$validUntil', 1)";
    mysqli_query($conn, $sql);
}

function deactivateCoupon($id,$conn) {
    $sql = "UPDATE coupons SET is_active = 0 WHERE id = $id";
    mysqli_query($conn, $sql);
}
?>