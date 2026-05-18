<?php
$page_title = 'Active Deliveries';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1>Active Deliveries</h1>
        <p class="text-muted">Live status updates via AJAX &mdash; no page reload.</p>
    </div>

    <div class="dashboard-section">
        <h2>In Progress (<?php echo count($deliveries); ?>)</h2>
        <?php if (empty($deliveries)): ?>
            <p class="empty-state">No active deliveries at the moment.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Seller</th>
                    <th>Agent</th>
                    <th>Vehicle</th>
                    <th>Zone</th>
                    <th>Amount</th>
                    <th>Assigned</th>
                    <th>Time Since</th>
                    <th>Current Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $d): ?>
                <?php
                    $assigned_ts  = strtotime($d['assigned_at']);
                    $diff_seconds = time() - $assigned_ts;
                    $diff_hours   = floor($diff_seconds / 3600);
                    $diff_minutes = floor(($diff_seconds % 3600) / 60);
                    $time_since   = ($diff_hours > 0) ? $diff_hours . 'h ' . $diff_minutes . 'm' : $diff_minutes . 'm';

                    $badge_map = array(
                        'assigned'   => 'badge-info',
                        'picked_up'  => 'badge-warning',
                        'in_transit' => 'badge-info',
                        'delivered'  => 'badge-success',
                        'failed'     => 'badge-danger',
                    );
                    $badge_class = isset($badge_map[$d['status']]) ? $badge_map[$d['status']] : 'badge-secondary';
                ?>
                <tr id="row-<?php echo (int)$d['assignment_id']; ?>">
                    <td>#<?php echo (int)$d['order_id']; ?></td>
                    <td><?php echo sanitize($d['shop_name'] ?? '—'); ?></td>
                    <td>
                        <?php echo sanitize($d['agent_name']); ?><br>
                        <small class="text-muted"><?php echo sanitize($d['agent_phone']); ?></small>
                    </td>
                    <td><?php echo sanitize($d['vehicle_type']); ?></td>
                    <td><?php echo sanitize($d['zone_name']); ?></td>
                    <td>&#2547; <?php echo number_format((float)$d['total_amount'], 2); ?></td>
                    <td><?php echo sanitize(date('d M, H:i', strtotime($d['assigned_at']))); ?></td>
                    <td><?php echo sanitize($time_since); ?> ago</td>
                    <td>
                        <span class="badge <?php echo $badge_class; ?> status-badge">
                            <?php echo sanitize(str_replace('_', ' ', $d['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <select class="form-control status-select"
                                id="select-<?php echo (int)$d['assignment_id']; ?>"
                                data-prev="<?php echo sanitize($d['status']); ?>"
                                onchange="onStatusChange(<?php echo (int)$d['assignment_id']; ?>, this)">
                            <option value="assigned"   <?php echo ($d['status'] === 'assigned')   ? 'selected' : ''; ?>>Assigned</option>
                            <option value="picked_up"  <?php echo ($d['status'] === 'picked_up')  ? 'selected' : ''; ?>>Picked Up</option>
                            <option value="in_transit" <?php echo ($d['status'] === 'in_transit') ? 'selected' : ''; ?>>In Transit</option>
                            <option value="delivered"  <?php echo ($d['status'] === 'delivered')  ? 'selected' : ''; ?>>Delivered</option>
                            <option value="failed"     <?php echo ($d['status'] === 'failed')     ? 'selected' : ''; ?>>Failed</option>
                        </select>
                        <div id="fail-box-<?php echo (int)$d['assignment_id']; ?>" style="display:none;margin-top:6px;">
                            <input type="text"
                                   id="fail-reason-<?php echo (int)$d['assignment_id']; ?>"
                                   class="form-control form-control-sm"
                                   placeholder="Reason for failure (required)"
                                   style="margin-bottom:4px;">
                            <button type="button"
                                    class="btn btn-sm btn-danger"
                                    onclick="submitFailure(<?php echo (int)$d['assignment_id']; ?>)">Save</button>
                            <button type="button"
                                    class="btn btn-sm btn-secondary"
                                    onclick="cancelFailure(<?php echo (int)$d['assignment_id']; ?>)">Cancel</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
function onStatusChange(assignmentId, selectEl) {
    var newStatus = selectEl.value;
    // For 'failed', reveal the inline reason input instead of submitting.
    if (newStatus === 'failed') {
        var box = document.getElementById('fail-box-' + assignmentId);
        if (box) {
            box.style.display = 'block';
            var input = document.getElementById('fail-reason-' + assignmentId);
            if (input) { input.focus(); }
        }
        return;
    }
    ajaxUpdateStatus(assignmentId, selectEl, '');
}

function cancelFailure(assignmentId) {
    var box = document.getElementById('fail-box-' + assignmentId);
    var sel = document.getElementById('select-' + assignmentId);
    if (box) { box.style.display = 'none'; }
    if (sel) { sel.value = sel.getAttribute('data-prev') || 'assigned'; }
}

function submitFailure(assignmentId) {
    var input = document.getElementById('fail-reason-' + assignmentId);
    var sel   = document.getElementById('select-' + assignmentId);
    var reason = input ? input.value.trim() : '';
    if (reason === '') {
        if (input) { input.focus(); input.style.borderColor = '#dc3545'; }
        return;
    }
    var box = document.getElementById('fail-box-' + assignmentId);
    if (box) { box.style.display = 'none'; }
    ajaxUpdateStatus(assignmentId, sel, reason);
}

function ajaxUpdateStatus(assignmentId, selectEl, failureReason) {
    var newStatus = selectEl.value;
    var prev      = selectEl.getAttribute('data-prev') || '';
    if (failureReason === undefined || failureReason === null) { failureReason = ''; }

    selectEl.disabled = true;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL; ?>../ajax/delivery_update_status.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState !== 4) { return; }
        selectEl.disabled = false;
        if (xhr.status !== 200) {
            alert('Network error. Status not saved.');
            selectEl.value = prev;
            return;
        }
        var resp;
        try { resp = JSON.parse(xhr.responseText); }
        catch (e) {
            alert('Server error. Status not saved.');
            selectEl.value = prev;
            return;
        }
        if (!resp.success) {
            alert(resp.message || 'Could not save status.');
            selectEl.value = prev;
            return;
        }

        selectEl.setAttribute('data-prev', newStatus);

        var row = document.getElementById('row-' + assignmentId);
        // 'delivered' and 'failed' leave the active list — remove the row.
        if (newStatus === 'delivered' || newStatus === 'failed') {
            if (row && row.parentNode) { row.parentNode.removeChild(row); }
            return;
        }
        if (row) {
            var badge = row.querySelector('.status-badge');
            if (badge) {
                badge.textContent = newStatus.replace('_', ' ');
                badge.className = 'badge status-badge ' + (
                    newStatus === 'picked_up'  ? 'badge-warning' :
                    'badge-info'
                );
            }
        }
    };
    var body = 'assignment_id=' + encodeURIComponent(assignmentId)
             + '&status=' + encodeURIComponent(newStatus)
             + '&failure_reason=' + encodeURIComponent(failureReason);
    xhr.send(body);
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
