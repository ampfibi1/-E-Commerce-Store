# Contributing to ShopHub (E-Commerce Store)

Group project for **P04 – E-Commerce Store**. This guide locks in the rules every
collaborator must follow so our work merges cleanly and the assignment passes
the rubric in `final_project.md`.

> **Read `final_project.md` first.** That document is the assignment spec
> (roles, required features, DB schema, technical requirements). This file is
> the operational guide for *how* we work together.

---

## 1. Team & Role Split

The spec defines **four roles**. Each group member owns one or two of them and
is solely responsible for their slice of the codebase, data, and demo.

| Role | Files / Areas | Owner |
|---|---|---|
| Customer       | `app/controllers/CustomerController.php`, `app/views/customer/*` | sakib2588 |
| Seller / Vendor | `app/controllers/SellerController.php`, `app/views/seller/*`     | sakib2588 |
| Delivery Manager | `app/controllers/DeliveryController.php`, `app/views/delivery/*` *(to add)* | TBD |
| Platform Admin   | `app/controllers/AdminController.php`, `app/views/admin/*` *(to add)*       | TBD |

Per spec: **"DO NOT rely on any other group member for anything, including DB
table creation, data insertion, session management."** Each member seeds their
own demo data.

---

## 2. Local Setup (XAMPP / LAMPP)

1. Clone into the web root:
   ```bash
   git clone git@github.com:ampfibi1/-E-Commerce-Store.git /opt/lampp/htdocs/ecommerce
   ```
2. Start Apache + MySQL from XAMPP.
3. Import the schema (everyone uses the same schema):
   ```bash
   /opt/lampp/bin/mysql -u root < /opt/lampp/htdocs/ecommerce/database.sql
   ```
4. Open: `http://localhost/ecommerce/public/`
5. Default dev DB config: `config/db.php` (host=localhost, user=root, pass=empty,
   db=`ecommerce_db`). **Do not** commit real credentials.

---

## 3. Project Layout

```
ecommerce/
├── ajax/             # AJAX endpoints — XMLHttpRequest targets, return JSON only
├── app/
│   ├── controllers/  # One class per role: <Role>Controller
│   ├── models/       # Procedural functions: <entity>_<verb>($conn, ...)
│   └── views/        # PHP templates rendered by controllers
│       ├── auth/
│       ├── customer/
│       ├── seller/
│       └── layouts/  # header.php / footer.php shared by all views
├── config/db.php     # DB connection + global helpers
├── public/           # WEB ROOT — only this directory is served
│   ├── index.php     # Front controller / router
│   ├── css/style.css # Single stylesheet — append-only
│   ├── js/validation.js
│   └── uploads/      # user content (gitignored)
└── database.sql      # Schema for all 16 tables (per final_project.md)
```

**Web root = `public/`**, not the project root.

---

## 4. Architecture Rules (Non-Negotiable)

These come from `final_project.md` "Technical Requirements" plus the team's
extra constraints. Breaking any of these = your PR gets bounced.

| # | Rule |
|---|---|
| 1 | **MVC pattern.** Models, Views, Controllers are separate. No business logic in views — views only render `$data` passed by the controller. |
| 2 | **PHP + MySQL** server-side only. No Node, no Python. |
| 3 | **mysqli — procedural style only.** No OOP `$mysqli->query(...)`. Use `mysqli_prepare`, `mysqli_stmt_bind_param`, `mysqli_stmt_execute`, `mysqli_stmt_get_result`, `mysqli_stmt_close`. |
| 4 | **Prepared statements always.** Zero raw string interpolation in SQL. Reviewers will reject any `"... WHERE id = $id"`. |
| 5 | **PHP sessions** for auth. Every protected page calls `require_login()` / `require_role('customer'\|'seller'\|...)` *before* any output. Session keys: `uid`, `role`, `uname`, `sid` (seller id), `cart`. |
| 6 | **AJAX uses `XMLHttpRequest`.** ❌ no `fetch()`, ❌ no `axios`, ❌ no jQuery `$.ajax`. Endpoint returns JSON via `echo json_encode(...)`. |
| 7 | **Legacy JavaScript.** Use `var` only (no `let`/`const`). No arrow functions. No template literals. No `Promise`/`async`/`await`. ES5 only. |
| 8 | **Semi-legacy PHP.** PHP 7-compatible style: `array(...)` literals OK, short arrays OK, but no PHP 8 features (no named args, no `match`, no enums, no readonly). |
| 9 | **No HTML5 form validation. This is an order.** ❌ No `required`, no `type="email"`, no `type="tel"`, no `type="number"`, no `type="date"`, no `pattern=`, no `min=`, no `max=`, no `minlength=`, no `maxlength=`. **Use `<input type="text">` for everything textual.** Every form must carry the `novalidate` attribute. |
| 10 | **Two validation layers, both required.** Client-side in `public/js/validation.js` (returns true/false from `onsubmit`). Server-side in the controller (builds an `$errors` array, re-renders the form with errors). |
| 11 | **One AJAX feature per role minimum** (per spec). Already implemented for Customer + Seller — see `ajax/`. |
| 12 | **No JS or CSS frameworks.** No Bootstrap, Tailwind, React, Vue, jQuery. Hand-rolled CSS in `public/css/style.css`. Hand-rolled JS in `public/js/validation.js` + inline `<script>` blocks at the bottom of views. |

