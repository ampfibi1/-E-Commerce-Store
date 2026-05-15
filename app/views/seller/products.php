<?php
$page_title = 'My Products';
include APP . '/views/layouts/header.php';
?>

<div class="seller-products" style="max-width:1100px;margin:0 auto;">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
        <div>
            <h1 style="margin:0;">My Products</h1>
            <p style="color:#6b7a99;margin:0.2rem 0 0;font-size:0.9rem;"><?php echo count($products); ?> product<?php echo count($products) !== 1 ? 's' : ''; ?> in your store</p>
        </div>
        <a href="<?php echo BASE_URL; ?>?c=seller&a=addProduct" class="btn btn-primary">+ Add New Product</a>
    </div>

    <?php if (!empty($products)): ?>
    <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.06);border:1px solid #e6ecf5;overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock Qty</th>
                <th>Avg Rating</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $i => $product): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td>
                    <?php if (!empty($product['primary_image'])): ?>
                    <img src="<?php echo UPLOAD_URL . 'product_images/' . sanitize($product['primary_image']); ?>"
                         alt="<?php echo sanitize($product['name']); ?>"
                         class="product-thumb">
                    <?php else: ?>
                    <div class="no-img-placeholder"><?php icon_box(18); ?></div>
                    <?php endif; ?>
                </td>
                <td><?php echo sanitize($product['name']); ?></td>
                <td><?php echo sanitize($product['category_name']); ?></td>
                <td>&#2547; <?php echo number_format($product['price'], 2); ?></td>
                <td>
                    <?php echo (int)$product['stock_qty']; ?>
                    <?php if (in_array($product['id'], $low_stock_ids) || (int)$product['stock_qty'] <= 5): ?>
                    <span class="low-stock-badge">Low Stock</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($product['avg_rating'])): ?>
                    <span class="rating">&#9733; <?php echo number_format($product['avg_rating'], 1); ?></span>
                    <?php else: ?>
                    <span class="text-muted">No ratings</span>
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Toggle Availability -->
                    <form action="?c=seller&a=products&do=toggle&id=<?php echo (int)$product['id']; ?>" method="POST" style="display:inline;">
                        <button type="submit" class="btn btn-sm <?php echo $product['is_available'] ? 'btn-success' : 'btn-secondary'; ?>">
                            <?php echo $product['is_available'] ? 'Yes' : 'No'; ?>
                        </button>
                    </form>
                </td>
                <td>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <a href="<?php echo BASE_URL; ?>?c=seller&a=editProduct&id=<?php echo (int)$product['id']; ?>"
                           style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;background:#fff;border:1.5px solid #2c7be5;color:#2c7be5;border-radius:6px;font-size:0.82rem;font-weight:600;text-decoration:none;white-space:nowrap;">
                            <?php icon_check(13); ?> Edit
                        </a>
                        <form action="<?php echo BASE_URL; ?>?c=seller&a=products&do=delete&id=<?php echo (int)$product['id']; ?>" method="POST" style="margin:0;"
                              onsubmit="return confirmDelete(this)">
                            <button type="submit"
                                    style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;background:#fff;border:1.5px solid #e74c3c;color:#e74c3c;border-radius:6px;font-size:0.82rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                                <?php icon_alert(13); ?> Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon"><?php icon_box(36); ?></div>
        <h3>No products yet</h3>
        <p>Add your first product to start selling.</p>
        <a href="<?php echo BASE_URL; ?>?c=seller&a=addProduct" class="btn btn-primary" style="margin-top:0.75rem;">Add Your First Product</a>
    </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(form) {
    var result = confirm('Are you sure you want to delete this product? This action cannot be undone.');
    return result;
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
