<?php $page_title = 'My Wishlist'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div class="page-header">
    <h1>My Wishlist</h1>
</div>

<?php if (!empty($wishlist)): ?>

<p class="text-muted" style="margin-bottom:0.85rem; font-size:0.9rem;">
    <?php echo count($wishlist); ?> item<?php echo count($wishlist) !== 1 ? 's' : ''; ?> saved
</p>

<div class="wishlist-grid">
    <?php foreach ($wishlist as $w): ?>
    <div class="product-card" id="wish-<?php echo (int)$w['product_id']; ?>">
        <?php if (!empty($w['primary_image_path'])): ?>
            <img
                src="<?php echo UPLOAD_URL . 'product_images/' . sanitize($w['primary_image_path']); ?>"
                alt="<?php echo sanitize($w['name']); ?>"
                class="product-card-image"
            >
        <?php else: ?>
            <div class="product-card-image-placeholder">&#128722;</div>
        <?php endif; ?>

        <div class="product-card-body">
            <div class="product-card-title"><?php echo sanitize($w['name']); ?></div>
            <div class="product-card-price">৳<?php echo number_format((float)$w['price'], 2); ?></div>
            <div class="product-card-seller">&#127978; <?php echo sanitize($w['shop_name'] ?? 'N/A'); ?></div>

            <?php if (isset($w['avg_rating'])): ?>
            <div style="margin-bottom:0.5rem;">
                <span class="star-rating">
                    <?php
                    $rv = round((float)$w['avg_rating']);
                    for ($s = 0; $s < $rv; $s++) { echo '&#9733;'; }
                    for ($s = $rv; $s < 5; $s++) { echo '<span class="empty">&#9733;</span>'; }
                    ?>
                </span>
            </div>
            <?php endif; ?>

            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:auto; padding-top:0.75rem; border-top:1px solid #f0f0f0;">
                <a href="<?php echo BASE_URL; ?>?c=customer&a=product&id=<?php echo (int)$w['product_id']; ?>"
                   class="btn btn-secondary btn-small">View</a>

                <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=cart" style="display:inline;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo (int)$w['product_id']; ?>">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn btn-primary btn-small"
                        <?php echo ((int)($w['stock_qty'] ?? 1) <= 0) ? 'disabled' : ''; ?>>
                        Add to Cart
                    </button>
                </form>

                <button
                    class="btn btn-danger btn-small"
                    onclick="removeFromWishlist(<?php echo (int)$w['product_id']; ?>, this)"
                >Remove</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php else: ?>

<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon">&#9825;</div>
        <p>Your wishlist is empty.</p>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-primary" style="margin-top:1rem;">Browse Products</a>
    </div>
</div>

<?php endif; ?>

<script>
function removeFromWishlist(productId, btn) {
    if (!confirm('Remove this item from your wishlist?')) { return; }

    var card = document.getElementById('wish-' + productId);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL; ?>../ajax/wishlist_toggle.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success && !resp.in_wishlist) {
                    if (card) {
                        card.style.transition = 'opacity 0.3s';
                        card.style.opacity = '0';
                        setTimeout(function() {
                            if (card.parentNode) {
                                card.parentNode.removeChild(card);
                            }
                        }, 300);
                    }
                } else if (resp.redirect) {
                    window.location.href = resp.redirect;
                }
            } catch (e) { /* ignore */ }
        }
    };
    xhr.send('product_id=' + productId);
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