---

## 5. Routing

Front controller: `public/index.php`. URLs:

```
?c=<controller>&a=<action>
```

Examples:
- `?c=customer&a=products` → `CustomerController->products()`
- `?c=seller&a=dashboard`  → `SellerController->dashboard()`
- `?c=auth&a=login`        → `AuthController->login()`

Add a page: add a method to the right controller class and a view at
`app/views/<role>/<action>.php`.

---

## 6. Code Patterns (Copy These Exactly)

### Model function
```php
function user_get_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row;
}
```

### Controller action
```php
public function profile() {
    require_role('customer');
    $uid    = (int)$_SESSION['uid'];
    $errors = array();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ... validate POST, populate $errors ...
        if (empty($errors)) {
            // ... call model ...
            set_flash('success', 'Profile updated.');
            redirect(BASE_URL . '?c=customer&a=profile');
        }
    }
    include APP . '/views/customer/profile.php';
}
```

### View skeleton
```php
<?php $page_title = 'Page Title'; include APP . '/views/layouts/header.php'; ?>
<section class="container">
    <form method="POST" action="<?php echo BASE_URL; ?>?c=customer&a=foo"
          id="fooForm" novalidate onsubmit="return validateFooForm()">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?php echo sanitize(isset($old['name']) ? $old['name'] : ''); ?>">
            <span class="err" id="err_name">
                <?php echo isset($errors['name']) ? sanitize($errors['name']) : ''; ?>
            </span>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</section>
<?php include APP . '/views/layouts/footer.php'; ?>
```

### AJAX endpoint (`ajax/<name>.php`)
```php
<?php
session_start();
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/db.php';
require_once APP . '/models/XModel.php';

if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(array('success' => false, 'message' => 'Login required.'));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Method not allowed.'));
    exit;
}
// ... validate input, call model, echo json_encode(...), exit;
```

### XMLHttpRequest from a view (legacy JS)
```js
<script>
function toggleWishlist(productId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo BASE_URL; ?>../ajax/wishlist_toggle.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            if (data.success) {
                // update DOM
            } else {
                alert(data.message);
            }
        }
    };
    xhr.send('product_id=' + encodeURIComponent(productId));
}
</script>
```

---

## 7. UI / UX Rules

### Brand
- **Name:** ShopHub
- **Primary:** `#2c7be5` (blue)
- **Accent:**  `#e74c3c` (red)
- **Background:** `#f5f7fa`

### Stylesheet — one file, append-only
- All CSS lives in `public/css/style.css`. **Do not create a second `.css` file.**
- Add new rules **at the bottom**, under a labeled section:
  ```css
  /* --- Delivery Dashboard (added by <yourname>) ------------ */
  .delivery-card { ... }
  ```
- **Do not rename or restyle** existing classes — others rely on them.

### Reusable classes (use these, don't reinvent)
| Class | Purpose |
|---|---|
| `.container`, `.main-wrap` | Page wrappers |
| `.navbar`, `.nav-link`, `.nav-link.active` | Top nav |
| `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger` | Buttons |
| `.card` | Bordered content panel |
| `.flash-success`, `.flash-error`, `.flash-info`, `.flash-warning` | Flash messages — set via `set_flash($type, $msg)` |
| `.form-group`, `.form-control`, `.err` | Form fields + inline error span |
| `.table` | Data tables |
| `.badge`, `.badge-success`, `.badge-danger` | Status pills |

