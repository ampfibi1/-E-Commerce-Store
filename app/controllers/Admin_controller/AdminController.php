<?php
// ============================================================
// app/controllers/Admin_controller/AdminController.php
// Procedural controller — no classes (per CONTRIBUTING.md rule).
// Every action is a function named "admin_<action>" so the router
// dispatches ?c=admin&a=dashboard → admin_dashboard($conn).
// $conn is passed in by the router; never via $this.
//
// All DB access uses prepared statements (see app/models/Admin_model/*).
// ============================================================

// ----------------------------------------------------------------
// Auth gate. Called as the FIRST line of every admin_* function so
// non-admin users cannot reach any admin endpoint. Replaces the
// previous class constructor's require_role('admin') call.
// ----------------------------------------------------------------
function admin_require_auth() {
    require_role('admin');
}

// ----------------------------------------------------------------
// Helper: render a legacy admin view. Views still do session_start()
// and read $_SESSION['<key>'] — we set those keys before include to
// stay backward-compatible without rewriting the view layer.
// ----------------------------------------------------------------
function admin_render($view_file) {
    $path = APP . '/views/admin/' . $view_file;
    if (!file_exists($path)) {
        http_response_code(404);
        include APP . '/views/404.php';
        return;
    }
    include $path;
}

// ===== Dashboard =====
function admin_dashboard($conn) {
    admin_require_auth();
    $data = getDashboardData($conn);
    $_SESSION['dashboard'] = $data;
    admin_render('dashboard.php');
}

// ===== Sellers =====
function admin_sellers($conn) {
    admin_require_auth();
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $action   = $_GET['action'];
        $sellerId = (int)$_GET['id'];
        switch ($action) {
            case 'approve':    approveSeller($sellerId, $conn); break;
            case 'reject':     rejectSeller($sellerId, $conn);  break;
            case 'suspend':    suspendSeller($sellerId, $conn); break;
            case 'reactivate': reactivateSeller($sellerId, $conn); break;
        }
    }
    $sellers = getAllSellers($conn);
    $_SESSION['sellers'] = $sellers;
    admin_render('sellers.php');
}

// ===== Categories =====
function admin_categories($conn) {
    admin_require_auth();
    $_SESSION['error'] = '';
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        deleteCategory((int)$_GET['id'], $conn);
    }
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $_SESSION['editCat'] = getCategoryById((int)$_GET['id'], $conn);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name        = trim($_POST['name']        ?? '');
        $description = trim($_POST['description'] ?? '');
        $parentId    = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        if ($name === '' || $description === '') {
            $_SESSION['error'] = 'All fields are required.';
        } else {
            if (isset($_POST['add_category'])) {
                addCategory($name, $description, $parentId, $conn);
            }
            if (isset($_POST['edit_category']) && isset($_POST['id'])) {
                updateCategory((int)$_POST['id'], $name, $description, $parentId, $conn);
            }
        }
    }
    $_SESSION['categories'] = getAllCategories($conn);
    $_SESSION['parents']    = getParentCategories($conn);
    admin_render('categories.php');
}

// ===== Products =====
function admin_products($conn) {
    admin_require_auth();
    if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
        removeProduct((int)$_GET['id'], $conn);
        redirect(BASE_URL . '?c=admin&a=products');
    }
    $search   = trim($_POST['search']   ?? '');
    $category = trim($_POST['category'] ?? '');
    $seller   = trim($_POST['seller']   ?? '');
    $_SESSION['products']          = getAllProducts($search, $category, $seller, $conn);
    $_SESSION['categories_filter'] = getCategoriesForFilter($conn);
    $_SESSION['sellers_filter']    = getSellersForFilter($conn);
    $_SESSION['search']   = $search;
    $_SESSION['category'] = $category;
    $_SESSION['seller']   = $seller;
    admin_render('products.php');
}

