<?php $page_title = isset($product['name']) ? $product['name'] : 'Product Detail'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div style="margin-bottom:0.75rem;">
    <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="text-muted" style="font-size:0.88rem;">
        &larr; Back to Products
    </a>
</div>

<!-- Product Detail Layout -->
<div class="product-detail-layout card" style="padding:1.5rem;">

    <!-- Images Section -->
    <div class="product-images">
        <div class="carousel-container" id="imageCarousel">
            <?php
            $main_img = !empty($product['primary_image']) ? $product['primary_image'] : '';
            $all_images = [];
            if (!empty($images)) {
                $all_images = $images;
            } elseif ($main_img) {
                $all_images = [['image_path' => $main_img]];
            }
            $first_img = !empty($all_images) ? $all_images[0]['image_path'] : '';
            ?>
            <?php if ($first_img): ?>
                <img
                    src="<?php echo UPLOAD_URL . 'product_images/' . sanitize($first_img); ?>"
                    alt="<?php echo sanitize($product['name']); ?>"
                    class="product-main-image"
                    id="mainProductImage"
                >
                <?php if (count($all_images) > 1): ?>
                <button class="carousel-btn carousel-btn-prev" onclick="prevImage()" type="button">&#8249;</button>
                <button class="carousel-btn carousel-btn-next" onclick="nextImage()" type="button">&#8250;</button>
                <?php endif; ?>
            <?php else: ?>
                <div class="product-card-image-placeholder" style="height:320px; border-radius:8px;">&#128722;</div>
            <?php endif; ?>
        </div>

        <?php if (count($all_images) > 1): ?>
        <div class="image-thumbnails" style="margin-top:0.75rem;">
            <?php foreach ($all_images as $idx => $img): ?>
            <img
                src="<?php echo UPLOAD_URL . 'product_images/' . sanitize($img['image_path']); ?>"
                alt="Thumbnail <?php echo $idx + 1; ?>"
                class="image-thumb <?php echo $idx === 0 ? 'active' : ''; ?>"
                onclick="setImage(<?php echo $idx; ?>, this)"
            >
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div class="product-info">
        <h1 style="font-size:1.5rem; margin-bottom:0.5rem;"><?php echo sanitize($product['name']); ?></h1>

        <div style="margin-bottom:0.5rem;">
            <?php
            $avg_r = isset($product['avg_rating']) ? (float)$product['avg_rating'] : 0;
            $rc = isset($product['review_count']) ? (int)$product['review_count'] : 0;
            ?>
            <span class="star-rating">
                <?php
                $full = floor($avg_r);
                $empty = 5 - $full;
                for ($s = 0; $s < $full; $s++) { echo '&#9733;'; }
                for ($s = 0; $s < $empty; $s++) { echo '<span class="empty">&#9733;</span>'; }
                ?>
            </span>
            <span class="star-count"><?php echo number_format($avg_r, 1); ?> (<?php echo $rc; ?> review<?php echo $rc !== 1 ? 's' : ''; ?>)</span>
        </div>

        <div class="product-price-large">৳<?php echo number_format((float)$product['price'], 2); ?></div>

        <div class="product-meta">
            <strong>Seller:</strong> <?php echo sanitize($product['shop_name'] ?? 'N/A'); ?>
        </div>
        <div class="product-meta">
            <strong>Category:</strong> <?php echo sanitize($product['category_name'] ?? 'N/A'); ?>
        </div>
        <div class="stock-info">
            <?php if ((int)$product['stock_qty'] > 0): ?>
                <span class="in-stock">&#10003; In Stock (<?php echo (int)$product['stock_qty']; ?> available)</span>
            <?php else: ?>
                <span class="out-of-stock">&#10007; Out of Stock</span>
            <?php endif; ?>
        </div>

        <div style="margin-bottom:1rem;">
            <p style="font-size:0.93rem; color:#555; line-height:1.7;">
                <?php echo nl2br(sanitize($product['description'])); ?>
            </p>
        </div>

        <!-- Add to Cart -->
        <?php if ((int)$product['stock_qty'] > 0): ?>
        <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=cart" style="display:flex; gap:0.75rem; align-items:center; margin-bottom:1rem;">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
            <div class="form-group" style="margin:0; display:flex; align-items:center; gap:0.5rem;">
                <label class="form-label" style="margin:0; white-space:nowrap;">Qty:</label>
                <input
                    type="number"
                    name="qty"
                    value="1"
                    min="1"
                    step="1"
                    max="<?php echo (int)$product['stock_qty']; ?>"
                    required
                    class="qty-input"
                    id="detailQty"
                    oninput="if(this.value < 1 || this.value === '') this.value = 1;"
                >
            </div>
            <button type="submit" class="btn btn-primary">Add to Cart</button>
        </form>
        <?php else: ?>
        <div class="alert alert-danger" style="display:inline-block;">This product is currently out of stock.</div>
        <?php endif; ?>

        <!-- Wishlist Button -->
        <?php if (isset($_SESSION['uid'])): ?>
        <button
            class="btn btn-outline"
            id="wishlistBtn"
            onclick="toggleWishlistDetail(<?php echo (int)$product['product_id']; ?>, this)"
        >
            <?php echo $in_wishlist ? '&#9829; Remove from Wishlist' : '&#9825; Add to Wishlist'; ?>
        </button>
        <?php else: ?>
        <a href="<?php echo BASE_URL; ?>?c=auth&a=login" class="btn btn-outline">&#9825; Add to Wishlist</a>
        <?php endif; ?>
    </div>
