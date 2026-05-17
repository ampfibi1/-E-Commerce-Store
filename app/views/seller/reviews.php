<?php
$page_title = 'Customer Reviews';
include APP . '/views/layouts/header.php';
?>

<div class="seller-reviews">
    <h1>Customer Reviews</h1>

    <?php if (!empty($reviews)): ?>
    <div class="reviews-list">
        <?php foreach ($reviews as $review): ?>
        <div class="review-card" id="review-card-<?php echo (int)$review['id']; ?>">
            <div class="review-meta">
                <span class="review-product"><strong><?php echo sanitize($review['product_name']); ?></strong></span>
                <span class="review-customer">by <?php echo sanitize($review['customer_name']); ?></span>
                <span class="review-date"><?php echo sanitize($review['created_at']); ?></span>
            </div>

            <div class="review-rating">
                <?php
                $rating = (int)$review['rating'];
                for ($s = 1; $s <= 5; $s++) {
                    echo '<span class="star ' . ($s <= $rating ? 'star-filled' : 'star-empty') . '">&#9733;</span>';
                }
                ?>
                <span class="rating-num">(<?php echo $rating; ?>/5)</span>
            </div>

            <div class="review-text">
                <p><?php echo sanitize($review['review_text']); ?></p>
            </div>

            <!-- Existing Reply -->
            <?php if (!empty($review['seller_reply'])): ?>
            <div class="seller-reply-box" id="reply-display-<?php echo (int)$review['id']; ?>">
                <strong>Your Reply:</strong>
                <blockquote><?php echo sanitize($review['seller_reply']); ?></blockquote>
            </div>
            <?php else: ?>
            <!-- Reply Form (shown when no reply exists) -->
            <div class="reply-form-wrap" id="reply-form-<?php echo (int)$review['id']; ?>">
                <label for="reply-text-<?php echo (int)$review['id']; ?>">Reply to this review:</label>
                <textarea id="reply-text-<?php echo (int)$review['id']; ?>"
                          class="form-control"
                          rows="3"
                          placeholder="Write your reply..."></textarea>
                <div class="err" id="err-reply-<?php echo (int)$review['id']; ?>"></div>
                <button type="button" class="btn btn-primary btn-sm"
                        onclick="submitReply(<?php echo (int)$review['id']; ?>)">
                    Submit Reply
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <p>No reviews yet for your products.</p>
    </div>
    <?php endif; ?>
</div>

<script>
function submitReply(reviewId) {
    var textarea = document.getElementById('reply-text-' + reviewId);
    var errSpan  = document.getElementById('err-reply-' + reviewId);
    var replyText = textarea.value;

    errSpan.innerHTML = '';

    if (replyText.trim() === '') {
        errSpan.innerHTML = 'Reply cannot be empty.';
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL; ?>../ajax/seller_reply.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var response;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    errSpan.innerHTML = 'Unexpected server response.';
                    return;
                }

                if (response.success) {
                    // Hide the form
                    var formWrap = document.getElementById('reply-form-' + reviewId);
                    if (formWrap) {
                        formWrap.style.display = 'none';
                    }
                    // Show the reply
                    var card = document.getElementById('review-card-' + reviewId);
                    var replyBox = document.createElement('div');
                    replyBox.className = 'seller-reply-box';
                    replyBox.id = 'reply-display-' + reviewId;
                    replyBox.innerHTML = '<strong>Your Reply:</strong><blockquote>' + response.reply + '</blockquote>';
                    card.appendChild(replyBox);
                } else {
                    errSpan.innerHTML = response.message ? response.message : 'Could not save reply.';
                }
            } else {
                errSpan.innerHTML = 'Server error. Please try again.';
            }
        }
    };
    xhr.send('review_id=' + reviewId + '&reply=' + encodeURIComponent(replyText) + '&sid=<?php echo (int)$_SESSION['sid']; ?>');
}
</script>

<?php include APP . '/views/layouts/footer.php'; ?>
