<?php
$page_title = 'My Profile';
include APP . '/views/layouts/header.php';
?>
<div class="container" style="max-width:720px;margin:30px auto;">
<h1>My Profile</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">Please fix the errors below.</div>
<?php endif; ?>

<!-- Update Info -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><h3 class="card-title">Account Information</h3></div>
    <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=profile" novalidate>
        <input type="hidden" name="action" value="update_info">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control"
                   value="<?php echo isset($old['name']) ? $old['name'] : sanitize($user['name']); ?>">
            <?php if (!empty($errors['name'])): ?><span class="err"><?php echo sanitize($errors['name']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" name="email" class="form-control"
                   value="<?php echo isset($old['email']) ? $old['email'] : sanitize($user['email']); ?>">
            <?php if (!empty($errors['email'])): ?><span class="err"><?php echo sanitize($errors['email']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Phone (11 digits)</label>
            <input type="text" name="phone" class="form-control"
                   value="<?php echo isset($old['phone']) ? $old['phone'] : sanitize($user['phone']); ?>">
            <?php if (!empty($errors['phone'])): ?><span class="err"><?php echo sanitize($errors['phone']); ?></span><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Info</button>
    </form>
</div>

<!-- Change Password -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><h3 class="card-title">Change Password</h3></div>
    <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=profile" novalidate>
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" class="form-control">
            <?php if (!empty($errors['current_password'])): ?><span class="err"><?php echo sanitize($errors['current_password']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>New Password (min. 8 chars)</label>
            <input type="password" name="new_password" class="form-control">
            <?php if (!empty($errors['new_password'])): ?><span class="err"><?php echo sanitize($errors['new_password']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control">
            <?php if (!empty($errors['confirm_password'])): ?><span class="err"><?php echo sanitize($errors['confirm_password']); ?></span><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>

<!-- Upload Profile Pic -->
<div class="card">
    <div class="card-header"><h3 class="card-title">Profile Picture</h3></div>
    <?php if (!empty($user['profile_pic'])): ?>
        <img src="<?php echo UPLOAD_URL . sanitize($user['profile_pic']); ?>" alt="Profile pic" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:12px;">
    <?php endif; ?>
    <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=profile" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="action" value="upload_pic">
        <div class="form-group">
            <label>Upload new picture (JPG/PNG, max 2 MB)</label>
            <input type="file" name="profile_pic" class="form-control" accept="image/*">
            <?php if (!empty($errors['profile_pic'])): ?><span class="err"><?php echo sanitize($errors['profile_pic']); ?></span><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Upload Picture</button>
    </form>
</div>
</div>
<?php include APP . '/views/layouts/footer.php'; ?>