// ===== Orders =====
function admin_orders($conn) {
    admin_require_auth();
    $status    = trim($_POST['status']    ?? '');
    $date_from = trim($_POST['date_from'] ?? '');
    $date_to   = trim($_POST['date_to']   ?? '');
    $seller    = trim($_POST['seller']    ?? '');
    $customer  = trim($_POST['customer']  ?? '');

    $_SESSION['orders']          = getAllOrders($status, $date_from, $date_to, $seller, $customer, $conn);
    $_SESSION['sellers_order']   = getSellersForOrderFilter($conn);
    $_SESSION['customers_order'] = getCustomersForOrderFilter($conn);
    $_SESSION['status']    = $status;
    $_SESSION['date_from'] = $date_from;
    $_SESSION['date_to']   = $date_to;
    $_SESSION['seller']    = $seller;
    $_SESSION['customer']  = $customer;
    admin_render('orders.php');
}

// ===== Users =====
function admin_users($conn) {
    admin_require_auth();
    $action = $_GET['action'] ?? '';
    if ($action === 'deactivate' && isset($_GET['id'])) {
        deactivateUser((int)$_GET['id'], $conn);
    }
    if ($action === 'reactivate' && isset($_GET['id'])) {
        reactivateUser((int)$_GET['id'], $conn);
    }
    $search = trim($_POST['search'] ?? '');
    $role   = trim($_POST['role']   ?? '');
    $_SESSION['users']        = getAllUsers($search, $role, $conn);
    $_SESSION['users_search'] = $search;
    $_SESSION['users_role']   = $role;
    admin_render('users.php');
}

// ===== Disputes =====
function admin_disputes($conn) {
    admin_require_auth();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_dispute'])) {
        resolveDispute((int)$_POST['id'], trim($_POST['note'] ?? ''), $conn);
    }
    $_SESSION['disputes'] = getAllDisputes($conn);
    admin_render('disputes.php');
}

// ===== Coupons =====
function admin_coupons($conn) {
    admin_require_auth();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_coupon'])) {
            addPlatformCoupon(
                trim($_POST['code'] ?? ''),
                (float)($_POST['discount_pct'] ?? 0),
                (int)($_POST['max_uses'] ?? 0),
                trim($_POST['valid_until'] ?? ''),
                $conn
            );
        } elseif (isset($_POST['deactivate'])) {
            deactivateCoupon((int)$_POST['id'], $conn);
        }
    }
    $_SESSION['coupons'] = getPlatformCoupons($conn);
    admin_render('coupons.php');
}

// ===== Analytics =====
function admin_analytics($conn) {
    admin_require_auth();
    $_SESSION['analytics'] = getAnalyticsData($conn);
    admin_render('analytics.php');
}

// ===== Reports =====
function admin_reports($conn) {
    admin_require_auth();
    $month = date('Y-m');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month'])) {
        $month = trim($_POST['month']);
    }
    $_SESSION['report']       = generateMonthlyReport($month, $conn);
    $_SESSION['report_month'] = $month;
    admin_render('reports.php');
}

// ===== Announcements =====
function admin_announcements($conn) {
    admin_require_auth();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_announcement'])) {
        postAnnouncement(
            trim($_POST['title']   ?? ''),
            trim($_POST['content'] ?? ''),
            $conn
        );
    }
    $_SESSION['announcements'] = getAnnouncements($conn);
    admin_render('announcements.php');
}

// ===== Settings =====
function admin_settings($conn) {
    admin_require_auth();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_commission'])) {
        updateCommission(
            (int)$_POST['seller_id'],
            (float)$_POST['commission_rate'],
            $conn
        );
    }
    $_SESSION['commission_sellers'] = getSellersForCommission($conn);
    admin_render('settings.php');
}

// ===== AJAX: product search suggestion (returns JSON) =====
function admin_suggest($conn) {
    admin_require_auth();
    header('Content-Type: application/json');
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($q === '') { echo json_encode([]); return; }
    echo json_encode(getSuggestions($q, $conn));
}

// ===== AJAX: user search suggestion (returns JSON) =====
function admin_userSuggest($conn) {
    admin_require_auth();
    header('Content-Type: application/json');
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($q === '') { echo json_encode([]); return; }
    echo json_encode(getUserSuggestions($q, $conn));
}
