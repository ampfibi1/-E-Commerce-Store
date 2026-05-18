<?php
$page_title = isset($product['name']) ? sanitize($product['name']) : 'Product';
include APP . '/views/layouts/header.php';
?>
<div class="container" style="max-width:900px;margin:30px auto;">

<div style="display:flex;gap:30px;flex-wrap:wrap;margin-bottom:30px;">
    <!-- Main image -->
    <div style="flex:0 0 340px;">
        <?php if ($product['primary_image_path']): ?>
            <img src="<?php echo UPLOAD_URL . 'product_images/' . sanitize($product['primary_image_path']); ?>"
                 alt="<?php echo sanitize($product['name']); ?>"
                 style="width:100%;border-radius:6px;" id="mainProductImg">
        <?php endif; ?>
        <?php if (!empty($images)): ?>
            <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;">
            <?php foreach ($images as $img): ?>
                <img src="<?php echo UPLOAD_URL . 'product_images/' . sanitize($img['image_path']); ?>"
                     style="width:70px;height:70px;object-fit:cover;cursor:pointer;border-radius:4px;border:2px solid transparent;"
                     onclick="document.getElementById('mainProductImg').src=this.src;">
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product info -->
    <div style="flex:1;min-width:240px;">
        <h1 style="margin-top:0;"><?php echo sanitize($product['name']); ?></h1>
        <div style="color:#888;margin-bottom:8px;"><?php echo sanitize($product['category_name']); ?> &bull; <?php echo sanitize($product['shop_name']); ?></div>
        <div style="font-size:1.5em;font-weight:bold;margin-bottom:12px;">৳<?php echo number_format((float)$product['price'], 2); ?></div>
        <div style="margin-bottom:12px;">Rating: <strong><?php echo number_format((float)$avg_rating, 1); ?></strong>/5 (<?php echo count($reviews); ?> reviews)</div>
        <div style="margin-bottom:12px;">Stock: <?php echo (int)$product['stock_qty']; ?> available</div>
        <p><?php echo nl2br(sanitize($product['description'])); ?></p>

        <?php if (isset($_SESSION['uid']) && $_SESSION['role'] === 'customer'): ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
                <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=cart" style="display:flex;gap:6px;align-items:center;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                    <input type="number" name="qty" value="1" min="1" step="1" max="<?php echo (int)$product['stock_qty']; ?>" required class="form-control" style="width:70px;" oninput="if(this.value < 1 || this.value === '') this.value = 1;">
                    <button type="submit" class="btn btn-primary" <?php echo ((int)$product['stock_qty'] <= 0 || !(int)$product['is_available']) ? 'disabled' : ''; ?>>Add to Cart</button>
                </form>
                <button id="wishlistBtn"
                        class="btn <?php echo $in_wishlist ? 'btn-danger' : 'btn-secondary'; ?>"
                        data-product="<?php echo (int)$product['id']; ?>">
                    <?php echo $in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reviews -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><h3 class="card-title">Customer Reviews</h3></div>

    <?php if ($can_review): ?>
    <div style="margin-bottom:20px;">
        <h4>Write a Review</h4>
        <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=product&id=<?php echo (int)$product['id']; ?>" novalidate>
            <input type="hidden" name="action" value="submit_review">
            <div class="form-group">
                <label>Rating</label>
                <select name="rating" class="form-control" style="width:120px;">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Review</label>
                <textarea name="review_text" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if (empty($reviews)): ?>
        <p>No reviews yet. Be the first!</p>
    <?php else: ?>
        <?php foreach ($reviews as $rev): ?>
        <div style="border-bottom:1px solid #eee;padding:12px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <strong><?php echo sanitize($rev['customer_name']); ?></strong>
                <div style="display:flex;gap:8px;align-items:center;">
                    <span><?php echo (int)$rev['rating']; ?>/5</span>
                    <?php if (isset($_SESSION['uid']) && (int)$rev['customer_id'] === (int)$_SESSION['uid']): ?>
                        <button class="btn btn-danger btn-small delete-review-btn"
                                data-review="<?php echo (int)$rev['id']; ?>">Delete</button>
                    <?php endif; ?>
                </div>
            </div>
            <p style="margin:6px 0;"><?php echo nl2br(sanitize($rev['review_text'])); ?></p>
            <?php if ($rev['seller_reply']): ?>
                <div style="background:#f0f4ff;padding:8px;border-left:3px solid #2c7be5;margin-top:6px;font-size:0.9em;">
                    <strong>Seller Reply:</strong> <?php echo nl2br(sanitize($rev['seller_reply'])); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>

<script>
<?php if (isset($_SESSION['uid']) && $_SESSION['role'] === 'customer'): ?>
var wishlistBtn = document.getElementById('wishlistBtn');
if (wishlistBtn) {
    wishlistBtn.addEventListener('click', function() {
        var pid = this.getAttribute('data-product');
        var self = this;
        var xhr1 = new XMLHttpRequest();
        xhr1.open('POST', '<?php echo BASE_URL; ?>../ajax/wishlist_toggle.php', true);
        xhr1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr1.onreadystatechange = function() {
            if (xhr1.readyState === 4 && xhr1.status === 200) {
                var data = JSON.parse(xhr1.responseText);
                if (data.success) {
                    if (data.in_wishlist) {
                        self.textContent = 'Remove from Wishlist';
                        self.classList.remove('btn-secondary');
                        self.classList.add('btn-danger');
                    } else {
                        self.textContent = 'Add to Wishlist';
                        self.classList.remove('btn-danger');
                        self.classList.add('btn-secondary');
                    }
                }
            }
        };
        xhr1.send('product_id=' + pid);
    });
}
<?php endif; ?>

document.querySelectorAll('.delete-review-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('Delete this review?')) return;
        var rid = this.getAttribute('data-review');
        var row = this.closest('div[style]');
        var xhr2 = new XMLHttpRequest();
        xhr2.open('POST', '<?php echo BASE_URL; ?>../ajax/review_delete.php', true);
        xhr2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr2.onreadystatechange = function() {
            if (xhr2.readyState === 4 && xhr2.status === 200) {
                var data = JSON.parse(xhr2.responseText);
                if (data.success) {
                    row.remove();
                } else {
                    alert(data.message);
                }
            }
        };
        xhr2.send('review_id=' + rid);
    });
});
</script>
<?php include APP . '/views/layouts/footer.php'; ?>