</div>

<!-- Reviews Section -->
<div class="reviews-section">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Customer Reviews
                <span class="text-muted" style="font-size:0.88rem; font-weight:400;">
                    (<?php echo $rc; ?> review<?php echo $rc !== 1 ? 's' : ''; ?>)
                </span>
            </h3>
        </div>

        <!-- Write a Review -->
        <?php if (isset($_SESSION['uid']) && $_SESSION['role'] === 'customer'): ?>
            <?php if ($can_review && !$already_reviewed): ?>
            <div style="margin-bottom:1.5rem; padding-bottom:1.5rem; border-bottom:1px solid #eee;">
                <h4 style="font-size:1rem; margin-bottom:0.85rem;">Write a Review</h4>
                <form id="reviewForm"
                      method="POST"
                      action="<?php echo BASE_URL . '?c=customer&a=product&id=' . (int)$product['product_id'] . '&post_review=1'; ?>"
                      novalidate
                      onsubmit="return validateReviewForm()">

                    <div class="form-group">
                        <label class="form-label" for="rating">Rating</label>
                        <select id="rating" name="rating" class="form-control" style="max-width:200px;">
                            <option value="">-- Select rating --</option>
                            <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; Excellent</option>
                            <option value="4">&#9733;&#9733;&#9733;&#9733; Good</option>
                            <option value="3">&#9733;&#9733;&#9733; Average</option>
                            <option value="2">&#9733;&#9733; Poor</option>
                            <option value="1">&#9733; Terrible</option>
                        </select>
                        <span class="err" id="err_rating"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="review_text">Your Review</label>
                        <textarea
                            id="review_text"
                            name="review_text"
                            class="form-control"
                            rows="4"
                            placeholder="Share your experience with this product..."
                        ></textarea>
                        <span class="err" id="err_review_text"></span>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
            <?php elseif ($already_reviewed && !empty($user_review)): ?>
            <div style="margin-bottom:1.5rem; padding-bottom:1.5rem; border-bottom:1px solid #eee;">
                <h4 style="font-size:1rem; margin-bottom:0.85rem;">Your Review</h4>
                <form id="reviewForm"
                      method="POST"
                      action="<?php echo BASE_URL . '?c=customer&a=product&id=' . (int)$product['product_id'] . '&post_review=1'; ?>"
                      novalidate
                      onsubmit="return validateReviewForm()">
                    <input type="hidden" name="review_id" value="<?php echo (int)$user_review['review_id']; ?>">

                    <div class="form-group">
                        <label class="form-label" for="rating">Rating</label>
                        <select id="rating" name="rating" class="form-control" style="max-width:200px;">
                            <option value="">-- Select rating --</option>
                            <?php for ($rv = 5; $rv >= 1; $rv--): ?>
                            <option value="<?php echo $rv; ?>" <?php echo ((int)$user_review['rating'] === $rv) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('★', $rv); ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                        <span class="err" id="err_rating"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="review_text">Your Review</label>
                        <textarea id="review_text" name="review_text" class="form-control" rows="3">
                            <?php echo sanitize($user_review['review_text']); ?>
                        </textarea>
                        <span class="err" id="err_review_text"></span>
                    </div>

                    <div style="display:flex; gap:0.5rem;">
                        <button type="submit" class="btn btn-primary">Update Review</button>
                        <button
                            type="button"
                            class="btn btn-danger"
                            onclick="deleteReview(<?php echo (int)$user_review['review_id']; ?>)"
                        >Delete Review</button>
                    </div>
                </form>
            </div>
            <?php elseif (!$can_review): ?>
            <div class="alert alert-info" style="margin-bottom:1rem;">
                You can only review products you have purchased and received.
            </div>
            <?php endif; ?>
        <?php elseif (!isset($_SESSION['uid'])): ?>
        <div class="alert alert-info" style="margin-bottom:1rem;">
            <a href="<?php echo BASE_URL; ?>?c=auth&a=login">Log in</a> to write a review.
        </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <div>
                        <span class="reviewer-name"><?php echo sanitize($review['reviewer_name']); ?></span>
                        <span class="star-rating" style="font-size:0.88rem; margin-left:0.5rem;">
                            <?php
                            $rv_full = (int)$review['rating'];
                            for ($s = 0; $s < $rv_full; $s++) { echo '&#9733;'; }
                            for ($s = $rv_full; $s < 5; $s++) { echo '<span class="empty">&#9733;</span>'; }
                            ?>
                        </span>
                    </div>
                    <span class="review-date"><?php echo sanitize(date('d M Y', strtotime($review['created_at']))); ?></span>
                </div>
                <div class="review-text"><?php echo nl2br(sanitize($review['review_text'])); ?></div>
                <?php if (!empty($review['seller_reply'])): ?>
                <div class="seller-reply">
                    <div class="seller-reply-label">Seller Reply:</div>
                    <?php echo nl2br(sanitize($review['seller_reply'])); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state" style="padding:2rem;">
            <p class="text-muted">No reviews yet. Be the first to review this product!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
