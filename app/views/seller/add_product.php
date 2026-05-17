<?php
$page_title = 'Add New Product';
include APP . '/views/layouts/header.php';
?>

<div style="max-width:780px;margin:0 auto;">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <div>
            <h1 style="margin:0;">Add New Product</h1>
            <p style="color:#6b7a99;margin:0.2rem 0 0;font-size:0.9rem;">Fill in the details below to list a new product.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>?c=seller&a=products" class="btn btn-secondary">&larr; Back</a>
    </div>

    <form action="<?php echo BASE_URL; ?>?c=seller&a=addProduct" method="POST" novalidate
          id="productForm" enctype="multipart/form-data" onsubmit="return validateProductForm()">

        <!-- ── Section 1: Basic Info ─────────────────────────── -->
        <div class="form-card" style="margin-bottom:1.25rem;">
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid #eef2f8;">
                Basic Information
            </h2>

            <div class="form-group">
                <label class="form-label" for="name">Product Name</label>
                <input type="text" id="name" name="name" class="form-control"
                       placeholder="e.g. Sony WH-1000XM5 Headphones"
                       value="<?php echo sanitize(isset($old['name']) ? $old['name'] : ''); ?>">
                <span class="err" id="err_name"><?php echo isset($errors['name']) ? sanitize($errors['name']) : ''; ?></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"
                          placeholder="Describe what makes this product great..."><?php echo sanitize(isset($old['description']) ? $old['description'] : ''); ?></textarea>
                <span class="err" id="err_description"><?php echo isset($errors['description']) ? sanitize($errors['description']) : ''; ?></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">— Select a category —</option>
                    <?php foreach ($categories as $parent): ?>
                    <option value="<?php echo (int)$parent['id']; ?>"
                        <?php echo (isset($old['category_id']) && $old['category_id'] == $parent['id']) ? 'selected' : ''; ?>>
                        <?php echo sanitize($parent['name']); ?>
                    </option>
                    <?php if (!empty($parent['children'])): ?>
                        <?php foreach ($parent['children'] as $child): ?>
                        <option value="<?php echo (int)$child['id']; ?>"
                            <?php echo (isset($old['category_id']) && $old['category_id'] == $child['id']) ? 'selected' : ''; ?>>
                            &nbsp;&nbsp;&mdash; <?php echo sanitize($child['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <span class="err" id="err_category_id"><?php echo isset($errors['category_id']) ? sanitize($errors['category_id']) : ''; ?></span>
            </div>
        </div>

        <!-- ── Section 2: Pricing & Stock ───────────────────── -->
        <div class="form-card" style="margin-bottom:1.25rem;">
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid #eef2f8;">
                Pricing &amp; Stock
            </h2>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="price">Price (&#2547;)</label>
                    <input type="text" id="price" name="price" class="form-control"
                           placeholder="0.00"
                           value="<?php echo sanitize(isset($old['price']) ? $old['price'] : ''); ?>">
                    <span class="err" id="err_price"><?php echo isset($errors['price']) ? sanitize($errors['price']) : ''; ?></span>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="stock_qty">Stock Quantity</label>
                    <input type="text" id="stock_qty" name="stock_qty" class="form-control"
                           placeholder="e.g. 50"
                           value="<?php echo sanitize(isset($old['stock_qty']) ? $old['stock_qty'] : ''); ?>">
                    <span class="err" id="err_stock_qty"><?php echo isset($errors['stock_qty']) ? sanitize($errors['stock_qty']) : ''; ?></span>
                </div>
            </div>
        </div>

        <!-- ── Section 3: Images ─────────────────────────────── -->
        <div class="form-card" style="margin-bottom:1.5rem;">
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid #eef2f8;">
                Product Images
            </h2>

            <div class="form-group">
                <label class="form-label" for="primary_image">
                    Primary Image <span style="color:#e74c3c;">*</span>
                </label>
                <div style="border:2px dashed #d0d9e8;border-radius:8px;padding:1.25rem;text-align:center;background:#fafbff;">
                    <input type="file" id="primary_image" name="primary_image" accept="image/*"
                           style="display:block;margin:0 auto;cursor:pointer;">
                    <p style="color:#6b7a99;font-size:0.82rem;margin:0.5rem 0 0;">Shown on product cards and listings. JPG, PNG, or WebP.</p>
                </div>
                <span class="err" id="err_primary_image"><?php echo isset($errors['primary_image']) ? sanitize($errors['primary_image']) : ''; ?></span>
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label" for="images">Additional Images <span style="color:#888;font-weight:400;">(up to 4, optional)</span></label>
                <div style="border:2px dashed #d0d9e8;border-radius:8px;padding:1.25rem;text-align:center;background:#fafbff;">
                    <input type="file" id="images" name="images[]" accept="image/*" multiple
                           style="display:block;margin:0 auto;cursor:pointer;">
                    <p style="color:#6b7a99;font-size:0.82rem;margin:0.5rem 0 0;">Shown in the product detail gallery. Hold Ctrl to select multiple.</p>
                </div>
                <span class="err" id="err_images"><?php echo isset($errors['images']) ? sanitize($errors['images']) : ''; ?></span>
            </div>
        </div>

        <!-- ── Actions ───────────────────────────────────────── -->
        <div style="display:flex;gap:1rem;justify-content:flex-end;">
            <a href="<?php echo BASE_URL; ?>?c=seller&a=products" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:0.65rem 2rem;">Add Product</button>
        </div>

    </form>
</div>

<?php include APP . '/views/layouts/footer.php'; ?>
