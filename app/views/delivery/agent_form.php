<?php
$is_edit_mode = isset($is_edit) && $is_edit;
$page_title = $is_edit_mode ? 'Edit Agent' : 'Add Agent';
$form_action = $is_edit_mode
    ? BASE_URL . '?c=delivery&a=agent_edit&id=' . (int)$agent['id']
    : BASE_URL . '?c=delivery&a=agent_add';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1><?php echo $is_edit_mode ? 'Edit Agent' : 'Add Delivery Agent'; ?></h1>
        <a href="<?php echo BASE_URL; ?>?c=delivery&a=agents" class="btn btn-secondary">&larr; Back to Agents</a>
    </div>

    <div class="dashboard-section form-section">
        <form method="POST"
              action="<?php echo $form_action; ?>"
              id="agentForm"
              novalidate
              onsubmit="return validateAgentForm()">

            <div class="form-group">
                <label for="name">Agent Name</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?php echo sanitize(isset($old['name']) ? $old['name'] : ''); ?>">
                <span class="err" id="err_name"><?php echo isset($errors['name']) ? sanitize($errors['name']) : ''; ?></span>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control"
                       value="<?php echo sanitize(isset($old['phone']) ? $old['phone'] : ''); ?>">
                <span class="err" id="err_phone"><?php echo isset($errors['phone']) ? sanitize($errors['phone']) : ''; ?></span>
            </div>

            <div class="form-group">
                <label for="vehicle_type">Vehicle Type</label>
                <input type="text" id="vehicle_type" name="vehicle_type" class="form-control"
                       placeholder="e.g. Motorcycle, Bicycle, Van"
                       value="<?php echo sanitize(isset($old['vehicle_type']) ? $old['vehicle_type'] : ''); ?>">
                <span class="err" id="err_vehicle_type"><?php echo isset($errors['vehicle_type']) ? sanitize($errors['vehicle_type']) : ''; ?></span>
            </div>

            <?php if ($is_edit_mode): ?>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active"   <?php echo (isset($old['status']) && $old['status'] === 'active')   ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($old['status']) && $old['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?php echo $is_edit_mode ? 'Update Agent' : 'Add Agent'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>?c=delivery&a=agents" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