/* Image carousel */
var carouselImages = [
    <?php foreach ($all_images as $img): ?>
    '<?php echo UPLOAD_URL . 'product_images/' . sanitize($img['image_path']); ?>',
    <?php endforeach; ?>
];
var currentImgIndex = 0;

function setImage(idx, thumbEl) {
    currentImgIndex = idx;
    var mainImg = document.getElementById('mainProductImage');
    if (mainImg && carouselImages[idx]) {
        mainImg.src = carouselImages[idx];
    }
    var thumbs = document.querySelectorAll('.image-thumb');
    for (var t = 0; t < thumbs.length; t++) {
        thumbs[t].classList.remove('active');
    }
    if (thumbEl) {
        thumbEl.classList.add('active');
    }
}

function prevImage() {
    var newIdx = currentImgIndex - 1;
    if (newIdx < 0) { newIdx = carouselImages.length - 1; }
    var thumbs = document.querySelectorAll('.image-thumb');
    setImage(newIdx, thumbs[newIdx] || null);
}

function nextImage() {
    var newIdx = currentImgIndex + 1;
    if (newIdx >= carouselImages.length) { newIdx = 0; }
    var thumbs = document.querySelectorAll('.image-thumb');
    setImage(newIdx, thumbs[newIdx] || null);
}

/* Wishlist toggle */
function toggleWishlistDetail(productId, btn) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL; ?>ajax/wishlist_toggle.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    if (resp.in_wishlist) {
                        btn.innerHTML = '&#9829; Remove from Wishlist';
                    } else {
                        btn.innerHTML = '&#9825; Add to Wishlist';
                    }
                } else if (resp.redirect) {
                    window.location.href = resp.redirect;
                }
            } catch (e) { /* ignore */ }
        }
    };
    xhr.send('product_id=' + productId);
}

/* Delete review via AJAX */
function deleteReview(reviewId) {
    if (!confirm('Are you sure you want to delete your review?')) { return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL; ?>ajax/review_delete.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    window.location.reload();
                } else {
                    alert(resp.message || 'Could not delete review.');
                }
            } catch (e) { /* ignore */ }
        }
    };
    xhr.send('review_id=' + reviewId);
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
