<?php
// Admin role dispatcher — routed via public/index.php?c=admin&a=<action>
// All actions are gated by require_role('admin'). All DB access uses
// prepared statements (see model/adminModel/*.php).

class AdminController {
    private $conn;
    private $views_dir;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->views_dir = APP . '/views/admin';
        // Hard gate for the entire admin role.
        require_role('admin');
    }

    // Helper: render a legacy admin view. Views still do session_start() and
    // read $_SESSION['<key>'] — we set those keys before include to stay
    // backward-compatible without rewriting the view layer.
    private function render($view_file) {
        $path = $this->views_dir . '/' . $view_file;
        if (!file_exists($path)) {
            http_response_code(404);
            include APP . '/views/404.php';
            return;
        }
        include $path;
    }

    // ===== Dashboard =====
    public function dashboard() {
        $data = getDashboardData($this->conn);
        $_SESSION['dashboard'] = $data;
        $this->render('dashboard.php');
    }

    // ===== Sellers =====
    public function sellers() {
        if (isset($_GET['action']) && isset($_GET['id'])) {
            $action = $_GET['action'];
            $sellerId = (int)$_GET['id'];
            switch ($action) {
                case 'approve':    approveSeller($sellerId, $this->conn); break;
                case 'reject':     rejectSeller($sellerId, $this->conn);  break;
                case 'suspend':    suspendSeller($sellerId, $this->conn); break;
                case 'reactivate': reactivateSeller($sellerId, $this->conn); break;
            }
        }
        $sellers = getAllSellers($this->conn);
        $_SESSION['sellers'] = $sellers;
        $this->render('sellers.php');
    }

    // ===== Categories =====
    public function categories() {
        $_SESSION['error'] = '';
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            deleteCategory((int)$_GET['id'], $this->conn);
        }
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $_SESSION['editCat'] = getCategoryById((int)$_GET['id'], $this->conn);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            if ($name === '' || $description === '') {
                $_SESSION['error'] = 'All fields are required.';
            } else {
                if (isset($_POST['add_category'])) {
                    addCategory($name, $description, $parentId, $this->conn);
                }
                if (isset($_POST['edit_category']) && isset($_POST['id'])) {
                    updateCategory((int)$_POST['id'], $name, $description, $parentId, $this->conn);
                }
            }
        }
        $_SESSION['categories'] = getAllCategories($this->conn);
        $_SESSION['parents']    = getParentCategories($this->conn);
        $this->render('categories.php');
    }

    // ===== Products =====
    public function products() {
        if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
            removeProduct((int)$_GET['id'], $this->conn);
            redirect(BASE_URL . '?c=admin&a=products');
        }
        $search   = trim($_POST['search']   ?? '');
        $category = trim($_POST['category'] ?? '');
        $seller   = trim($_POST['seller']   ?? '');
        $_SESSION['products']          = getAllProducts($search, $category, $seller, $this->conn);
        $_SESSION['categories_filter'] = getCategoriesForFilter($this->conn);
        $_SESSION['sellers_filter']    = getSellersForFilter($this->conn);
        $_SESSION['search']   = $search;
        $_SESSION['category'] = $category;
        $_SESSION['seller']   = $seller;
        $this->render('products.php');
    }

    // ===== Orders =====
    public function orders() {
        $status    = trim($_POST['status']    ?? '');
        $date_from = trim($_POST['date_from'] ?? '');
        $date_to   = trim($_POST['date_to']   ?? '');
        $seller    = trim($_POST['seller']    ?? '');
        $customer  = trim($_POST['customer']  ?? '');

        $_SESSION['orders']          = getAllOrders($status, $date_from, $date_to, $seller, $customer, $this->conn);
        $_SESSION['sellers_order']   = getSellersForOrderFilter($this->conn);
        $_SESSION['customers_order'] = getCustomersForOrderFilter($this->conn);
        $_SESSION['status']    = $status;
        $_SESSION['date_from'] = $date_from;
        $_SESSION['date_to']   = $date_to;
        $_SESSION['seller']    = $seller;
        $_SESSION['customer']  = $customer;
        $this->render('orders.php');
    }

    // ===== Users =====
    public function users() {
        $action = $_GET['action'] ?? '';
        if ($action === 'deactivate' && isset($_GET['id'])) {
            deactivateUser((int)$_GET['id'], $this->conn);
        }
        if ($action === 'reactivate' && isset($_GET['id'])) {
            reactivateUser((int)$_GET['id'], $this->conn);
        }
        $search = trim($_POST['search'] ?? '');
        $role   = trim($_POST['role']   ?? '');
        $_SESSION['users']        = getAllUsers($search, $role, $this->conn);
        $_SESSION['users_search'] = $search;
        $_SESSION['users_role']   = $role;
        $this->render('users.php');
    }

    // ===== Disputes =====
    public function disputes() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_dispute'])) {
            resolveDispute((int)$_POST['id'], trim($_POST['note'] ?? ''), $this->conn);
        }
        $_SESSION['disputes'] = getAllDisputes($this->conn);
        $this->render('disputes.php');
    }

    // ===== Coupons =====
    public function coupons() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_coupon'])) {
                addPlatformCoupon(
                    trim($_POST['code'] ?? ''),
                    (float)($_POST['discount_pct'] ?? 0),
                    (int)($_POST['max_uses'] ?? 0),
                    trim($_POST['valid_until'] ?? ''),
                    $this->conn
                );
            } elseif (isset($_POST['deactivate'])) {
                deactivateCoupon((int)$_POST['id'], $this->conn);
            }
        }
        $_SESSION['coupons'] = getPlatformCoupons($this->conn);
        $this->render('coupons.php');
    }

    // ===== Analytics =====
    public function analytics() {
        $_SESSION['analytics'] = getAnalyticsData($this->conn);
        $this->render('analytics.php');
    }

    // ===== Reports =====
    public function reports() {
        $month = date('Y-m');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month'])) {
            $month = trim($_POST['month']);
        }
        $_SESSION['report']       = generateMonthlyReport($month, $this->conn);
        $_SESSION['report_month'] = $month;
        $this->render('reports.php');
    }

    // ===== Announcements =====
    public function announcements() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_announcement'])) {
            postAnnouncement(
                trim($_POST['title']   ?? ''),
                trim($_POST['content'] ?? ''),
                $this->conn
            );
        }
        $_SESSION['announcements'] = getAnnouncements($this->conn);
        $this->render('announcements.php');
    }

    // ===== Settings =====
    public function settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_commission'])) {
            updateCommission(
                (int)$_POST['seller_id'],
                (float)$_POST['commission_rate'],
                $this->conn
            );
        }
        $_SESSION['commission_sellers'] = getSellersForCommission($this->conn);
        $this->render('settings.php');
    }

    // ===== AJAX: product search suggestion (returns JSON) =====
    public function suggest() {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if ($q === '') { echo json_encode([]); return; }
        echo json_encode(getSuggestions($q, $this->conn));
    }

    // ===== AJAX: user search suggestion (returns JSON) =====
    public function userSuggest() {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if ($q === '') { echo json_encode([]); return; }
        echo json_encode(getUserSuggestions($q, $this->conn));
    }
}
