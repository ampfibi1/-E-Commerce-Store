<?php
$page_title = 'Browse Products';
include APP . '/views/layouts/header.php';
?>
<div class="container" style="max-width:1100px;margin:30px auto;">
<h1>Browse Products</h1>

<!-- Filters -->
<form method="GET" action="<?php echo BASE_URL; ?>" style="background:#f7f7f7;padding:15px;border-radius:6px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
    <input type="hidden" name="c" value="customer">
    <input type="hidden" name="a" value="products">
    <div>
        <label>Search</label>
        <input type="text" name="q" class="form-control" value="<?php echo isset($_GET['q']) ? sanitize($_GET['q']) : ''; ?>" placeholder="Product name...">
    </div>
    <div>
        <label>Category</label>
        <select name="cat" class="form-control">
            <option value="">All</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>" <?php echo (isset($_GET['cat']) && (int)$_GET['cat'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                    <?php echo sanitize($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Min Price</label>
        <input type="text" name="min_p" class="form-control" style="width:90px;" value="<?php echo isset($_GET['min_p']) ? sanitize($_GET['min_p']) : ''; ?>">
    </div>
    <div>
        <label>Max Price</label>
        <input type="text" name="max_p" class="form-control" style="width:90px;" value="<?php echo isset($_GET['max_p']) ? sanitize($_GET['max_p']) : ''; ?>">
    </div>
    <div>
        <label>Min Rating</label>
        <select name="min_r" class="form-control">
            <option value="">Any</option>
            <?php for ($r = 1; $r <= 5; $r++): ?>
                <option value="<?php echo $r; ?>" <?php echo (isset($_GET['min_r']) && (int)$_GET['min_r'] === $r) ? 'selected' : ''; ?>>
                    <?php echo $r; ?>+
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div><button type="submit" class="btn btn-primary">Filter</button></div>
    <div><a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-secondary">Reset</a></div>
</form>

<!-- Product Grid -->
<?php if (empty($products)): ?>
    <p>No products match your filters.</p>
<?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;">
    <?php foreach ($products as $p): ?>
        <div class="card" style="padding:0;overflow:hidden;">
            <?php if ($p['primary_image_path']): ?>
                <img src="<?php echo UPLOAD_URL . sanitize($p['primary_image_path']); ?>" alt="<?php echo sanitize($p['name']); ?>" style="width:100%;height:180px;object-fit:cover;">
            <?php else: ?>
                <div style="width:100%;height:180px;background:#eee;display:flex;align-items:center;justify-content:center;color:#aaa;">No Image</div>
            <?php endif; ?>
            <div style="padding:12px;">
                <h4 style="margin:0 0 6px;"><?php echo sanitize($p['name']); ?></h4>
                <div style="color:#888;font-size:0.85em;margin-bottom:4px;"><?php echo sanitize($p['category_name']); ?></div>
                <div style="font-weight:bold;font-size:1.1em;margin-bottom:6px;">৳<?php echo number_format((float)$p['price'], 2); ?></div>
                <div style="font-size:0.85em;margin-bottom:10px;">
                    Rating: <?php echo number_format((float)$p['avg_rating'], 1); ?>/5
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a href="<?php echo BASE_URL; ?>?c=customer&a=product&id=<?php echo (int)$p['id']; ?>" class="btn btn-secondary btn-small">View</a>
                    <?php if (isset($_SESSION['uid']) && $_SESSION['role'] === 'customer'): ?>
                        <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=cart" style="margin:0;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn btn-primary btn-small">Add to Cart</button>
                        </form>
                        <button class="btn btn-small wishlist-btn <?php echo in_array((int)$p['id'], $wishlist_ids) ? 'btn-danger' : 'btn-secondary'; ?>"
                                data-product="<?php echo (int)$p['id']; ?>">
                            <?php echo in_array((int)$p['id'], $wishlist_ids) ? '&#9829;' : '&#9825;'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>

<script>
document.querySelectorAll('.wishlist-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var pid = this.getAttribute('data-product');
        var self = this;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo BASE_URL; ?>../api/wishlist_toggle.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    if (data.in_wishlist) {
                        self.innerHTML = '&#9829;';
                        self.classList.remove('btn-secondary');
                        self.classList.add('btn-danger');
                    } else {
                        self.innerHTML = '&#9825;';
                        self.classList.remove('btn-danger');
                        self.classList.add('btn-secondary');
                    }
                }
            }
        };
        xhr.send('product_id=' + pid);
    });
});
</script>
<?php include APP . '/views/layouts/footer.php'; ?>
