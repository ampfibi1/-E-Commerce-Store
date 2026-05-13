<?php

function coupon_validate($conn, $code, $seller_id, $subtotal) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM coupons
         WHERE code = ? AND seller_id = ? AND is_active = 1
           AND valid_until >= CURDATE()
           AND (max_uses = 0 OR uses_count < max_uses)
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "si", $code, $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $coupon = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$coupon) {
        return [
            'valid'      => false,
            'discount'   => 0.0,
            'message'    => 'Invalid or expired coupon code.',
            'coupon_id'  => null,
        ];
    }

    $discount = (float)$subtotal * ((float)$coupon['discount_pct'] / 100);

    return [
        'valid'     => true,
        'discount'  => $discount,
        'message'   => 'Coupon applied successfully.',
        'coupon_id' => (int)$coupon['id'],
    ];
}

function coupon_get_by_seller($conn, $seller_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM coupons WHERE seller_id = ? ORDER BY id DESC"
    );
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows   = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function coupon_create($conn, $data) {
    // Check for duplicate code for this seller
    $chk = mysqli_prepare($conn,
        "SELECT id FROM coupons WHERE code = ? AND seller_id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($chk, "si", $data['code'], $data['seller_id']);
    mysqli_stmt_execute($chk);
    $chk_result = mysqli_stmt_get_result($chk);
    $exists     = mysqli_fetch_assoc($chk_result);
    mysqli_stmt_close($chk);

    if ($exists) {
        return false;
    }

    $stmt = mysqli_prepare($conn,
        "INSERT INTO coupons (seller_id, code, discount_pct, max_uses, valid_until)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isdis",
        $data['seller_id'],
        $data['code'],
        $data['discount_pct'],
        $data['max_uses'],
        $data['valid_until']
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function coupon_update($conn, $id, $data) {
    $stmt = mysqli_prepare($conn,
        "UPDATE coupons SET code = ?, discount_pct = ?, max_uses = ?, valid_until = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sdisi",
        $data['code'],
        $data['discount_pct'],
        $data['max_uses'],
        $data['valid_until'],
        $id
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function coupon_increment_use($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "UPDATE coupons SET uses_count = uses_count + 1 WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function coupon_toggle($conn, $id) {
    $upd = mysqli_prepare($conn,
        "UPDATE coupons SET is_active = NOT is_active WHERE id = ?"
    );
    mysqli_stmt_bind_param($upd, "i", $id);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    $sel = mysqli_prepare($conn,
        "SELECT is_active FROM coupons WHERE id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($sel, "i", $id);
    mysqli_stmt_execute($sel);
    $result = mysqli_stmt_get_result($sel);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($sel);

    return $row ? (int)$row['is_active'] : null;
}
