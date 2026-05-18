<?php
// ============================================================
// app/controllers/Customer_controller/CustomerController.php
// Procedural controller — no classes (per CONTRIBUTING.md rule).
// Every action is a function named "customer_<action>" so the
// router dispatches ?c=customer&a=cart → customer_cart($conn).
// $conn is passed in by the router; never via $this.
// ============================================================

// ----------------------------------------------------------------
// Auth gate. Called as the first line of every customer_* function
// that requires the customer to be logged in. Anonymous-browse
// actions (products, product) intentionally skip this gate.
// ----------------------------------------------------------------
function customer_require_auth() {
    require_role('customer');
}

// ----------------------------------------------------------------
// dashboard()
// ----------------------------------------------------------------
function customer_dashboard($conn) {
    customer_require_auth();

    $uid            = (int)$_SESSION['uid'];
    $user           = user_get_by_id($conn, $uid);
    $all_orders     = order_get_by_customer($conn, $uid);
    $recent_orders  = array_slice($all_orders, 0, 5);
    $wishlist_items = wishlist_get($conn, $uid);
    $wishlist_count = count($wishlist_items);

    include APP . '/views/customer/dashboard.php';
}

// ----------------------------------------------------------------
// profile()
// ----------------------------------------------------------------
function customer_profile($conn) {
    customer_require_auth();

    $uid    = (int)$_SESSION['uid'];
    $errors = array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        // ----------------------------------------------------------
        if ($action === 'update_info') {
            $name  = isset($_POST['name'])  ? trim($_POST['name'])  : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

            if ($name === '') {
                $errors['name'] = 'Full name is required.';
            }
            if ($email === '') {
                $errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Enter a valid email address.';
            }
            if ($phone === '') {
                $errors['phone'] = 'Phone number is required.';
            } elseif (!preg_match('/^\d{11}$/', $phone)) {
                $errors['phone'] = 'Phone number must be exactly 11 digits.';
            }

            // Ensure email is not taken by another user
            if (empty($errors['email'])) {
                $existing = user_get_by_email($conn, $email);
                if ($existing && (int)$existing['id'] !== $uid) {
                    $errors['email'] = 'This email address is already in use.';
                }
            }

            if (empty($errors)) {
                user_update($conn, $uid, array(
                    'name'  => $name,
                    'email' => $email,
                    'phone' => $phone,
                ));
                $_SESSION['uname'] = $name;
                set_flash('success', 'Profile updated successfully.');
                redirect(BASE_URL . '?c=customer&a=profile');
            }

        // ----------------------------------------------------------
        } elseif ($action === 'change_password') {
            $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
            $new_password     = isset($_POST['new_password'])     ? $_POST['new_password']     : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

            $user = user_get_by_id($conn, $uid);

            if ($current_password === '') {
                $errors['current_password'] = 'Current password is required.';
            } elseif (!password_verify($current_password, $user['password_hash'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            }

            if ($new_password === '') {
                $errors['new_password'] = 'New password is required.';
            } elseif (strlen($new_password) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters.';
            }

            if ($confirm_password === '') {
                $errors['confirm_password'] = 'Please confirm your new password.';
            } elseif ($new_password !== $confirm_password) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }

            if (empty($errors)) {
                user_update_password($conn, $uid, password_hash($new_password, PASSWORD_BCRYPT));
                set_flash('success', 'Password changed successfully.');
                redirect(BASE_URL . '?c=customer&a=profile');
            }

        // ----------------------------------------------------------
        } elseif ($action === 'upload_pic') {
            if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors['profile_pic'] = 'Please select an image file.';
            } else {
                $file      = $_FILES['profile_pic'];
                $allowed   = array('image/jpeg', 'image/png');
                $max_size  = 2 * 1024 * 1024;

                $finfo     = finfo_open(FILEINFO_MIME_TYPE);
                $mime      = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors['profile_pic'] = 'File upload error.';
                } elseif (!in_array($mime, $allowed)) {
                    $errors['profile_pic'] = 'Only JPG and PNG images are allowed.';
                } elseif ($file['size'] > $max_size) {
                    $errors['profile_pic'] = 'Image must not exceed 2 MB.';
                } else {
                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'pic_' . $uid . '_' . uniqid() . '.' . strtolower($ext);
                    $dest_dir = UPLOAD_PATH . 'profile_pics/';
                    $dest     = $dest_dir . $filename;

                    if (!is_dir($dest_dir)) {
                        mkdir($dest_dir, 0755, true);
                    }

                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        $errors['profile_pic'] = 'Could not save the uploaded file.';
                    } else {
                        user_update_pic($conn, $uid, 'profile_pics/' . $filename);
                        set_flash('success', 'Profile picture updated.');
                        redirect(BASE_URL . '?c=customer&a=profile');
                    }
                }
            }
        }
    }

    $user = user_get_by_id($conn, $uid);
    include APP . '/views/customer/profile.php';
}

