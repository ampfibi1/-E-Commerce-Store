<?php

class AuthController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ----------------------------------------------------------------
    // GET /index.php?c=auth&a=login
    // POST: validate, authenticate, redirect by role
    // ----------------------------------------------------------------
    public function login() {
        $errors = array();
        $old    = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
            $password = isset($_POST['password']) ? $_POST['password']      : '';

            $old['email'] = sanitize($email);

            // Validation
            if ($email === '') {
                $errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Enter a valid email address.';
            }

            if ($password === '') {
                $errors['password'] = 'Password is required.';
            }

            if (empty($errors)) {
                $user = user_get_by_email($this->conn, $email);

                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $errors['login'] = 'Invalid email or password.';
                } elseif (!(int)$user['is_active']) {
                    $errors['login'] = 'Your account has been deactivated. Please contact support.';
                } else {
                    // Build session
                    $_SESSION['uid']  = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['uname']= $user['name'];
                    $_SESSION['sid']  = null;

                    if ($user['role'] === 'seller') {
                        $seller = seller_get_by_user_id($this->conn, $user['id']);
                        if ($seller) {
                            $_SESSION['sid'] = $seller['id'];
                        }
                    }

                    if ($user['role'] === 'customer') {
                        redirect(BASE_URL . '?c=customer&a=dashboard');
                    } elseif ($user['role'] === 'seller') {
                        redirect(BASE_URL . '?c=seller&a=dashboard');
                    } elseif ($user['role'] === 'admin') {
                        // Set flat-admin session keys so the admin panel recognises this session
                        $_SESSION['user_id']   = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = 'admin';
                        redirect(str_replace('public/', '', BASE_URL));
                    } else {
                        redirect(BASE_URL);
                    }
                }
            }
        }

        include APP . '/views/auth/login.php';
    }

    // ----------------------------------------------------------------
    // GET /index.php?c=auth&a=register
    // POST: validate, create customer, redirect to login
    // ----------------------------------------------------------------
    public function register() {
        $errors = array();
        $old    = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name             = isset($_POST['name'])             ? trim($_POST['name'])             : '';
            $email            = isset($_POST['email'])            ? trim($_POST['email'])            : '';
            $phone            = isset($_POST['phone'])            ? trim($_POST['phone'])            : '';
            $password         = isset($_POST['password'])         ? $_POST['password']               : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password']       : '';

            $old['name']  = sanitize($name);
            $old['email'] = sanitize($email);
            $old['phone'] = sanitize($phone);

            // Name
            if ($name === '') {
                $errors['name'] = 'Full name is required.';
            }

            // Email
            if ($email === '') {
                $errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Enter a valid email address.';
            }

            // Phone – exactly 11 digits
            if ($phone === '') {
                $errors['phone'] = 'Phone number is required.';
            } elseif (!preg_match('/^\d{11}$/', $phone)) {
                $errors['phone'] = 'Phone number must be exactly 11 digits.';
            }

            // Password
            if ($password === '') {
                $errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            }

            // Confirm password
            if ($confirm_password === '') {
                $errors['confirm_password'] = 'Please confirm your password.';
            } elseif ($password !== $confirm_password) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }

            // Unique email
            if (empty($errors['email'])) {
                $existing = user_get_by_email($this->conn, $email);
                if ($existing) {
                    $errors['email'] = 'This email address is already registered.';
                }
            }

            if (empty($errors)) {
                $data = array(
                    'name'          => $name,
                    'email'         => $email,
                    'phone'         => $phone,
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'role'          => 'customer',
                );
                $new_id = user_create($this->conn, $data);

                if ($new_id) {
                    set_flash('success', 'Registration successful! Please log in.');
                    redirect(BASE_URL . '?c=auth&a=login');
                } else {
                    $errors['register'] = 'Registration failed. Please try again.';
                }
            }
        }

        include APP . '/views/auth/register.php';
    }

    // ----------------------------------------------------------------
    // GET /index.php?c=auth&a=sellerRegister
    // POST: validate, create user (seller) + seller record
    // ----------------------------------------------------------------
    public function sellerRegister() {
        $errors = array();
        $old    = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name             = isset($_POST['name'])             ? trim($_POST['name'])             : '';
            $email            = isset($_POST['email'])            ? trim($_POST['email'])            : '';
            $phone            = isset($_POST['phone'])            ? trim($_POST['phone'])            : '';
            $password         = isset($_POST['password'])         ? $_POST['password']               : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password']       : '';
            $shop_name        = isset($_POST['shop_name'])        ? trim($_POST['shop_name'])        : '';
            $shop_description = isset($_POST['shop_description']) ? trim($_POST['shop_description']) : '';
            $address          = isset($_POST['address'])          ? trim($_POST['address'])          : '';

            $old['name']             = sanitize($name);
            $old['email']            = sanitize($email);
            $old['phone']            = sanitize($phone);
            $old['shop_name']        = sanitize($shop_name);
            $old['shop_description'] = sanitize($shop_description);
            $old['address']          = sanitize($address);

            // User fields
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
            if ($password === '') {
                $errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            }
            if ($confirm_password === '') {
                $errors['confirm_password'] = 'Please confirm your password.';
            } elseif ($password !== $confirm_password) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }

            // Seller fields
            if ($shop_name === '') {
                $errors['shop_name'] = 'Shop name is required.';
            }
            if ($shop_description === '') {
                $errors['shop_description'] = 'Shop description is required.';
            }
            if ($address === '') {
                $errors['address'] = 'Shop address is required.';
            }

            // Unique email
            if (empty($errors['email'])) {
                $existing = user_get_by_email($this->conn, $email);
                if ($existing) {
                    $errors['email'] = 'This email address is already registered.';
                }
            }

            // Logo upload (optional)
            $logo_path = '';
            if (isset($_FILES['shop_logo']) && $_FILES['shop_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['shop_logo'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors['shop_logo'] = 'File upload failed. Please try again.';
                } else {
                    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
                    $max_size      = 2 * 1024 * 1024; // 2 MB

                    $finfo     = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if (!in_array($mime_type, $allowed_types)) {
                        $errors['shop_logo'] = 'Only JPG, PNG, and GIF images are allowed.';
                    } elseif ($file['size'] > $max_size) {
                        $errors['shop_logo'] = 'Image size must not exceed 2 MB.';
                    } else {
                        $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename  = 'logo_' . uniqid() . '.' . strtolower($ext);
                        $dest_dir  = UPLOAD_PATH . 'shop_logos/';
                        $dest_path = $dest_dir . $filename;

                        if (!is_dir($dest_dir)) {
                            mkdir($dest_dir, 0755, true);
                        }

                        if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
                            $errors['shop_logo'] = 'Could not save the uploaded file.';
                        } else {
                            $logo_path = 'shop_logos/' . $filename;
                        }
                    }
                }
            }

            if (empty($errors)) {
                $user_data = array(
                    'name'          => $name,
                    'email'         => $email,
                    'phone'         => $phone,
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'role'          => 'seller',
                );
                $user_id = user_create($this->conn, $user_data);

                if ($user_id) {
                    // Mark user as active immediately; seller approval is separate
                    $seller_data = array(
                        'user_id'          => $user_id,
                        'shop_name'        => $shop_name,
                        'shop_description' => $shop_description,
                        'shop_logo_path'   => $logo_path,
                        'address'          => $address,
                    );
                    $seller_id = seller_create($this->conn, $seller_data);

                    if ($seller_id) {
                        set_flash('info', 'Your seller application has been submitted and is awaiting admin approval.');
                        redirect(BASE_URL . '?c=auth&a=pendingSeller');
                    } else {
                        $errors['register'] = 'Could not create seller profile. Please try again.';
                    }
                } else {
                    $errors['register'] = 'Registration failed. Please try again.';
                }
            }
        }

        include APP . '/views/auth/seller_register.php';
    }

    // ----------------------------------------------------------------
    // GET /index.php?c=auth&a=pendingSeller
    // ----------------------------------------------------------------
    public function pendingSeller() {
        include APP . '/views/auth/pending_seller.php';
    }

    // ----------------------------------------------------------------
    // GET /index.php?c=auth&a=logout
    // ----------------------------------------------------------------
    public function logout() {
        session_destroy();
        redirect(BASE_URL . '?c=auth&a=login');
    }
}
