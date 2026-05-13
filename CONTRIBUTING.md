# Contributing to ShopHub (E-Commerce Store)

This guide is for the 3-person team working on this repo. Follow it so our
UI/UX stays consistent and our PRs merge without conflicts.

---

## 1. Local Setup

1. Clone into your XAMPP/LAMPP `htdocs`:
   ```bash
   git clone git@github.com:ampfibi1/-E-Commerce-Store.git /opt/lampp/htdocs/ecommerce
   ```
2. Import the schema:
   ```bash
   /opt/lampp/bin/mysql -u root < /opt/lampp/htdocs/ecommerce/database.sql
   ```
3. Start XAMPP, then visit: `http://localhost/ecommerce/public/`
4. Default dev DB config lives in `config/db.php` (host=localhost, user=root, pass=empty,
   db=`ecommerce_db`). **Do not** commit production credentials here — use environment
   defaults only.

---

## 2. Project Layout (Memorize This)

```
ecommerce/
├── api/              # AJAX endpoints — return JSON only
├── app/
│   ├── controllers/  # One class per role: AuthController, CustomerController, SellerController
│   ├── models/       # Procedural functions: <entity>_<verb>($conn, ...)
│   └── views/        # PHP templates rendered by controllers
│       ├── auth/
│       ├── customer/
│       ├── seller/
│       └── layouts/  # header.php / footer.php shared by all views
├── config/db.php     # DB connection + global helpers (sanitize, redirect, require_role, etc.)
├── public/           # Web root — only this directory is served
│   ├── index.php     # Front controller / router
│   ├── css/style.css # SINGLE stylesheet — do not add new .css files
│   ├── js/validation.js
│   └── uploads/      # user content (gitignored)
└── database.sql      # full schema + seed
```

**Web root = `public/`**, not the project root. All URLs go through
`public/index.php`.

---

## 3. Routing Convention

Front controller is `public/index.php`. URLs look like:

```
?c=<controller>&a=<action>
```

- `c=customer&a=products` → `CustomerController->products()`
- `c=seller&a=dashboard`  → `SellerController->dashboard()`
- `c=auth&a=login`        → `AuthController->login()`

**To add a new page**: add a public method to the right controller class,
and a view at `app/views/<role>/<action>.php`. The router auto-finds it.

---

## 4. Coding Conventions (Match These Exactly)

### Models — procedural, not OOP
Every model is a flat file of functions named `<entity>_<verb>`:
```php
function user_get_by_id($conn, $id)     { ... }
function product_get_by_seller($conn, $sid) { ... }
function cart_add($product_id, $qty)    { ... }
```
- Always pass `$conn` as the first argument when the function touches the DB.
- Always use `mysqli_prepare` + `bind_param` — **never** interpolate user input
  into SQL strings.
- Close every prepared statement with `mysqli_stmt_close($stmt)`.
- Return associative arrays for single rows, arrays of arrays for lists,
  `false`/`null` on miss.

### Controllers — class-based
```php
class <Role>Controller {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }
    public function <action>() { /* ... */ }
}
```
- First line of any protected action: `require_role('customer'|'seller'|'admin')`.
- Validate POST, build `$errors` array, set `set_flash('error'|'success'|'info', $msg)`,
  then `include APP . '/views/<role>/<action>.php'` or `redirect(BASE_URL . '?c=...')`.

### Views — server-rendered PHP
- Wrap every view in:
  ```php
  <?php $page_title = 'Page Title'; include APP . '/views/layouts/header.php'; ?>
    <!-- page content -->
  <?php include APP . '/views/layouts/footer.php'; ?>
  ```
- Escape every dynamic value: `<?php echo sanitize($value); ?>`.
- Never write `<style>` blocks or inline `style="..."` — use the existing CSS classes
  (see §5).

### AJAX endpoints — `api/*.php`
Every endpoint follows this template:
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
// ... validate, call model, echo json_encode(...), exit;
```
- **Always** return JSON with at least `{success: bool, message: string}`.
- **Never** mix HTML output and JSON in the same endpoint.

---

## 5. UI/UX Rules (so all three of us produce the same look)

### Brand
- **Name:** ShopHub
- **Primary color:** `#2c7be5` (blue)
- **Accent color:** `#e74c3c` (red)
- **Background:** `#f5f7fa`
- All colors live as raw hex in `public/css/style.css`. **Do not introduce new
  brand colors** without team agreement.

### Stylesheet — single file
- Only one stylesheet: `public/css/style.css`.
- Add new styles **at the bottom** of `style.css` under a clearly labeled section,
  e.g.:
  ```css
  /* --- Disputes Page (added by <yourname>) ------------------ */
  .dispute-card { ... }
  ```
- **Do not** rename existing classes — other teammates may rely on them.
- **Do not** add a second CSS file or use a CSS framework (Bootstrap/Tailwind).
  Hand-rolled CSS only, to match the existing aesthetic.