// ----------------------------------------------------------------
// addresses()
// ----------------------------------------------------------------
function customer_addresses($conn) {
    customer_require_auth();

    $uid    = (int)$_SESSION['uid'];
    $errors = array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action === 'add') {
            $label        = isset($_POST['label'])        ? trim($_POST['label'])        : '';
            $address_line = isset($_POST['address_line']) ? trim($_POST['address_line']) : '';
            $city         = isset($_POST['city'])         ? trim($_POST['city'])         : '';
            $zip          = isset($_POST['zip'])          ? trim($_POST['zip'])          : '';

            if ($label === '') {
                $errors['label'] = 'Label is required (e.g. Home, Office).';
            }
            if ($address_line === '') {
                $errors['address_line'] = 'Address line is required.';
            }
            if ($city === '') {
                $errors['city'] = 'City is required.';
            }
            if ($zip === '') {
                $errors['zip'] = 'Zip/postal code is required.';
            }

            if (empty($errors)) {
                address_create($conn, array(
                    'customer_id'  => $uid,
                    'label'        => $label,
                    'address_line' => $address_line,
                    'city'         => $city,
                    'zip'          => $zip,
                ));
                set_flash('success', 'Address added.');
                redirect(BASE_URL . '?c=customer&a=addresses');
            }

        } elseif ($action === 'edit') {
            $id           = isset($_POST['id'])           ? (int)$_POST['id']            : 0;
            $label        = isset($_POST['label'])        ? trim($_POST['label'])        : '';
            $address_line = isset($_POST['address_line']) ? trim($_POST['address_line']) : '';
            $city         = isset($_POST['city'])         ? trim($_POST['city'])         : '';
            $zip          = isset($_POST['zip'])          ? trim($_POST['zip'])          : '';

            if ($id <= 0) {
                $errors['id'] = 'Invalid address.';
            }
            if ($label === '') {
                $errors['label'] = 'Label is required.';
            }
            if ($address_line === '') {
                $errors['address_line'] = 'Address line is required.';
            }
            if ($city === '') {
                $errors['city'] = 'City is required.';
            }
            if ($zip === '') {
                $errors['zip'] = 'Zip/postal code is required.';
            }

            if (empty($errors)) {
                address_update($conn, $id, $uid, array(
                    'label'        => $label,
                    'address_line' => $address_line,
                    'city'         => $city,
                    'zip'          => $zip,
                ));
                set_flash('success', 'Address updated.');
                redirect(BASE_URL . '?c=customer&a=addresses');
            }

        } elseif ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id > 0) {
                address_delete($conn, $id, $uid);
            }
            set_flash('success', 'Address removed.');
            redirect(BASE_URL . '?c=customer&a=addresses');

        } elseif ($action === 'set_default') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id > 0) {
                address_set_default($conn, $uid, $id);
            }
            set_flash('success', 'Default address updated.');
            redirect(BASE_URL . '?c=customer&a=addresses');
        }
    }

    $addresses = address_get_by_customer($conn, $uid);
    include APP . '/views/customer/addresses.php';
}

