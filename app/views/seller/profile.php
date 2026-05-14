<?php
$page_title = 'Seller Profile';
include APP . '/views/layouts/header.php';
?>

<div class="seller-profile">
    <h1>My Shop Profile</h1>

    <form action="?c=seller&a=profile" method="POST" novalidate id="sellerProfileForm" enctype="multipart/form-data" onsubmit="return validateSellerProfileForm()">

        <!-- Shop Name -->
        <div class="form-group">
            <label for="shop_name">Shop Name</label>
            <input type="text" id="shop_name" name="shop_name" class="form-control"
                   value="<?php echo sanitize(isset($old['shop_name']) ? $old['shop_name'] : (isset($seller['shop_name']) ? $seller['shop_name'] : '')); ?>">
            <span class="err" id="err_shop_name">
                <?php echo isset($errors['shop_name']) ? sanitize($errors['shop_name']) : ''; ?>
            </span>
        </div>

        <!-- Shop Description -->
        <div class="form-group">
            <label for="shop_description">Shop Description</label>
            <textarea id="shop_description" name="shop_description" class="form-control" rows="4"><?php echo sanitize(isset($old['shop_description']) ? $old['shop_description'] : (isset($seller['shop_description']) ? $seller['shop_description'] : '')); ?></textarea>
            <span class="err" id="err_shop_description">
                <?php echo isset($errors['shop_description']) ? sanitize($errors['shop_description']) : ''; ?>
            </span>
        </div>

        <!-- Address -->
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" class="form-control" rows="3"><?php echo sanitize(isset($old['address']) ? $old['address'] : (isset($seller['address']) ? $seller['address'] : '')); ?></textarea>
            <span class="err" id="err_address">
                <?php echo isset($errors['address']) ? sanitize($errors['address']) : ''; ?>
            </span>
        </div>

        <!-- Shop Logo -->
        <div class="form-group">
            <label for="shop_logo">Shop Logo</label>
            <?php if (!empty($seller['shop_logo'])): ?>
            <div class="current-logo">
                <img src="<?php echo BASE_URL; ?>uploads/logos/<?php echo sanitize($seller['shop_logo']); ?>" alt="Current Shop Logo" class="logo-preview">
                <p class="text-muted">Current logo — upload a new file to replace it</p>
            </div>
            <?php endif; ?>
            <input type="file" id="shop_logo" name="shop_logo" class="form-control" accept="image/*">
            <span class="err" id="err_shop_logo">
                <?php echo isset($errors['shop_logo']) ? sanitize($errors['shop_logo']) : ''; ?>
            </span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Profile</button>
            <a href="?c=seller&a=dashboard" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<script>
function validateSellerProfileForm() {
    var valid = true;

    var shopName = document.getElementById('shop_name').value;
    var shopDesc = document.getElementById('shop_description').value;
    var address  = document.getElementById('address').value;

    document.getElementById('err_shop_name').innerHTML = '';
    document.getElementById('err_shop_description').innerHTML = '';
    document.getElementById('err_address').innerHTML = '';

    if (shopName.trim() === '') {
        document.getElementById('err_shop_name').innerHTML = 'Shop name is required.';
        valid = false;
    }

    if (shopDesc.trim() === '') {
        document.getElementById('err_shop_description').innerHTML = 'Shop description is required.';
        valid = false;
    }

    if (address.trim() === '') {
        document.getElementById('err_address').innerHTML = 'Address is required.';
        valid = false;
    }

    return valid;
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
