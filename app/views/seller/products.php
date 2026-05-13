<?php
$page_title = 'My Products';
include APP . '/views/layouts/header.php';
?>

<div class="seller-products">
    <div class="page-header">
        <h1>My Products</h1>
        <a href="?c=seller&a=addProduct" class="btn btn-primary">&#10133; Add New Product</a>
    </div>

    <?php if (!empty($products)): ?>
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
                    <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo sanitize($product['primary_image']); ?>"
                         alt="<?php echo sanitize($product['name']); ?>"
                         class="product-thumb">
                    <?php else: ?>
                    <span class="no-image">No Image</span>
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
                    <a href="?c=seller&a=editProduct&id=<?php echo (int)$product['id']; ?>" class="btn btn-sm btn-warning">Edit</a>

                    <!-- Delete Form -->
                    <form action="?c=seller&a=products&do=delete&id=<?php echo (int)$product['id']; ?>" method="POST" style="display:inline;"
                          onsubmit="return confirmDelete(this)">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <p>You have not added any products yet.</p>
        <a href="?c=seller&a=addProduct" class="btn btn-primary">Add Your First Product</a>
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