// ----------------------------------------------------------------
// products() – browsing; no auth required
// ----------------------------------------------------------------
function customer_products($conn) {
    $q     = isset($_GET['q'])     ? trim($_GET['q'])        : '';
    $cat   = isset($_GET['cat'])   ? (int)$_GET['cat']       : 0;
    $min_p = isset($_GET['min_p']) ? trim($_GET['min_p'])    : '';
    $max_p = isset($_GET['max_p']) ? trim($_GET['max_p'])    : '';
    $min_r = isset($_GET['min_r']) ? trim($_GET['min_r'])    : '';

    $filters = array('available_only' => 1);
    if ($q !== '') {
        $filters['keyword'] = $q;
    }
    if ($cat > 0) {
        $filters['category_id'] = $cat;
    }
    if ($min_p !== '') {
        $filters['min_price'] = (float)$min_p;
    }
    if ($max_p !== '') {
        $filters['max_price'] = (float)$max_p;
    }
    if ($min_r !== '') {
        $filters['min_rating'] = (float)$min_r;
    }

    $categories = category_get_all($conn);
    $products   = product_get_all($conn, $filters);

    $wishlist_ids = array();
    if (isset($_SESSION['uid']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
        $wl_items = wishlist_get($conn, (int)$_SESSION['uid']);
        foreach ($wl_items as $wl_item) {
            $wishlist_ids[] = (int)$wl_item['product_id'];
        }
    }

    include APP . '/views/customer/products.php';
}

// ----------------------------------------------------------------
// product() – single product page (anonymous-browse allowed)
// ----------------------------------------------------------------
function customer_product($conn) {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($product_id <= 0) {
        set_flash('error', 'Product not found.');
        redirect(BASE_URL . '?c=customer&a=products');
    }

    $product = product_get_by_id($conn, $product_id);
    if (!$product) {
        set_flash('error', 'Product not found.');
        redirect(BASE_URL . '?c=customer&a=products');
    }

    $images     = product_get_images($conn, $product_id);
    $reviews    = review_get_by_product($conn, $product_id);
    $avg_rating = product_get_avg_rating($conn, $product_id);

    $in_wishlist = false;
    $can_review  = false;

    if (isset($_SESSION['uid']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
        $uid         = (int)$_SESSION['uid'];
        $in_wishlist = wishlist_is_in($conn, $uid, $product_id);
        $can_review  = review_can_review($conn, $uid, $product_id);
    }

    include APP . '/views/customer/product.php';
}

// ----------------------------------------------------------------
// cart()
// ----------------------------------------------------------------
function customer_cart($conn) {
    customer_require_auth();

    $uid = (int)$_SESSION['uid'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action     = isset($_POST['action'])     ? $_POST['action']     : '';
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $qty        = isset($_POST['qty'])        ? (int)$_POST['qty']   : 1;
        if ($qty < 1) { $qty = 1; }

        if ($action === 'add' && $product_id > 0) {
            $product = product_get_by_id($conn, $product_id);
            if ($product && (int)$product['is_available'] && (int)$product['stock_qty'] > 0) {
                cart_add($product_id, $qty);
                set_flash('success', 'Item added to cart.');
            } else {
                set_flash('error', 'This product is not available.');
            }
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . '?c=customer&a=products';
            redirect($referer);

        } elseif ($action === 'update' && $product_id > 0) {
            cart_update($product_id, $qty);
            set_flash('success', 'Cart updated.');
            redirect(BASE_URL . '?c=customer&a=cart');

        } elseif ($action === 'remove' && $product_id > 0) {
            cart_remove($product_id);
            set_flash('success', 'Item removed from cart.');
            redirect(BASE_URL . '?c=customer&a=cart');
        }
    }

    $cart_items = cart_get($conn);
    $subtotal   = cart_subtotal($conn);

    include APP . '/views/customer/cart.php';
}

// ----------------------------------------------------------------
// checkout()
// ----------------------------------------------------------------
function customer_checkout($conn) {
    customer_require_auth();

    $uid    = (int)$_SESSION['uid'];
    $errors = array();

    $cart_items = cart_get($conn);
    if (empty($cart_items)) {
        set_flash('info', 'Your cart is empty.');
        redirect(BASE_URL . '?c=customer&a=cart');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $shipping_address_id = isset($_POST['shipping_address_id']) ? (int)$_POST['shipping_address_id']    : 0;
        $new_address         = isset($_POST['new_address'])         ? trim($_POST['new_address'])           : '';
        $zone_id             = isset($_POST['zone_id'])             ? (int)$_POST['zone_id']                : 0;
        $payment_method      = isset($_POST['payment_method'])      ? trim($_POST['payment_method'])        : '';
        $coupon_code         = isset($_POST['coupon_code'])         ? trim($_POST['coupon_code'])           : '';

        // Determine shipping address string
        $shipping_address_str = '';
        if ($shipping_address_id > 0) {
            $addr_row = address_get_by_id($conn, $shipping_address_id);
            if ($addr_row && (int)$addr_row['customer_id'] === $uid) {
                $shipping_address_str = $addr_row['label'] . ': ' . $addr_row['address_line'] . ', ' . $addr_row['city'] . ' ' . $addr_row['zip'];
            } else {
                $errors['shipping_address_id'] = 'Selected address not found.';
            }
        } elseif ($new_address !== '') {
            $shipping_address_str = $new_address;
        } else {
            $errors['shipping_address'] = 'Please select or enter a shipping address.';
        }

        if ($zone_id <= 0) {
            $errors['zone_id'] = 'Please select a delivery zone.';
        }

        $allowed_payment = array('cash_on_delivery', 'card');
        if ($payment_method === '') {
            $errors['payment_method'] = 'Please select a payment method.';
        } elseif (!in_array($payment_method, $allowed_payment)) {
            $errors['payment_method'] = 'Invalid payment method.';
        }

        // Coupon validation
        $coupon_id       = null;
        $discount_amount = 0.00;
        $subtotal        = cart_subtotal($conn);

        if ($coupon_code !== '' && empty($errors)) {
            // Validate against first cart item's seller
            $first_seller_id = (int)$cart_items[0]['seller_id'];
            $coupon_result   = coupon_validate($conn, $coupon_code, $first_seller_id, $subtotal);

            if ($coupon_result && isset($coupon_result['valid']) && $coupon_result['valid']) {
                $coupon_id       = $coupon_result['coupon_id'];
                $discount_amount = round((float)$coupon_result['discount'], 2);
            } else {
                $errors['coupon_code'] = 'Invalid or expired coupon code.';
            }
        }

        if (empty($errors)) {
            // Delivery fee from zone
            $zone         = zone_get_by_id($conn, $zone_id);
            $delivery_fee = $zone ? (float)$zone['delivery_fee'] : 0.00;
            $total        = $subtotal - $discount_amount + $delivery_fee;

            $order_data = array(
                'customer_id'      => $uid,
                'shipping_address' => $shipping_address_str,
                'zone_id'          => $zone_id,
                'payment_method'   => $payment_method,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discount_amount,
                'delivery_fee'     => $delivery_fee,
                'total_amount'     => $total,
                'coupon_id'        => $coupon_id,
            );

            $order_id = order_create($conn, $order_data);

            if ($order_id) {
                // Build items array and pass to order_add_items in one call
                // (order_add_items also decrements stock internally)
                $items_to_add = array();
                foreach ($cart_items as $item) {
                    $items_to_add[] = array(
                        'product_id' => (int)$item['product_id'],
                        'seller_id'  => (int)$item['seller_id'],
                        'quantity'   => (int)$item['qty'],
                        'unit_price' => (float)$item['price'],
                    );
                }
                order_add_items($conn, $order_id, $items_to_add);

                if ($coupon_id) {
                    coupon_increment_use($conn, $coupon_id);
                }

                cart_clear();
                redirect(BASE_URL . '?c=customer&a=orderConfirm&id=' . $order_id);
            } else {
                $errors['order'] = 'Order could not be placed. Please try again.';
            }
        }
    }

    $addresses = address_get_by_customer($conn, $uid);
    $zones     = zone_get_all($conn);
    $subtotal  = cart_subtotal($conn);

    include APP . '/views/customer/checkout.php';
}

// ----------------------------------------------------------------
// orderConfirm()
// ----------------------------------------------------------------
function customer_orderConfirm($conn) {
    customer_require_auth();

    $uid      = (int)$_SESSION['uid'];
    $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($order_id <= 0) {
        redirect(BASE_URL . '?c=customer&a=orders');
    }

    $order = order_get_by_id($conn, $order_id);
    if (!$order || (int)$order['customer_id'] !== $uid) {
        set_flash('error', 'Order not found.');
        redirect(BASE_URL . '?c=customer&a=orders');
    }

    $items = order_get_items($conn, $order_id);

    include APP . '/views/customer/order_confirm.php';
}

// ----------------------------------------------------------------
// orders()
// ----------------------------------------------------------------
function customer_orders($conn) {
    customer_require_auth();

    $uid    = (int)$_SESSION['uid'];
    $orders = order_get_by_customer($conn, $uid);

    include APP . '/views/customer/orders.php';
}

// ----------------------------------------------------------------
// orderDetail()
// ----------------------------------------------------------------
function customer_orderDetail($conn) {
    customer_require_auth();

    $uid      = (int)$_SESSION['uid'];
    $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($order_id <= 0) {
        redirect(BASE_URL . '?c=customer&a=orders');
    }

    $order = order_get_by_id($conn, $order_id);
    if (!$order || (int)$order['customer_id'] !== $uid) {
        set_flash('error', 'Order not found.');
        redirect(BASE_URL . '?c=customer&a=orders');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action === 'cancel') {
            if (order_can_cancel($conn, $order_id)) {
                order_update_status($conn, $order_id, 'cancelled');
                set_flash('success', 'Order cancelled successfully.');
            } else {
                set_flash('error', 'This order cannot be cancelled.');
            }
            redirect(BASE_URL . '?c=customer&a=orderDetail&id=' . $order_id);

        } elseif ($action === 'return_request') {
            $order_item_id = isset($_POST['order_item_id']) ? (int)$_POST['order_item_id'] : 0;
            $reason        = isset($_POST['reason'])        ? trim($_POST['reason'])        : '';

            $r_errors = array();
            if ($order_item_id <= 0) {
                $r_errors[] = 'Please select an item to return.';
            }
            if ($reason === '') {
                $r_errors[] = 'Please provide a reason for the return.';
            }

            if (empty($r_errors)) {
                return_create($conn, array(
                    'order_id'      => $order_id,
                    'order_item_id' => $order_item_id,
                    'customer_id'   => $uid,
                    'reason'        => $reason,
                ));
                set_flash('success', 'Return request submitted.');
            } else {
                set_flash('error', implode(' ', $r_errors));
            }
            redirect(BASE_URL . '?c=customer&a=orderDetail&id=' . $order_id);
        }
    }

    $items                = order_get_items($conn, $order_id);
    $delivery_assignments = order_get_delivery_assignments($conn, $order_id);
    include APP . '/views/customer/order_detail.php';
}

// ----------------------------------------------------------------
// wishlist()
// ----------------------------------------------------------------
function customer_wishlist($conn) {
    customer_require_auth();

    $uid      = (int)$_SESSION['uid'];
    $wishlist = wishlist_get($conn, $uid);

    include APP . '/views/customer/wishlist.php';
}

// ----------------------------------------------------------------
// disputes()
// ----------------------------------------------------------------
function customer_disputes($conn) {
    customer_require_auth();

    $uid    = (int)$_SESSION['uid'];
    $errors = array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $order_id    = isset($_POST['order_id'])    ? (int)$_POST['order_id']    : 0;
        $seller_id   = isset($_POST['seller_id'])   ? (int)$_POST['seller_id']   : 0;
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';

        if ($order_id <= 0) {
            $errors['order_id']  = 'Please select the related order.';
        }
        if ($seller_id <= 0) {
            $errors['seller_id'] = 'Please select the seller this dispute is about.';
        }
        if ($description === '') {
            $errors['description'] = 'Please describe the issue.';
        }

        // Anti-tamper: confirm the seller really belongs to this order
        // for this customer. Without this, a customer could change the
        // hidden seller_id in DevTools and file disputes against any
        // seller, who would then see complaints about orders they
        // never fulfilled.
        if (empty($errors) && !dispute_seller_in_order($conn, $order_id, $uid, $seller_id)) {
            $errors['seller_id'] = 'The selected seller is not part of this order.';
        }

        if (empty($errors)) {
            dispute_create($conn, array(
                'customer_id' => $uid,
                'seller_id'   => $seller_id,
                'order_id'    => $order_id,
                'description' => $description,
            ));
            set_flash('success', 'Dispute submitted. Our team will review it shortly.');
            redirect(BASE_URL . '?c=customer&a=disputes');
        }
    }

    $disputes = dispute_get_by_customer($conn, $uid);
    $orders   = order_get_by_customer($conn, $uid);

    // Map of order_id => list of {seller_id, shop_name} for that order,
    // used by the dispute form's seller dropdown (filtered by chosen order).
    $sellers_by_order = array();
    foreach ($orders as $ord) {
        $oid = isset($ord['order_id']) ? (int)$ord['order_id'] : (isset($ord['id']) ? (int)$ord['id'] : 0);
        if ($oid > 0) {
            $sellers_by_order[$oid] = dispute_sellers_for_order($conn, $oid, $uid);
        }
    }

    include APP . '/views/customer/disputes.php';
}
