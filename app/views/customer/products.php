<?php
$page_title = 'Browse Products';
include APP . '/views/layouts/header.php';
?>
<div class="container" style="max-width:1200px;margin:30px auto;padding:0 1rem;">
<h1 style="margin-bottom:1.2rem;font-size:1.8rem;color:#1a2540;letter-spacing:-0.01em;">Browse Products</h1>

<!-- Filters -->
<form method="GET" action="<?php echo BASE_URL; ?>" class="filter-bar">
    <input type="hidden" name="c" value="customer">
    <input type="hidden" name="a" value="products">
    <div style="flex:1 1 200px;">
        <label>Search</label>
        <input type="text" name="q" class="form-control" value="<?php echo isset($_GET['q']) ? sanitize($_GET['q']) : ''; ?>" placeholder="Product name...">
    </div>
    <div style="min-width:160px;">
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
        <input type="text" name="min_p" class="form-control" style="width:100px;" value="<?php echo isset($_GET['min_p']) ? sanitize($_GET['min_p']) : ''; ?>">
    </div>
    <div>
        <label>Max Price</label>
        <input type="text" name="max_p" class="form-control" style="width:100px;" value="<?php echo isset($_GET['max_p']) ? sanitize($_GET['max_p']) : ''; ?>">
    </div>
    <div>
        <label>Min Rating</label>
        <select name="min_r" class="form-control">
            <option value="">Any</option>
            <?php for ($r = 1; $r <= 5; $r++): ?>
                <option value="<?php echo $r; ?>" <?php echo (isset($_GET['min_r']) && (int)$_GET['min_r'] === $r) ? 'selected' : ''; ?>>
                    <?php echo $r; ?>+ stars
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div><button type="submit" class="btn btn-primary"><?php icon_search(16); ?> Filter</button></div>
    <div><a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-secondary">Reset</a></div>
</form>

<!-- Product Grid -->
<?php if (empty($products)): ?>
    <div class="empty-state">
        <div class="empty-icon"><?php icon_box(56); ?></div>
        <h3>No products match your filters</h3>
        <p>Try a broader search or reset the filters.</p>
    </div>
<?php else: ?>
    <div class="product-grid-v2">
    <?php foreach ($products as $p): ?>
        <?php $img_path = trim((string)$p['primary_image_path']); ?>
        <?php $img_file = $img_path !== '' ? UPLOAD_PATH . 'product_images/' . $img_path : ''; ?>
        <?php $has_img = $img_path !== '' && file_exists($img_file); ?>
        <div class="product-card">
            <a href="<?php echo BASE_URL; ?>?c=customer&a=product&id=<?php echo (int)$p['id']; ?>" class="img-wrap" style="text-decoration:none;">
                <?php if ($has_img): ?>
                    <img src="<?php echo UPLOAD_URL . 'product_images/' . sanitize($img_path); ?>" alt="<?php echo sanitize($p['name']); ?>" loading="lazy">
                <?php else: ?>
                    <div class="img-fallback">
                        <?php icon_box(36); ?>
                        <span>No image</span>
                    </div>
                <?php endif; ?>
            </a>
            <div class="body">
                <div class="cat"><?php echo sanitize($p['category_name']); ?></div>
                <h4 class="name"><?php echo sanitize($p['name']); ?></h4>
                <div style="display:flex;align-items:center;gap:0.3rem;">
                    <?php
                    $rating = (float)$p['avg_rating'];
                    echo '<span class="star-rating" style="--stars:' . $rating . ';">';
                    for ($i = 0; $i < 5; $i++) {
                        echo '<svg class="star" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
                    }
                    echo '<span class="star-fill">';
                    for ($i = 0; $i < 5; $i++) {
                        echo '<svg class="star" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
                    }
                    echo '</span></span>';
                    ?>
                    <span class="rating-text"><?php echo number_format($rating, 1); ?></span>
                </div>
                <div class="price">৳<?php echo number_format((float)$p['price'], 2); ?></div>
                <div class="actions">
                    <?php if (isset($_SESSION['uid']) && $_SESSION['role'] === 'customer'): ?>
                        <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=cart" style="margin:0;flex-grow:1;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn btn-primary btn-small w-100"><?php icon_cart(15); ?> Add to Cart</button>
                        </form>
                        <button class="icon-btn wishlist-btn <?php echo in_array((int)$p['id'], $wishlist_ids) ? 'is-active' : ''; ?>"
                                data-product="<?php echo (int)$p['id']; ?>"
                                aria-label="Toggle wishlist">
                            <?php icon_heart(16, in_array((int)$p['id'], $wishlist_ids)); ?>
                        </button>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>?c=customer&a=product&id=<?php echo (int)$p['id']; ?>" class="btn btn-primary btn-small w-100">View Details</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>

<script>
// Wishlist toggle (kept as XMLHttpRequest per assignment spec)
document.querySelectorAll('.wishlist-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var pid = this.getAttribute('data-product');
        var self = this;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo BASE_URL; ?>../ajax/wishlist_toggle.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        if (data.in_wishlist) {
                            self.classList.add('is-active');
                            self.querySelector('svg').setAttribute('fill', 'currentColor');
                            if (window.showToast) showToast('Added to wishlist', 'success');
                        } else {
                            self.classList.remove('is-active');
                            self.querySelector('svg').setAttribute('fill', 'none');
                            if (window.showToast) showToast('Removed from wishlist', 'info');
                        }
                    }
                } catch (e) {}
            }
        };
        xhr.send('product_id=' + pid);
    });
});
</script>
<?php include APP . '/views/layouts/footer.php'; ?>