### Required reusable classes (already defined — use them)
| Class | Use for |
|---|---|
| `.container` | page wrapper (max-width centered) |
| `.navbar`, `.nav-link`, `.nav-link.active` | top nav |
| `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger` | all buttons |
| `.card` | bordered content panel |
| `.flash-success`, `.flash-error`, `.flash-info`, `.flash-warning` | flash messages (use `set_flash()`) |
| `.form-group`, `.form-control`, `.form-error` | forms |
| `.table` | data tables |
| `.badge`, `.badge-success`, `.badge-danger` | status pills |

If you need something that doesn't exist, **add a class** rather than inline styles.

### Page skeleton (use for every new page)
```php
<?php $page_title = 'My Page'; include APP . '/views/layouts/header.php'; ?>
<section class="container">
    <h1>My Page</h1>
    <!-- content using existing utility classes -->
</section>
<?php include APP . '/views/layouts/footer.php'; ?>
```

### JS conventions
- All client-side validation lives in `public/js/validation.js`.
- AJAX calls use vanilla `fetch()` with `FormData`. Pattern:
  ```js
  fetch(BASE_URL + 'api/cart_action.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => { /* update DOM based on data.success / data.message */ });
  ```
- **Do not** add jQuery or any other JS dependency.

---

## 6. Database

- Schema is the single source of truth: `database.sql`.
- **Any schema change** (new table, new column, altered type) must:
  1. Be reflected in `database.sql` in the same PR.
  2. Be announced in the PR description so teammates re-run the import.
- Use `snake_case` for tables and columns. Primary key is always `id INT AUTO_INCREMENT`.
- Foreign keys: `<entity>_id` (e.g., `user_id`, `product_id`, `seller_id`).
- Always add `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP` and, where edits happen,
  `updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`.

---

## 7. Git Workflow (Conflict-Free Collaboration)

### Branch naming
```
<your-handle>/<feature-or-fix-slug>
```
Examples: `sakib/checkout-flow`, `<teammate>/seller-dashboard-fix`.

**Never** push directly to `main`. **Never** force-push any shared branch.

### Day-to-day flow
```bash
git checkout main
git pull --ff-only origin main
git checkout -b <yourname>/<slug>
# ... do work ...
git add <specific files>     # avoid `git add .`
git commit -m "short imperative summary"
git push -u origin <yourname>/<slug>
# open a PR on GitHub targeting main
```

### To avoid conflicts
1. **Pull before you start every session**: `git pull --ff-only origin main`.
2. **Keep PRs small and scoped** — one feature or fix per branch.
3. **Don't reformat existing files** in unrelated PRs (no whitespace-only edits).
4. **Add at the end** of shared files (`style.css`, `database.sql`, `header.php` nav)
   rather than inserting in the middle.
5. **Lock-step the schema**: if you change `database.sql`, ping the team first.
6. When `main` advances while your branch is open:
   ```bash
   git checkout <yourbranch>
   git fetch origin
   git merge origin/main      # use merge, not rebase, on shared branches
   # resolve conflicts -> commit -> push
   ```

### PR checklist (paste into PR description)
- [ ] Tested locally on `http://localhost/ecommerce/public/` for all relevant roles
- [ ] No new CSS files / no inline styles / no new JS libs
- [ ] Used existing `.btn`, `.card`, `.form-control`, `.flash-*` classes
- [ ] All DB queries use `mysqli_prepare` + `bind_param`
- [ ] If schema changed, `database.sql` updated and teammates notified
- [ ] No secrets, no `*.env`, no large binaries committed

---

## 8. Roles & Ownership (suggested split — adjust as a team)

| Area | Files | Default owner |
|---|---|---|
| Customer flows | `CustomerController.php`, `app/views/customer/*` | TBD |
| Seller flows   | `SellerController.php`, `app/views/seller/*`     | TBD |
| Auth & shared  | `AuthController.php`, `config/db.php`, `layouts/*` | TBD |
| Styling        | `public/css/style.css` (append-only)             | shared — coordinate before large edits |
| DB schema      | `database.sql`                                   | shared — coordinate before any change |

Fill in the names once decided so everyone knows who to ping during review.

---

## 9. What NOT to Do

- Don't introduce a JS framework (React/Vue) or CSS framework (Bootstrap/Tailwind).
- Don't switch from procedural model functions to OOP models — stay consistent.
- Don't add new colors outside the brand palette.
- Don't rename existing CSS classes, controller methods, or model functions.
- Don't commit `public/uploads/*`, `.env`, IDE config, or anything in `.gitignore`.
- Don't force-push to `main` or anyone else's branch.

---

Questions or proposals? Open a draft PR and tag the team — we discuss in the PR,
not in DMs, so decisions are recorded.
