<?php $page_title = 'Shopping Cart'; ?>
<?php include APP . '/views/layouts/header.php'; ?>

<div class="page-header">
    <h1>Shopping Cart</h1>
</div>

<?php if (!empty($cart_items)): ?>

<div class="cart-layout">

    <!-- Cart Items -->
    <div class="cart-items-section">
        <div class="card" style="padding:0; overflow:hidden;">
            <table class="table cart-table">
                <thead>
                    <tr>
                        <th style="width:70px;">Image</th>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr id="cart-row-<?php echo (int)$item['product_id']; ?>">
                        <td>
                            <?php if (!empty($item['primary_image_path'])): ?>
                            <img
                                src="<?php echo UPLOAD_URL . 'product_images/' . sanitize($item['primary_image_path']); ?>"
                                alt="<?php echo sanitize($item['name']); ?>"
                                class="product-thumb"
                            >
                            <?php else: ?>
                            <div style="width:60px; height:60px; background:#eee; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#aaa; font-size:1.5rem;">&#128722;</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>?c=customer&a=product&id=<?php echo (int)$item['product_id']; ?>"
                               style="font-weight:600; color:#222;">
                                <?php echo sanitize($item['name']); ?>
                            </a>
                            <div class="text-muted" style="font-size:0.8rem;"><?php echo sanitize($item['shop_name'] ?? ''); ?></div>
                        </td>
                        <td>৳<?php echo number_format((float)$item['price'], 2); ?></td>
                        <td>
                            <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=cart" style="display:flex; gap:4px; align-items:center;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
                                <input type="number" name="qty" value="<?php echo (int)$item['qty']; ?>"
                                       min="1" step="1" required
                                       class="qty-input"
                                       id="qty-<?php echo (int)$item['product_id']; ?>"
                                       oninput="if(this.value < 1 || this.value === '') this.value = 1;">
                                <button type="submit" class="btn btn-secondary btn-small">Update</button>
                            </form>
                        </td>
                        <td id="subtotal-<?php echo (int)$item['product_id']; ?>">
                            ৳<?php echo number_format((float)$item['subtotal'], 2); ?>
                        </td>
                        <td>
                            <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=cart">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-small"
                                        onclick="return confirm('Remove this item?')">&#10005;</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right; font-weight:700; padding:0.75rem 1rem;">Subtotal:</td>
                        <td colspan="2" style="font-weight:700; padding:0.75rem 1rem;" id="cartSubtotal">
                            ৳<?php echo number_format((float)$subtotal, 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="margin-top:0.75rem;">
            <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-secondary">&larr; Continue Shopping</a>
        </div>
    </div>

    <!-- Cart Summary -->
    <div class="cart-summary card">
        <h3 style="margin-bottom:1rem; font-size:1.05rem;">Order Summary</h3>

        <div class="summary-row">
            <span>Subtotal</span>
            <span>৳<?php echo number_format((float)$subtotal, 2); ?></span>
        </div>
        <div class="summary-row">
            <span>Delivery Fee</span>
            <span>Calculated at checkout</span>
        </div>
        <div class="summary-row summary-total">
            <span>Estimated Total</span>
            <span>৳<?php echo number_format((float)$subtotal, 2); ?></span>
        </div>

        <div style="margin-top:1.25rem;">
            <a href="<?php echo BASE_URL; ?>?c=customer&a=checkout" class="btn btn-primary w-100">
                Proceed to Checkout &rarr;
            </a>
        </div>
        <div style="margin-top:0.5rem;">
            <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=cart"
                  onsubmit="return confirm('Clear the entire cart?')">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-danger w-100">Clear Cart</button>
            </form>
        </div>
    </div>

</div><!-- /.cart-layout -->

<?php else: ?>

<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon">&#128722;</div>
        <p>Your cart is empty.</p>
        <a href="<?php echo BASE_URL; ?>?c=customer&a=products" class="btn btn-primary" style="margin-top:1rem;">Start Shopping</a>
    </div>
</div>

<?php endif; ?>

<?php include APP . '/views/layouts/footer.php'; ?>
