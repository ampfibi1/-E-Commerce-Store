<?php
$page_title = 'My Addresses';
include APP . '/views/layouts/header.php';
?>
<div class="container" style="max-width:720px;margin:30px auto;">
<h1>My Addresses</h1>

<?php $flash = get_flash(); if ($flash): ?>
    <div class="alert alert-<?php echo sanitize($flash['type']); ?>"><?php echo sanitize($flash['msg']); ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">Please fix the errors below.</div>
<?php endif; ?>

<!-- Existing Addresses -->
<?php if (!empty($addresses)): ?>
    <div style="margin-bottom:20px;">
    <?php foreach ($addresses as $addr): ?>
        <div class="card" style="margin-bottom:10px;padding:15px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <strong><?php echo sanitize($addr['label']); ?></strong>
                <?php if ((int)$addr['is_default']): ?> <span class="badge">Default</span><?php endif; ?>
                <div><?php echo sanitize($addr['address_line']); ?>, <?php echo sanitize($addr['city']); ?> <?php echo sanitize($addr['zip']); ?></div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php if (!(int)$addr['is_default']): ?>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="set_default">
                    <input type="hidden" name="id" value="<?php echo (int)$addr['id']; ?>">
                    <button type="submit" class="btn btn-secondary btn-small">Set Default</button>
                </form>
                <?php endif; ?>
                <form method="POST" style="margin:0;" onsubmit="return confirm('Delete this address?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$addr['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No saved addresses yet.</p>
<?php endif; ?>

<!-- Add New Address -->
<div class="card">
    <div class="card-header"><h3 class="card-title">Add New Address</h3></div>
    <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=addresses" novalidate>
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label>Label (e.g. Home, Office)</label>
            <input type="text" name="label" class="form-control" value="<?php echo isset($old['label']) ? $old['label'] : ''; ?>">
            <?php if (!empty($errors['label'])): ?><span class="err"><?php echo sanitize($errors['label']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Address Line</label>
            <textarea name="address_line" class="form-control" rows="2"><?php echo isset($old['address_line']) ? $old['address_line'] : ''; ?></textarea>
            <?php if (!empty($errors['address_line'])): ?><span class="err"><?php echo sanitize($errors['address_line']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>City</label>
            <input type="text" name="city" class="form-control" value="<?php echo isset($old['city']) ? $old['city'] : ''; ?>">
            <?php if (!empty($errors['city'])): ?><span class="err"><?php echo sanitize($errors['city']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Zip / Postal Code</label>
            <input type="text" name="zip" class="form-control" value="<?php echo isset($old['zip']) ? $old['zip'] : ''; ?>">
            <?php if (!empty($errors['zip'])): ?><span class="err"><?php echo sanitize($errors['zip']); ?></span><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Add Address</button>
    </form>
</div>
</div>
<?php include APP . '/views/layouts/footer.php'; ?>
