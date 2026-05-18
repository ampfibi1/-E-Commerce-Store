<?php

class SellerController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ----------------------------------------------------------------
    // dashboard()
    // ----------------------------------------------------------------
    public function dashboard() {
        require_seller_approved($this->conn);

        $sid    = (int)$_SESSION['sid'];
        $seller = seller_get_by_id($this->conn, $sid);

        // Count pending order items for this seller
        $all_seller_orders = order_get_by_seller($this->conn, $sid);
        $pending_count     = 0;
        foreach ($all_seller_orders as $so) {
            if ($so['status'] === 'pending') {
                $pending_count++;
            }
        }

        $low_stock     = product_get_low_stock($this->conn, $sid, 5);
        $earnings      = analytics_earnings($this->conn, $sid, 'day');
        $all_returns   = return_get_by_seller($this->conn, $sid);
        $return_count  = 0;
        foreach ($all_returns as $r) {
            if ($r['status'] === 'pending') $return_count++;
        }
        $all_reviews  = review_get_by_seller($this->conn, $sid);
        $review_count = 0;
        foreach ($all_reviews as $rv) {
            if (empty($rv['seller_reply'])) $review_count++;
        }
        $all_disputes   = dispute_get_by_seller($this->conn, $sid);
        $dispute_count  = 0;
        foreach ($all_disputes as $d) {
            if ($d['status'] === 'open') $dispute_count++;
        }

        include APP . '/views/seller/dashboard.php';
    }

    // ----------------------------------------------------------------
    // profile()
    // ----------------------------------------------------------------
    public function profile() {
        require_seller_approved($this->conn);

        $sid    = (int)$_SESSION['sid'];
        $uid    = (int)$_SESSION['uid'];
        $errors = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $shop_name        = isset($_POST['shop_name'])        ? trim($_POST['shop_name'])        : '';
            $shop_description = isset($_POST['shop_description']) ? trim($_POST['shop_description']) : '';
            $address          = isset($_POST['address'])          ? trim($_POST['address'])          : '';

            if ($shop_name === '') {
                $errors['shop_name'] = 'Shop name is required.';
            }
            if ($shop_description === '') {
                $errors['shop_description'] = 'Shop description is required.';
            }
            if ($address === '') {
                $errors['address'] = 'Shop address is required.';
            }

            $seller    = seller_get_by_id($this->conn, $sid);
            $logo_path = $seller ? $seller['shop_logo_path'] : '';

            // Optional logo upload
            if (isset($_FILES['shop_logo']) && $_FILES['shop_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file     = $_FILES['shop_logo'];
                $allowed  = array('image/jpeg', 'image/png', 'image/gif');
                $max_size = 2 * 1024 * 1024;

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors['shop_logo'] = 'File upload error.';
                } elseif (!in_array($mime, $allowed)) {
                    $errors['shop_logo'] = 'Only JPG, PNG, and GIF are allowed.';
                } elseif ($file['size'] > $max_size) {
                    $errors['shop_logo'] = 'Logo must not exceed 2 MB.';
                } else {
                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'logo_' . $sid . '_' . uniqid() . '.' . strtolower($ext);
                    $dest_dir = UPLOAD_PATH . 'shop_logos/';
                    $dest     = $dest_dir . $filename;

                    if (!is_dir($dest_dir)) {
                        mkdir($dest_dir, 0755, true);
                    }

                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        $errors['shop_logo'] = 'Could not save the uploaded file.';
                    } else {
                        $logo_path = 'shop_logos/' . $filename;
                    }
                }
            }

            if (empty($errors)) {
                seller_update($this->conn, $sid, array(
                    'shop_name'        => $shop_name,
                    'shop_description' => $shop_description,
                    'address'          => $address,
                    'shop_logo_path'   => $logo_path,
                ));
                set_flash('success', 'Shop profile updated.');
                redirect(BASE_URL . '?c=seller&a=profile');
            }
        }

        $seller = seller_get_by_id($this->conn, $sid);
        $user   = user_get_by_id($this->conn, $uid);

        include APP . '/views/seller/profile.php';
    }

    // ----------------------------------------------------------------
    // products()
    // ----------------------------------------------------------------
    public function products() {
        require_seller_approved($this->conn);

        $sid = (int)$_SESSION['sid'];
        $do  = isset($_GET['do']) ? trim($_GET['do']) : '';
        $pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($do === 'toggle' && $pid > 0) {
            $p = product_get_by_id($this->conn, $pid);
            if ($p && (int)$p['seller_id'] === $sid) {
                product_toggle_availability($this->conn, $pid);
            }
            redirect(BASE_URL . '?c=seller&a=products');
        }

        if ($do === 'delete' && $pid > 0) {
            $p = product_get_by_id($this->conn, $pid);
            if ($p && (int)$p['seller_id'] === $sid) {
                if (!product_has_pending_orders($this->conn, $pid)) {
                    product_delete_images($this->conn, $pid);
                    product_delete($this->conn, $pid);
                    set_flash('success', 'Product deleted.');
                } else {
                    set_flash('error', 'Cannot delete: product has pending orders.');
                }
            }
            redirect(BASE_URL . '?c=seller&a=products');
        }

        $products      = product_get_by_seller($this->conn, $sid);
        $low_stock_raw = product_get_low_stock($this->conn, $sid, 5);
        $low_stock_ids = array();
        foreach ($low_stock_raw as $ls) {
            $low_stock_ids[] = (int)$ls['id'];
        }

        include APP . '/views/seller/products.php';
    }

    // ----------------------------------------------------------------
    // addProduct()
    // ----------------------------------------------------------------
    public function addProduct() {
        require_seller_approved($this->conn);

        $sid    = (int)$_SESSION['sid'];
        $errors = array();
        $old    = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = isset($_POST['name'])        ? trim($_POST['name'])        : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $price       = isset($_POST['price'])       ? trim($_POST['price'])       : '';
            $stock_qty   = isset($_POST['stock_qty'])   ? trim($_POST['stock_qty'])   : '';
            $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
            $is_available= isset($_POST['is_available'])? (int)$_POST['is_available']: 1;

            $old['name']        = sanitize($name);
            $old['description'] = sanitize($description);
            $old['price']       = sanitize($price);
            $old['stock_qty']   = sanitize($stock_qty);
            $old['category_id'] = $category_id;

            if ($name === '') {
                $errors['name'] = 'Product name is required.';
            }
            if ($description === '') {
                $errors['description'] = 'Product description is required.';
            }
            if ($price === '') {
                $errors['price'] = 'Price is required.';
            } elseif (!is_numeric($price) || (float)$price <= 0) {
                $errors['price'] = 'Price must be a positive number.';
            }
            if ($stock_qty === '') {
                $errors['stock_qty'] = 'Stock quantity is required.';
            } elseif (!ctype_digit($stock_qty) || (int)$stock_qty < 0) {
                $errors['stock_qty'] = 'Stock quantity must be 0 or greater.';
            }
            if ($category_id <= 0) {
                $errors['category_id'] = 'Please select a category.';
            }

            // Primary image – required
            $primary_image_path = '';
            if (!isset($_FILES['primary_image']) || $_FILES['primary_image']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors['primary_image'] = 'A primary product image is required.';
            } else {
                $file     = $_FILES['primary_image'];
                $allowed  = array('image/jpeg', 'image/png', 'image/gif');
                $max_size = 2 * 1024 * 1024;

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors['primary_image'] = 'Primary image upload failed.';
                } elseif (!in_array($mime, $allowed)) {
                    $errors['primary_image'] = 'Only JPG, PNG, and GIF images are allowed.';
                } elseif ($file['size'] > $max_size) {
                    $errors['primary_image'] = 'Image must not exceed 2 MB.';
                } else {
                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'prod_' . uniqid() . '.' . strtolower($ext);
                    $dest_dir = UPLOAD_PATH . 'product_images/';
                    $dest     = $dest_dir . $filename;

                    if (!is_dir($dest_dir)) {
                        mkdir($dest_dir, 0755, true);
                    }

                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        $errors['primary_image'] = 'Could not save the primary image.';
                    } else {
                        $primary_image_path = 'product_images/' . $filename;
                    }
                }
            }

            if (empty($errors)) {
                $product_id = product_create($this->conn, array(
                    'seller_id'          => $sid,
                    'category_id'        => $category_id,
                    'name'               => $name,
                    'description'        => $description,
                    'price'              => (float)$price,
                    'stock_qty'          => (int)$stock_qty,
                    'primary_image_path' => $primary_image_path,
                ));

                if ($product_id) {
                    // Extra images (up to 4)
                    if (isset($_FILES['images']) && is_array($_FILES['images']['error'])) {
                        $extra_files  = $_FILES['images'];
                        $allowed_ext  = array('image/jpeg', 'image/png', 'image/gif');
                        $max_size_img = 2 * 1024 * 1024;
                        $order        = 1;

                        for ($i = 0; $i < min(4, count($extra_files['error'])); $i++) {
                            if ($extra_files['error'][$i] !== UPLOAD_ERR_OK) {
                                continue;
                            }
                            if ($extra_files['size'][$i] > $max_size_img) {
                                continue;
                            }

                            $finfo2 = finfo_open(FILEINFO_MIME_TYPE);
                            $mime2  = finfo_file($finfo2, $extra_files['tmp_name'][$i]);
                            finfo_close($finfo2);

                            if (!in_array($mime2, $allowed_ext)) {
                                continue;
                            }

                            $ext2      = pathinfo($extra_files['name'][$i], PATHINFO_EXTENSION);
                            $fname2    = 'prod_' . $product_id . '_' . uniqid() . '.' . strtolower($ext2);
                            $dest_dir2 = UPLOAD_PATH . 'product_images/';
                            $dest2     = $dest_dir2 . $fname2;

                            if (!is_dir($dest_dir2)) {
                                mkdir($dest_dir2, 0755, true);
                            }

                            if (move_uploaded_file($extra_files['tmp_name'][$i], $dest2)) {
                                product_add_image($this->conn, $product_id, 'product_images/' . $fname2, $order);
                                $order++;
                            }
                        }
                    }

                    set_flash('success', 'Product added successfully.');
                    redirect(BASE_URL . '?c=seller&a=products');
                } else {
                    $errors['product'] = 'Could not save the product. Please try again.';
                }
            }
        }

        $categories = category_get_all($this->conn);
        include APP . '/views/seller/add_product.php';
    }

    // ----------------------------------------------------------------
    // editProduct()
    // ----------------------------------------------------------------
    public function editProduct() {
        require_seller_approved($this->conn);

        $sid        = (int)$_SESSION['sid'];
        $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $errors     = array();
        $old        = array();

        if ($product_id <= 0) {
            redirect(BASE_URL . '?c=seller&a=products');
        }

        $product = product_get_by_id($this->conn, $product_id);
        if (!$product || (int)$product['seller_id'] !== $sid) {
            set_flash('error', 'Product not found.');
            redirect(BASE_URL . '?c=seller&a=products');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name         = isset($_POST['name'])         ? trim($_POST['name'])         : '';
            $description  = isset($_POST['description'])  ? trim($_POST['description'])  : '';
            $price        = isset($_POST['price'])        ? trim($_POST['price'])        : '';
            $stock_qty    = isset($_POST['stock_qty'])    ? trim($_POST['stock_qty'])    : '';
            $category_id  = isset($_POST['category_id'])  ? (int)$_POST['category_id']  : 0;
            $is_available = isset($_POST['is_available']) ? (int)$_POST['is_available'] : 1;

            $old['name']        = sanitize($name);
            $old['description'] = sanitize($description);
            $old['price']       = sanitize($price);
            $old['stock_qty']   = sanitize($stock_qty);
            $old['category_id'] = $category_id;

            if ($name === '') {
                $errors['name'] = 'Product name is required.';
            }
            if ($description === '') {
                $errors['description'] = 'Description is required.';
            }
            if ($price === '') {
                $errors['price'] = 'Price is required.';
            } elseif (!is_numeric($price) || (float)$price <= 0) {
                $errors['price'] = 'Price must be a positive number.';
            }
            if ($stock_qty === '') {
                $errors['stock_qty'] = 'Stock quantity is required.';
            } elseif (!ctype_digit($stock_qty) || (int)$stock_qty < 0) {
                $errors['stock_qty'] = 'Stock quantity must be 0 or greater.';
            }
            if ($category_id <= 0) {
                $errors['category_id'] = 'Please select a category.';
            }

            // Optional new primary image
            $primary_image_path = $product['primary_image_path'];
            if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file     = $_FILES['primary_image'];
                $allowed  = array('image/jpeg', 'image/png', 'image/gif');
                $max_size = 2 * 1024 * 1024;

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors['primary_image'] = 'Primary image upload failed.';
                } elseif (!in_array($mime, $allowed)) {
                    $errors['primary_image'] = 'Only JPG, PNG, and GIF images are allowed.';
                } elseif ($file['size'] > $max_size) {
                    $errors['primary_image'] = 'Image must not exceed 2 MB.';
                } else {
                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'prod_' . $product_id . '_' . uniqid() . '.' . strtolower($ext);
                    $dest_dir = UPLOAD_PATH . 'product_images/';
                    $dest     = $dest_dir . $filename;

                    if (!is_dir($dest_dir)) {
                        mkdir($dest_dir, 0755, true);
                    }

                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        $errors['primary_image'] = 'Could not save the new primary image.';
                    } else {
                        $primary_image_path = 'product_images/' . $filename;
                    }
                }
            }

            if (empty($errors)) {
                product_update($this->conn, $product_id, array(
                    'category_id'        => $category_id,
                    'name'               => $name,
                    'description'        => $description,
                    'price'              => (float)$price,
                    'stock_qty'          => (int)$stock_qty,
                    'primary_image_path' => $primary_image_path,
                    'is_available'       => $is_available,
                ));

                // Replace extra images if new ones were uploaded
                if (isset($_FILES['images']) && is_array($_FILES['images']['error'])) {
                    $has_new_extras = false;
                    foreach ($_FILES['images']['error'] as $ferr) {
                        if ($ferr === UPLOAD_ERR_OK) {
                            $has_new_extras = true;
                            break;
                        }
                    }

                    if ($has_new_extras) {
                        product_delete_images($this->conn, $product_id);

                        $extra_files  = $_FILES['images'];
                        $allowed_ext  = array('image/jpeg', 'image/png', 'image/gif');
                        $max_size_img = 2 * 1024 * 1024;
                        $order        = 1;

                        for ($i = 0; $i < min(4, count($extra_files['error'])); $i++) {
                            if ($extra_files['error'][$i] !== UPLOAD_ERR_OK) {
                                continue;
                            }
                            if ($extra_files['size'][$i] > $max_size_img) {
                                continue;
                            }

                            $finfo2 = finfo_open(FILEINFO_MIME_TYPE);
                            $mime2  = finfo_file($finfo2, $extra_files['tmp_name'][$i]);
                            finfo_close($finfo2);

                            if (!in_array($mime2, $allowed_ext)) {
                                continue;
                            }

                            $ext2      = pathinfo($extra_files['name'][$i], PATHINFO_EXTENSION);
                            $fname2    = 'prod_' . $product_id . '_' . uniqid() . '.' . strtolower($ext2);
                            $dest_dir2 = UPLOAD_PATH . 'product_images/';
                            $dest2     = $dest_dir2 . $fname2;

                            if (!is_dir($dest_dir2)) {
                                mkdir($dest_dir2, 0755, true);
                            }

                            if (move_uploaded_file($extra_files['tmp_name'][$i], $dest2)) {
                                product_add_image($this->conn, $product_id, 'product_images/' . $fname2, $order);
                                $order++;
                            }
                        }
                    }
                }

                set_flash('success', 'Product updated successfully.');
                redirect(BASE_URL . '?c=seller&a=products');
            }

            // Re-fetch to get updated state for re-render
            $product = product_get_by_id($this->conn, $product_id);
        }

        $images     = product_get_images($this->conn, $product_id);
        $categories = category_get_all($this->conn);

        include APP . '/views/seller/edit_product.php';
    }

    // ----------------------------------------------------------------
    // orders()
    // ----------------------------------------------------------------
    public function orders() {
        require_seller_approved($this->conn);

        $sid    = (int)$_SESSION['sid'];
        $status     = isset($_GET['status']) ? trim($_GET['status']) : 'all';
        $all_orders = order_get_by_seller($this->conn, $sid);

        // Filter by status in PHP if not 'all'
        if ($status !== 'all') {
            $orders = array();
            foreach ($all_orders as $o) {
                if ($o['status'] === $status) {
                    $orders[] = $o;
                }
            }
        } else {
            $orders = $all_orders;
        }

        $filter_status = $status;
        include APP . '/views/seller/orders.php';
    }

    // ----------------------------------------------------------------
    // orderDetail()
    // ----------------------------------------------------------------
    public function orderDetail() {
        require_seller_approved($this->conn);

        $sid      = (int)$_SESSION['sid'];
        $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($order_id <= 0) {
            redirect(BASE_URL . '?c=seller&a=orders');
        }

        $order = order_get_by_id($this->conn, $order_id);
        if (!$order) {
            set_flash('error', 'Order not found.');
            redirect(BASE_URL . '?c=seller&a=orders');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action  = isset($_POST['action'])  ? $_POST['action']      : '';
            $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

            if ($action === 'confirm_item' && $item_id > 0) {
                order_item_update_status($this->conn, $item_id, 'confirmed', '');
                set_flash('success', 'Item confirmed.');
            } elseif ($action === 'ship_item' && $item_id > 0) {
                $tracking_note = isset($_POST['tracking_note']) ? trim($_POST['tracking_note']) : '';
                order_item_update_status($this->conn, $item_id, 'shipped', $tracking_note);
                set_flash('success', 'Item marked as shipped.');
            }
            redirect(BASE_URL . '?c=seller&a=orderDetail&id=' . $order_id);
        }

        $items = order_get_items_by_seller($this->conn, $order_id, $sid);

        include APP . '/views/seller/order_detail.php';
    }

    // ----------------------------------------------------------------
    // coupons()
    // ----------------------------------------------------------------
    public function coupons() {
        require_seller_approved($this->conn);

        $sid    = (int)$_SESSION['sid'];
        $errors = array();
        $old    = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code         = isset($_POST['code'])         ? trim($_POST['code'])         : '';
            $discount_pct = isset($_POST['discount_pct']) ? trim($_POST['discount_pct']) : '';
            $max_uses     = isset($_POST['max_uses'])     ? trim($_POST['max_uses'])     : '';
            $valid_until  = isset($_POST['valid_until'])  ? trim($_POST['valid_until'])  : '';

            $old['code']         = sanitize($code);
            $old['discount_pct'] = sanitize($discount_pct);
            $old['max_uses']     = sanitize($max_uses);
            $old['valid_until']  = sanitize($valid_until);

            if ($code === '') {
                $errors['code'] = 'Coupon code is required.';
            } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $code)) {
                $errors['code'] = 'Coupon code must be alphanumeric only.';
            }

            if ($discount_pct === '') {
                $errors['discount_pct'] = 'Discount percentage is required.';
            } elseif (!is_numeric($discount_pct) || (float)$discount_pct < 1 || (float)$discount_pct > 100) {
                $errors['discount_pct'] = 'Discount must be between 1 and 100.';
            }

            if ($max_uses === '') {
                $errors['max_uses'] = 'Max uses is required.';
            } elseif (!ctype_digit($max_uses) || (int)$max_uses < 0) {
                $errors['max_uses'] = 'Max uses must be 0 or greater.';
            }

            if ($valid_until === '') {
                $errors['valid_until'] = 'Expiry date is required.';
            } else {
                $expire_ts = strtotime($valid_until);
                if ($expire_ts === false || $expire_ts <= time()) {
                    $errors['valid_until'] = 'Expiry date must be a future date.';
                }
            }

            if (empty($errors)) {
                coupon_create($this->conn, array(
                    'seller_id'    => $sid,
                    'code'         => strtoupper($code),
                    'discount_pct' => (float)$discount_pct,
                    'max_uses'     => (int)$max_uses,
                    'valid_until'  => $valid_until,
                ));
                set_flash('success', 'Coupon created successfully.');
                redirect(BASE_URL . '?c=seller&a=coupons');
            }
        }

        $coupons = coupon_get_by_seller($this->conn, $sid);
        include APP . '/views/seller/coupons.php';
    }

    // ----------------------------------------------------------------
    // analytics()
    // ----------------------------------------------------------------
    public function analytics() {
        require_seller_approved($this->conn);

        $sid = (int)$_SESSION['sid'];

        $revenue      = analytics_revenue($this->conn, $sid, 'month');
        $top_products = analytics_top_products($this->conn, $sid, 5);
        $order_volume = analytics_order_volume($this->conn, $sid, 'month');
        $earnings     = analytics_earnings($this->conn, $sid, 'month');

        $revenue_json      = json_encode($revenue);
        $top_products_json = json_encode($top_products);
        $order_volume_json = json_encode($order_volume);
        $earnings_json     = json_encode($earnings);

        include APP . '/views/seller/analytics.php';
    }

    // ----------------------------------------------------------------
    // reviews()
    // ----------------------------------------------------------------
    public function reviews() {
        require_seller_approved($this->conn);

        $sid     = (int)$_SESSION['sid'];
        $reviews = review_get_by_seller($this->conn, $sid);

        include APP . '/views/seller/reviews.php';
    }

    // ----------------------------------------------------------------
    // returns()
    // ----------------------------------------------------------------
    public function returns() {
        require_seller_approved($this->conn);

        $sid    = (int)$_SESSION['sid'];
        $errors = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action    = isset($_POST['action'])    ? $_POST['action']      : '';
            $return_id = isset($_POST['return_id']) ? (int)$_POST['return_id'] : 0;

            if (in_array($action, array('approve', 'reject')) && $return_id > 0) {
                $new_status = ($action === 'approve') ? 'approved' : 'rejected';
                return_update_status($this->conn, $return_id, $new_status, $sid);
                set_flash('success', 'Return request ' . $new_status . '.');
            } else {
                set_flash('error', 'Invalid action.');
            }
            redirect(BASE_URL . '?c=seller&a=returns');
        }

        $returns = return_get_by_seller($this->conn, $sid);
        include APP . '/views/seller/returns.php';
    }

    public function disputes() {
        require_seller_approved($this->conn);
        $sid      = (int)$_SESSION['sid'];
        $disputes = dispute_get_by_seller($this->conn, $sid);
        include APP . '/views/seller/disputes.php';
    }

    public function disputeDetail() {
        require_seller_approved($this->conn);
        $sid = (int)$_SESSION['sid'];
        $id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $errors = array();

        if ($id <= 0) {
            set_flash('error', 'Invalid dispute id.');
            redirect(BASE_URL . '?c=seller&a=disputes');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = isset($_POST['seller_response']) ? trim($_POST['seller_response']) : '';
            $action   = isset($_POST['action']) ? $_POST['action'] : '';

            if ($response === '') {
                $errors['seller_response'] = 'Please write a response before submitting.';
            }
            if (!in_array($action, array('accepted', 'rejected'), true)) {
                $errors['general'] = 'Please choose Accept or Reject.';
            }

            if (empty($errors)) {
                $ok = dispute_seller_respond($this->conn, $id, $sid, $response, $action);
                if ($ok) {
                    set_flash('success', $action === 'accepted'
                        ? 'Dispute accepted and marked as resolved.'
                        : 'Dispute rejected and escalated to admin.');
                    redirect(BASE_URL . '?c=seller&a=disputeDetail&id=' . $id);
                } else {
                    $errors['general'] = 'Could not save your response. Please try again.';
                }
            }
        }

        $dispute = dispute_get_one_for_seller($this->conn, $id, $sid);
        if (!$dispute) {
            set_flash('error', 'Dispute not found or not yours.');
            redirect(BASE_URL . '?c=seller&a=disputes');
        }

        include APP . '/views/seller/dispute_detail.php';
    }
}
