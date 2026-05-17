<?php
$page_title = 'Analytics';
include APP . '/views/layouts/header.php';
?>

<div class="seller-analytics">
    <div class="page-header">
        <h1>Analytics</h1>
        <!-- Period Filter -->
        <form action="?c=seller&a=analytics" method="GET" style="display:inline;">
            <input type="hidden" name="c" value="seller">
            <input type="hidden" name="a" value="analytics">
            <select name="period" class="form-control form-control-inline"
                    onchange="this.form.submit()">
                <option value="day"   <?php echo (isset($_GET['period']) && $_GET['period'] === 'day')   ? 'selected' : ''; ?>>Today</option>
                <option value="week"  <?php echo (!isset($_GET['period']) || $_GET['period'] === 'week')  ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="month" <?php echo (isset($_GET['period']) && $_GET['period'] === 'month') ? 'selected' : ''; ?>>Last 30 Days</option>
            </select>
        </form>
    </div>

    <!-- Earnings Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h3>&#2547; <?php echo number_format($earnings['gross'], 2); ?></h3>
                <p>Gross Revenue</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3>&#2547; <?php echo number_format($earnings['commission'], 2); ?></h3>
                <p>Commission (<?php echo number_format($earnings['commission_rate'], 1); ?>%)</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3>&#2547; <?php echo number_format($earnings['net'], 2); ?></h3>
                <p>Net Payout</p>
            </div>
        </div>
    </div>

    <!-- Hidden data for JS chart -->
    <input type="hidden" id="revenue-data" value="<?php echo htmlspecialchars($revenue_json); ?>">
    <input type="hidden" id="volume-data"  value="<?php echo htmlspecialchars($order_volume_json); ?>">

    <!-- Revenue Bar Chart -->
    <div class="analytics-section">
        <h2>Revenue Trend</h2>
        <div id="revenue-chart" class="bar-chart-wrap">
            <!-- built by JS -->
        </div>
    </div>

    <!-- Top 5 Products -->
    <div class="analytics-section">
        <h2>Top 5 Products</h2>
        <?php if (!empty($top_products)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product Name</th>
                    <th>Total Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_products as $rank => $prod): ?>
                <tr>
                    <td><?php echo $rank + 1; ?></td>
                    <td><?php echo sanitize($prod['name']); ?></td>
                    <td><?php echo (int)$prod['total_sold']; ?></td>
                    <td>&#2547; <?php echo number_format($prod['total_revenue'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="empty-state">No product sales data available for this period.</p>
        <?php endif; ?>
    </div>

    <!-- Order Volume Table -->
    <div class="analytics-section">
        <h2>Order Volume</h2>
        <div id="volume-table-wrap">
            <!-- built by JS -->
        </div>
    </div>
</div>

<script>
window.onload = function() {
    // --- Revenue Bar Chart ---
    var revenueRaw = document.getElementById('revenue-data').value;
    var data;
    try {
        data = JSON.parse(revenueRaw);
    } catch (e) {
        data = [];
    }

    var chartDiv = document.getElementById('revenue-chart');
    if (data && data.length > 0) {
        var maxVal = 0;
        var i;
        for (i = 0; i < data.length; i++) {
            if (parseFloat(data[i].revenue) > maxVal) {
                maxVal = parseFloat(data[i].revenue);
            }
        }
        if (maxVal === 0) { maxVal = 1; }

        var chartHtml = '<div class="bar-chart">';
        for (i = 0; i < data.length; i++) {
            var barHeight = Math.round((parseFloat(data[i].revenue) / maxVal) * 150);
            chartHtml += '<div class="bar-item">';
            chartHtml += '<div class="bar-label-top">' + parseFloat(data[i].revenue).toFixed(0) + '</div>';
            chartHtml += '<div class="bar" style="height:' + barHeight + 'px;"></div>';
            chartHtml += '<div class="bar-label">' + data[i].label + '</div>';
            chartHtml += '</div>';
        }
        chartHtml += '</div>';
        chartDiv.innerHTML = chartHtml;
    } else {
        chartDiv.innerHTML = '<p class="empty-state">No revenue data available.</p>';
    }

    // --- Order Volume Table ---
    var volumeRaw = document.getElementById('volume-data').value;
    var volumeData;
    try {
        volumeData = JSON.parse(volumeRaw);
    } catch (e) {
        volumeData = [];
    }

    var volWrap = document.getElementById('volume-table-wrap');
    if (volumeData && volumeData.length > 0) {
        var tableHtml = '<table class="data-table">';
        tableHtml += '<thead><tr><th>Date</th><th>Orders</th></tr></thead><tbody>';
        var j;
        for (j = 0; j < volumeData.length; j++) {
            tableHtml += '<tr>';
            tableHtml += '<td>' + volumeData[j].date + '</td>';
            tableHtml += '<td>' + volumeData[j].count + '</td>';
            tableHtml += '</tr>';
        }
        tableHtml += '</tbody></table>';
        volWrap.innerHTML = tableHtml;
    } else {
        volWrap.innerHTML = '<p class="empty-state">No order volume data available.</p>';
    }
};
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
