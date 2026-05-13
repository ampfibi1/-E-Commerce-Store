<?php
$page_title = 'Add New Product';
include APP . '/views/layouts/header.php';
?>

<div class="seller-add-product">
    <div class="page-header">
        <h1>Add New Product</h1>
        <a href="?c=seller&a=products" class="btn btn-secondary">&larr; Back to Products</a>
    </div>

    <form action="?c=seller&a=addProduct" method="POST" novalidate id="productForm" enctype="multipart/form-data"
          onsubmit="return validateProductForm()">

        <!-- Product Name -->
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?php echo sanitize(isset($old['name']) ? $old['name'] : ''); ?>">
            <span class="err" id="err_name">
                <?php echo isset($errors['name']) ? sanitize($errors['name']) : ''; ?>
            </span>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="5"><?php echo sanitize(isset($old['description']) ? $old['description'] : ''); ?></textarea>
            <span class="err" id="err_description">
                <?php echo isset($errors['description']) ? sanitize($errors['description']) : ''; ?>
            </span>
        </div>

        <!-- Price -->
        <div class="form-group">
            <label for="price">Price (&#2547;)</label>
            <input type="text" id="price" name="price" class="form-control"
                   value="<?php echo sanitize(isset($old['price']) ? $old['price'] : ''); ?>">
            <span class="err" id="err_price">
                <?php echo isset($errors['price']) ? sanitize($errors['price']) : ''; ?>
            </span>
        </div>

        <!-- Stock Quantity -->
        <div class="form-group">
            <label for="stock_qty">Stock Quantity</label>
            <input type="text" id="stock_qty" name="stock_qty" class="form-control"
                   value="<?php echo sanitize(isset($old['stock_qty']) ? $old['stock_qty'] : ''); ?>">
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
                <option value="<?php echo (int)$parent['id']; ?>"
                    <?php echo (isset($old['category_id']) && $old['category_id'] == $parent['id']) ? 'selected' : ''; ?>>
                    <?php echo sanitize($parent['name']); ?>
                </option>
                <?php if (!empty($parent['children'])): ?>
                    <?php foreach ($parent['children'] as $child): ?>
                    <option value="<?php echo (int)$child['id']; ?>"
                        <?php echo (isset($old['category_id']) && $old['category_id'] == $child['id']) ? 'selected' : ''; ?>>
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

        <!-- Primary Image (Required for Add) -->
        <div class="form-group">
            <label for="primary_image">Primary Image <span class="text-danger">*</span></label>
            <input type="file" id="primary_image" name="primary_image" class="form-control" accept="image/*">
            <small class="text-muted">This is the main product image shown in listings.</small>
            <span class="err" id="err_primary_image">
                <?php echo isset($errors['primary_image']) ? sanitize($errors['primary_image']) : ''; ?>
            </span>
        </div>

        <!-- Additional Images -->
        <div class="form-group">
            <label for="images">Additional Images (up to 4)</label>
            <input type="file" id="images" name="images[]" class="form-control" accept="image/*" multiple>
            <small class="text-muted">You may upload up to 4 extra images for this product.</small>
            <span class="err" id="err_images">
                <?php echo isset($errors['images']) ? sanitize($errors['images']) : ''; ?>
            </span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Product</button>
            <a href="?c=seller&a=products" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
