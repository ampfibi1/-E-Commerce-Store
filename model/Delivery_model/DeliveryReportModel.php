<?php
// ============================================================
// app/models/DeliveryReportModel.php
// Report queries: agent performance, zone, daily, weekly
// ============================================================

// ----------------------------------------------------------
// Agent performance: total assigned, delivered, failed
// ----------------------------------------------------------
function delivery_report_agent_performance($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT ag.name AS agent_name,
                ag.vehicle_type,
                COUNT(da.id)                                          AS total_assigned,
                SUM(da.status = 'delivered')                          AS total_delivered,
                SUM(da.status = 'failed')                             AS total_failed,
                SUM(da.status IN ('assigned','picked_up','in_transit')) AS total_active
         FROM delivery_agents ag
         LEFT JOIN delivery_assignments da ON da.agent_id = ag.id
         GROUP BY ag.id, ag.name, ag.vehicle_type
         ORDER BY total_delivered DESC");
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// ----------------------------------------------------------
// Zone performance: deliveries per zone, revenue
// ----------------------------------------------------------
function delivery_report_zone_performance($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT dz.zone_name,
                dz.delivery_fee,
                dz.estimated_days,
                COUNT(da.id)                         AS total_deliveries,
                SUM(da.status = 'delivered')         AS delivered_count,
                SUM(da.status = 'failed')            AS failed_count
         FROM delivery_zones dz
         LEFT JOIN delivery_assignments da ON da.zone_id = dz.id
         GROUP BY dz.id, dz.zone_name, dz.delivery_fee, dz.estimated_days
         ORDER BY total_deliveries DESC");
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// ----------------------------------------------------------
// Daily delivery summary (today)
// ----------------------------------------------------------
function delivery_report_daily_summary($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT
            DATE(da.updated_at)                       AS delivery_date,
            COUNT(da.id)                              AS total,
            SUM(da.status = 'delivered')              AS delivered,
            SUM(da.status = 'failed')                 AS failed,
            SUM(da.status IN ('assigned','picked_up','in_transit')) AS pending
         FROM delivery_assignments da
         WHERE DATE(da.updated_at) = CURDATE()
         GROUP BY DATE(da.updated_at)");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? $row : array(
        'delivery_date' => date('Y-m-d'),
        'total'         => 0,
        'delivered'     => 0,
        'failed'        => 0,
        'pending'       => 0,
    );
}

// ----------------------------------------------------------
// Weekly delivery summary (last 7 days, one row per day)
// ----------------------------------------------------------
function delivery_report_weekly_summary($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT
            DATE(da.updated_at)                               AS delivery_date,
            COUNT(da.id)                                      AS total,
            SUM(da.status = 'delivered')                      AS delivered,
            SUM(da.status = 'failed')                         AS failed
         FROM delivery_assignments da
         WHERE da.updated_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
         GROUP BY DATE(da.updated_at)
         ORDER BY delivery_date DESC");
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $rows = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}
