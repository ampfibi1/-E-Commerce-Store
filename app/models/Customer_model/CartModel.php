<?php

function cart_get($conn) {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return [];
    }

    $items = [];
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $product_id = (int)$product_id;
        $qty        = (int)$qty;
        if ($qty <= 0) {
            continue;
        }

        $stmt = mysqli_prepare($conn,
            "SELECT p.id AS product_id, p.name, p.price, p.primary_image_path,
                    p.stock_qty, p.is_available, p.seller_id,
                    s.shop_name
             FROM products p
             JOIN sellers s ON p.seller_id = s.id
             WHERE p.id = ?
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($row) {
            $row['qty']      = $qty;
            $row['subtotal'] = $row['price'] * $qty;
            $items[]         = $row;
        }
    }
    return $items;
}

function cart_add($product_id, $qty = 1) {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $product_id = (int)$product_id;
    $qty        = (int)$qty;
    if ($qty <= 0) {
        return;
    }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }
}

function cart_update($product_id, $qty) {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $product_id = (int)$product_id;
    $qty        = (int)$qty;
    if ($qty <= 0) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }
}

function cart_remove($product_id) {
    $product_id = (int)$product_id;
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function cart_clear() {
    $_SESSION['cart'] = [];
}

function cart_count() {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $total += (int)$qty;
    }
    return $total;
}

function cart_subtotal($conn) {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0.0;
    }
    $total = 0.0;
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $product_id = (int)$product_id;
        $qty        = (int)$qty;
        if ($qty <= 0) {
            continue;
        }
        $stmt = mysqli_prepare($conn, "SELECT price FROM products WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if ($row) {
            $total += (float)$row['price'] * $qty;
        }
    }
    return $total;
}
