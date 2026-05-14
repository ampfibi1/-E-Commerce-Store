<?php
$page_title = 'Edit Product';
include APP . '/views/layouts/header.php';
?>

<div class="seller-edit-product">
    <div class="page-header">
        <h1>Edit Product</h1>
        <a href="?c=seller&a=products" class="btn btn-secondary">&larr; Back to Products</a>
    </div>

    <form action="?c=seller&a=editProduct" method="POST" novalidate id="editProductForm" enctype="multipart/form-data"
          onsubmit="return validateEditProductForm()">

        <!-- Hidden product_id -->
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

        <!-- Product Name -->
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?php echo sanitize(isset($old['name']) ? $old['name'] : $product['name']); ?>">
            <span class="err" id="err_name">
                <?php echo isset($errors['name']) ? sanitize($errors['name']) : ''; ?>
            </span>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="5"><?php echo sanitize(isset($old['description']) ? $old['description'] : $product['description']); ?></textarea>
            <span class="err" id="err_description">
                <?php echo isset($errors['description']) ? sanitize($errors['description']) : ''; ?>
            </span>
        </div>

        <!-- Price -->
        <div class="form-group">
            <label for="price">Price (&#2547;)</label>
            <input type="text" id="price" name="price" class="form-control"
                   value="<?php echo sanitize(isset($old['price']) ? $old['price'] : $product['price']); ?>">
            <span class="err" id="err_price">
                <?php echo isset($errors['price']) ? sanitize($errors['price']) : ''; ?>
            </span>
        </div>

        <!-- Stock Quantity -->
        <div class="form-group">
            <label for="stock_qty">Stock Quantity</label>
            <input type="text" id="stock_qty" name="stock_qty" class="form-control"
                   value="<?php echo sanitize(isset($old['stock_qty']) ? $old['stock_qty'] : $product['stock_qty']); ?>">
            <span class="err" id="err_stock_qty">
                <?php echo isset($errors['stock_qty']) ? sanitize($errors['stock_qty']) : ''; ?>
            </span>
        </div>

        <!-- Category -->
        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="form-control">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $parent): ?>
                <?php $selectedParent = (isset($old['category_id']) ? $old['category_id'] : $product['category_id']); ?>
                <option value="<?php echo (int)$parent['id']; ?>"
                    <?php echo ($selectedParent == $parent['id']) ? 'selected' : ''; ?>>
                    <?php echo sanitize($parent['name']); ?>
                </option>
                <?php if (!empty($parent['children'])): ?>
                    <?php foreach ($parent['children'] as $child): ?>
                    <option value="<?php echo (int)$child['id']; ?>"
                        <?php echo ($selectedParent == $child['id']) ? 'selected' : ''; ?>>
                        &mdash; <?php echo sanitize($child['name']); ?>
                    </option>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <span class="err" id="err_category_id">
                <?php echo isset($errors['category_id']) ? sanitize($errors['category_id']) : ''; ?>
            </span>
        </div>

        <!-- Primary Image (Optional for Edit) -->
        <div class="form-group">
            <label for="primary_image">Primary Image</label>
            <?php if (!empty($product['primary_image'])): ?>
            <div class="current-image">
                <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo sanitize($product['primary_image']); ?>"
                     alt="Current Primary Image" class="product-thumb-lg">
                <p class="text-muted">Current primary image — leave blank to keep it.</p>
            </div>
            <?php endif; ?>
            <input type="file" id="primary_image" name="primary_image" class="form-control" accept="image/*">
            <span class="err" id="err_primary_image">
                <?php echo isset($errors['primary_image']) ? sanitize($errors['primary_image']) : ''; ?>
            </span>
        </div>

        <!-- Additional Images -->
        <div class="form-group">
            <label>Additional Images</label>
            <?php if (!empty($images)): ?>
            <div class="current-images">
                <?php foreach ($images as $img): ?>
                <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo sanitize($img['image_path']); ?>"
                     alt="Product Image" class="product-thumb">
                <?php endforeach; ?>
                <p class="text-muted">Upload new images to replace all existing additional images.</p>
            </div>
            <?php endif; ?>
            <input type="file" id="images" name="images[]" class="form-control" accept="image/*" multiple>
            <small class="text-muted">Upload up to 4 extra images. This will replace all existing additional images.</small>
            <span class="err" id="err_images">
                <?php echo isset($errors['images']) ? sanitize($errors['images']) : ''; ?>
            </span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="?c=seller&a=products" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<script>
function validateEditProductForm() {
    var valid = true;

    var nameVal     = document.getElementById('name').value;
    var descVal     = document.getElementById('description').value;
    var priceVal    = document.getElementById('price').value;
    var stockVal    = document.getElementById('stock_qty').value;
    var catVal      = document.getElementById('category_id').value;

    document.getElementById('err_name').innerHTML        = '';
    document.getElementById('err_description').innerHTML = '';
    document.getElementById('err_price').innerHTML       = '';
    document.getElementById('err_stock_qty').innerHTML   = '';
    document.getElementById('err_category_id').innerHTML = '';

    if (nameVal.trim() === '') {
        document.getElementById('err_name').innerHTML = 'Product name is required.';
        valid = false;
    }

    if (descVal.trim() === '') {
        document.getElementById('err_description').innerHTML = 'Description is required.';
        valid = false;
    }

    if (priceVal.trim() === '') {
        document.getElementById('err_price').innerHTML = 'Price is required.';
        valid = false;
    } else if (isNaN(priceVal) || parseFloat(priceVal) <= 0) {
        document.getElementById('err_price').innerHTML = 'Price must be a positive number.';
        valid = false;
    }

    if (stockVal.trim() === '') {
        document.getElementById('err_stock_qty').innerHTML = 'Stock quantity is required.';
        valid = false;
    } else if (isNaN(stockVal) || parseInt(stockVal) < 0) {
        document.getElementById('err_stock_qty').innerHTML = 'Stock quantity must be 0 or more.';
        valid = false;
    }

    if (catVal === '' || catVal === '0') {
        document.getElementById('err_category_id').innerHTML = 'Please select a category.';
        valid = false;
    }

    // primary_image is NOT checked here — optional for edit
    return valid;
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
