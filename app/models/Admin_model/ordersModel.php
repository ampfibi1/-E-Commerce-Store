<?php

function getAllOrders($status,$dateFrom, $dateTo,$seller,$customer,$conn)
{
    $query = "SELECT
                o.id,
                o.total_amount,
                o.status,
                o.created_at,
                u.name AS customer_name,
                GROUP_CONCAT(DISTINCT s.shop_name SEPARATOR ', ')
                AS shop_name
            FROM orders o
            JOIN users u
                ON o.customer_id = u.id
            LEFT JOIN order_items oi
                ON o.id = oi.order_id
            LEFT JOIN sellers s
                ON oi.seller_id = s.id
            WHERE 1=1";

    if (!empty($status)) {
        $status = mysqli_real_escape_string($conn, $status);
        $query .= " AND o.status = '$status'";
    }
    if (!empty($dateFrom)) {
        $dateFrom = mysqli_real_escape_string($conn, $dateFrom);
        $query .= " AND DATE(o.created_at) >= '$dateFrom'";
    }

    if (!empty($dateTo)) {
        $dateTo = mysqli_real_escape_string($conn, $dateTo);
        $query .= " AND DATE(o.created_at) <= '$dateTo'";
    }

    if (!empty($seller)) {
        $seller = intval($seller);
        $query .= " AND oi.seller_id = $seller";
    }
    if (!empty($customer)) {
        $customer = intval($customer);
        $query .= " AND o.customer_id = $customer";
    }

    $query .= "
    GROUP BY o.id 
    ORDER BY o.id DESC";
    $result = mysqli_query($conn, $query);
    $orders = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    return $orders;
}

function getSellersForOrderFilter($conn)
{
    $sql = " SELECT DISTINCT
                s.id,
                u.name
            FROM sellers s
            JOIN users u
                ON s.user_id = u.id
            JOIN order_items oi
                ON s.id = oi.seller_id
            ORDER BY u.name ASC";

    $result = mysqli_query($conn, $sql);
    $sellers = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sellers[] = $row;
        }
    }
    return $sellers;
}

function getCustomersForOrderFilter($conn)
{
    $sql = "SELECT DISTINCT
                u.id,
                u.name
            FROM users u
            JOIN orders o
                ON u.id = o.customer_id
            ORDER BY u.name ASC";
    $result = mysqli_query($conn, $sql);
    $customers = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
    }
    return $customers;
}
?>