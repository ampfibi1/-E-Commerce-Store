<?php
$is_edit_mode = isset($is_edit) && $is_edit;
$page_title = $is_edit_mode ? 'Edit Zone' : 'Add Zone';
$form_action = $is_edit_mode
    ? BASE_URL . '?c=delivery&a=zone_edit&id=' . (int)$zone['id']
    : BASE_URL . '?c=delivery&a=zone_add';
include APP . '/views/layouts/header.php';
?>

<div class="delivery-dashboard">
    <div class="dashboard-header">
        <h1><?php echo $is_edit_mode ? 'Edit Delivery Zone' : 'Add Delivery Zone'; ?></h1>
        <a href="<?php echo BASE_URL; ?>?c=delivery&a=zones" class="btn btn-secondary">&larr; Back to Zones</a>
    </div>

    <div class="dashboard-section form-section">
        <form method="POST"
              action="<?php echo $form_action; ?>"
              id="zoneForm"
              novalidate
              onsubmit="return validateZoneForm()">

            <div class="form-group">
                <label for="zone_name">Zone Name</label>
                <input type="text" id="zone_name" name="zone_name" class="form-control"
                       placeholder="e.g. Dhaka City"
                       value="<?php echo sanitize(isset($old['zone_name']) ? $old['zone_name'] : ''); ?>">
                <span class="err" id="err_zone_name"><?php echo isset($errors['zone_name']) ? sanitize($errors['zone_name']) : ''; ?></span>
            </div>

            <div class="form-group">
                <label for="delivery_fee">Delivery Fee (&#2547;)</label>
                <input type="text" id="delivery_fee" name="delivery_fee" class="form-control"
                       placeholder="e.g. 60"
                       value="<?php echo sanitize(isset($old['delivery_fee']) ? $old['delivery_fee'] : ''); ?>">
                <span class="err" id="err_delivery_fee"><?php echo isset($errors['delivery_fee']) ? sanitize($errors['delivery_fee']) : ''; ?></span>
            </div>

            <div class="form-group">
                <label for="estimated_days">Estimated Delivery Days</label>
                <input type="text" id="estimated_days" name="estimated_days" class="form-control"
                       placeholder="e.g. 2"
                       value="<?php echo sanitize(isset($old['estimated_days']) ? $old['estimated_days'] : ''); ?>">
                <span class="err" id="err_estimated_days"><?php echo isset($errors['estimated_days']) ? sanitize($errors['estimated_days']) : ''; ?></span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?php echo $is_edit_mode ? 'Update Zone' : 'Add Zone'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>?c=delivery&a=zones" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