### Pages must use `header.php` + `footer.php`
Every view starts with `include APP . '/views/layouts/header.php';` and ends
with `include APP . '/views/layouts/footer.php';`. Don't render your own
`<html>` / `<head>` / `<body>`.

### Escape everything dynamic
```php
<?php echo sanitize($value); ?>
```

---

## 8. Database

- **Single source of truth:** `database.sql` (16 tables, matches the spec in
  `final_project.md`).
- Schema changes must be:
  1. Reflected in `database.sql` in the same PR.
  2. Announced in the PR description — teammates must re-run the import.
- Conventions:
  - Tables and columns: `snake_case`.
  - Primary key: `id INT AUTO_INCREMENT PRIMARY KEY`.
  - Foreign keys: `<entity>_id` (`user_id`, `product_id`, `seller_id`).
  - Timestamps: `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`.

### Per-member data isolation
Per spec: each group member inserts only the data needed to demonstrate their
own role. Don't pollute another member's tables with test data.

---

## 9. Git Workflow

### Identity
Each member configures their **own** git identity locally before committing:
```bash
git config user.name  "<your-github-username>"
git config user.email "<your-github-email>"
```

### Branch naming
```
<your-handle>/<feature-or-fix-slug>
```
Examples: `sakib/cart-ajax-flow`, `<teammate>/delivery-zones-crud`.

### Rules
1. **Never push directly to `main`.** Per spec: "Do not push to main/master
   branch directly. Create feature branches and submit a Pull Request for each
   major feature."
2. **Never force-push** any shared branch.
3. **Pull before every session:** `git pull --ff-only origin main`.
4. **Small, scoped PRs.** One feature per branch.
5. **Append in shared files** (`style.css`, `database.sql`, `header.php` nav)
   rather than inserting in the middle — this is the #1 source of merge
   conflicts in this project.
6. When `main` advances while your branch is open, **merge** (don't rebase):
   ```bash
   git fetch origin
   git merge origin/main
   # resolve conflicts -> commit -> push
   ```

### PR checklist (paste into every PR description)
- [ ] Cloned fresh in a clean XAMPP install and tested locally
- [ ] No `fetch()`; all AJAX uses `XMLHttpRequest`
- [ ] No HTML5 validation attributes (`required`, `type="email"`, `pattern`, etc.); all forms have `novalidate`
- [ ] Both JS (`validation.js`) and PHP (controller) validation present for every user-input form
- [ ] All DB calls use `mysqli_prepare` + `bind_param`
- [ ] Session/role check at the top of every protected action
- [ ] No new CSS files / no inline `style="..."` (use existing classes)
- [ ] No JS/CSS framework added
- [ ] If schema changed, `database.sql` updated and teammates notified
- [ ] No secrets, no `*.env`, no large binaries

---

## 10. What NOT to Do

- ❌ `fetch()`, `axios`, `jQuery.ajax`
- ❌ HTML5 validation attributes (this is an order)
- ❌ ES6+ JS (`let`, `const`, `=>`, template literals, `Promise`, `async`/`await`)
- ❌ PHP 8 features (`match`, named args, enums, readonly, `str_contains`)
- ❌ OOP mysqli (`$mysqli->query(...)`) — use procedural only
- ❌ Raw SQL string interpolation
- ❌ React, Vue, Bootstrap, Tailwind, jQuery
- ❌ New CSS files or inline styles
- ❌ Renaming existing CSS classes, model functions, or controller methods
- ❌ Force-pushing or pushing to `main`
- ❌ Inserting your own demo data into another member's tables

---

## 11. Submission Gate (from spec)

Before submitting, every member confirms:
- [ ] Role pages individually accessible with own login and dashboard
- [ ] Role-based access control prevents cross-role access
- [ ] Shared DB schema implemented consistently
- [ ] At least one AJAX feature per role
- [ ] All forms have server-side validation with descriptive error messages
- [ ] Git history shows feature branches and PRs for major features
- [ ] Hardcopy report describes all features for your assigned role

---

Questions, design proposals, or rule clarifications: open a **draft PR** and tag
the team. Decisions are recorded in PR threads, not in DMs.
