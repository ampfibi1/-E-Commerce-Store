# ShopHub — Team Integration Guide

> **Audience:** 3 group members building the E-Commerce Store final project.
> **Status:** Living document. Open a PR if you want to change any rule.
> **Last updated:** 2026-05-14

This document is the single source of truth for how the four roles plug into one working project. If your code doesn't follow what's here, it won't integrate.

---

## 1. Roles & owners

| Role | Owner | Branch prefix | Status |
|---|---|---|---|
| 1. Customer | **sakib2588** | `sakib/...` | ✅ Merged via PR #5 |
| 2. Seller / Vendor | **sakib2588** | `sakib/...` | ✅ Merged via PR #5 |
| 3. Delivery Manager | **claimed** | `delivery/...` | 🚧 Implemented locally, separate PR in flight |
| 4. Platform Admin | **ampfibi1** | `feature/...` | ✅ Merged via PR #6 |

> **One member has 2 roles (Customer + Seller). The other two each own 1 role.** Delivery Manager is currently unassigned — someone must take it for the project to be complete.

---

## 2. Hard rules from `final_project.md` (non-negotiable)

These come straight from the assignment spec. Breaking any of these will cost marks:

1. **MVC pattern** — separate Models, Views, Controllers. No business logic in views.
2. **PHP + MySQL** only.
3. **`mysqli` with prepared statements** — NO raw string interpolation in SQL. ⚠️ The current `model/logModel.php` violates this (`SELECT ... WHERE email = '$email'`). Must be fixed before submission.
4. **PHP sessions** with role-based access control on every protected page.
5. **At least one AJAX feature per role**, using **`XMLHttpRequest`** and returning JSON from a PHP endpoint.
6. **Server-side validation** on every form, with descriptive error messages.
7. **Git workflow** — feature branches + PR for every major feature. No direct pushes to `main`.
8. **Each member inserts ONLY the data needed to demo their own role.**

---

## 3. Project rules (team-agreed, in addition to the MD)

These are team conventions — pick one approach and stick to it:

1. **MVC structure with role subfolders:** every role gets its own folder inside `app/controllers/`, `app/models/`, and `app/views/`. Truly shared code (Auth, Info, Product, Order, Cart, etc.) lives in `SharedController/` / `SharedModel/`. See §4 for the exact tree.
2. **Front controller:** all requests go through `public/index.php?c=Controller&a=action`. No direct `*.php` file access.
3. **No HTML5 validation attributes** (`required`, `pattern`, `minlength`, `type="email"`). All validation in JS + PHP only.
4. **MySQLi procedural style** (`mysqli_prepare`, `mysqli_stmt_bind_param`) — not OOP MySQLi.
5. **Password hashing** — `password_hash()` on register, `password_verify()` on login. Never store plaintext.
6. **Single CSS file:** `public/css/style.css`. Don't fork per-role CSS files.
7. **Icons:** use the inline SVG helpers in `app/views/layouts/icons.php`.

---

## 4. Architecture — how the 4 roles plug together

```
ecommerce/
├── public/
│   ├── index.php           ← ONE front controller for the whole app
│   ├── .htaccess
│   ├── css/style.css       ← shared
│   ├── js/
│   │   ├── validation.js   ← legacy JS + XMLHttpRequest (MD-required)
│   │   └── interactions.js ← modern vanilla JS (toasts, mobile nav)
│   └── uploads/            ← user-uploaded files (incl. product_images/)
├── app/
│   ├── controllers/
│   │   ├── SharedController/        (sakib) — AuthController, InfoController
│   │   ├── Customer_controller/     (sakib) — CustomerController.php
│   │   ├── Seller_controller/       (sakib) — SellerController.php
│   │   ├── Admin_controller/        (ampfibi1) — AdminController.php dispatcher + flat action scripts
│   │   └── Delivery_controller/     (delivery owner) — DeliveryController.php
│   ├── models/
│   │   ├── SharedModel/             (sakib) — ProductModel, OrderModel, CartModel, CategoryModel, CouponModel, UserModel, ReturnModel, DeliveryZoneModel, connection.php, logModel.php
│   │   ├── Customer_model/          (sakib) — AddressModel, CartModel, ReviewModel, WishlistModel
│   │   ├── Seller_model/            (sakib) — SellerModel
│   │   ├── Admin_model/             (ampfibi1) — analyticsModel, couponsModel, dashboardModel, etc.
│   │   └── Delivery_model/          (delivery owner) — DeliveryAgentModel, DeliveryAssignmentModel, DeliveryReportModel, DeliveryZoneModel
│   └── views/
│       ├── layouts/        (header/footer/icons — shared)
│       ├── auth/           (login, register — shared)
│       ├── info/           (contact, help, terms — shared)
│       ├── customer/       (sakib)
│       ├── seller/         (sakib)
│       ├── admin/          (ampfibi1)
│       └── delivery/       (delivery owner)
├── ajax/                   ← AJAX endpoints, one file per action, named ajax/<role>_<action>.php
├── config/db.php           ← ONE database connection helper + auth/role helpers
├── shema.sql               ← canonical schema (do not modify directly)
├── shema2.0.sql            ← updated schema for fresh setups
├── migrations/             ← additive schema changes (numbered .sql files)
└── seed_demo.sql           ← optional demo data
```

**Router resolution.** `public/index.php` holds a `$controller_class_map` that maps each `?c=<role>` slug to its controller-class + file path. When a teammate adds a new role, they append one entry to that map. Models are loaded recursively from `app/models/*/` so new role subfolders are picked up automatically.

**SharedController & SharedModel ownership.** sakib maintains the Shared folders since they back Customer + Seller. Other roles may *use* shared code (e.g. `connection.php`, `OrderModel`) but should not modify it without coordinating.

> **Resolved:** the old top-level `controller/`, `model/`, and `views/admin/` directories are being removed by the Admin port. The root `index.php` has already been deleted — `public/index.php` is the single front controller as the rule requires.

---

## 5. Database — single source of truth

- **Canonical schema:** `shema.sql` (original team schema, do not modify directly).
- **Updated schema:** `shema2.0.sql` (includes Customer/Seller additions — use this for fresh setups).
- **Database name:** `ecommerce` (NOT `ecommerce_db`, NOT `ecommerce_store`, etc.).
- **Additive changes:** add a new file in `migrations/`, numbered sequentially (e.g. `003_add_xyz.sql`).

### Fresh setup (no existing database):

```bash
# Drop any old DB with the wrong name
mysql -u root -e "DROP DATABASE IF EXISTS ecommerce; DROP DATABASE IF EXISTS ecommerce_db;"

# Import the updated schema — one command, everything included
mysql -u root < shema2.0.sql

# Optional: seed demo products and users for testing
mysql -u root ecommerce < seed_demo.sql
```

### Manual migration helper (already have the database):

If you already imported `shema.sql` and just need to apply the new columns/tables, run only the migrations that are missing from your database:

```bash
# Check which tables you already have
mysql -u root ecommerce -e "SHOW TABLES;"
mysql -u root ecommerce -e "SHOW COLUMNS FROM orders;"

# Migration 001 — adds customer_addresses table (Customer address book feature)
mysql -u root ecommerce < migrations/001_add_customer_addresses.sql

# Migration 002 — adds zone_id + delivery_fee to orders (Customer checkout feature)
mysql -u root ecommerce < migrations/002_add_zone_delivery_fee_to_orders.sql
```

All migration files use `CREATE TABLE IF NOT EXISTS` or `ALTER TABLE ADD COLUMN IF NOT EXISTS` so they are **safe to run even if partially applied** — they will not error if the column already exists.

After setup, `config/db.php` should connect successfully with:
```php
DB_HOST = localhost  |  DB_USER = root  |  DB_PASS = ''  |  DB_NAME = ecommerce
```

---

## 6. Session conventions (agreed keys)

All four roles must use the SAME session keys. Mixed naming will break shared header/role checks.

| Key | Type | Set when | Used for |
|---|---|---|---|
| `$_SESSION['uid']` | int | login | user.id |
| `$_SESSION['uname']` | string | login | user.name (for greeting) |
| `$_SESSION['role']` | string | login | one of: `customer`, `seller`, `delivery_manager`, `admin` |
| `$_SESSION['sid']` | int | login (sellers only) | sellers.id (the row in `sellers`, not users) |
| `$_SESSION['flash']` | array | one-time message | `['type' => 'success', 'msg' => '...']` |
| `$_SESSION['cart']` | array | customer adds to cart | `[product_id => qty, ...]` |

> ⚠️ The current admin code uses `user_id` / `user_name` / `user_role`. **ampfibi1 must rename these to `uid` / `uname` / `role`** when porting to `AdminController`.

### Helpers in `config/db.php` (use these, don't roll your own):

- `require_login()` — redirects to login if no session.
- `require_role('admin')` — redirects if user is not that role.
- `require_seller_approved($conn)` — blocks unapproved sellers.
- `set_flash('success', '...')` / `get_flash()` — one-time toast messages.
- `sanitize($str)` — `htmlspecialchars` wrapper for output.
- `redirect($url)` — header + exit.

---

## 7. URL convention (front controller routing)

```
BASE_URL/public/?c=<controller>&a=<action>[&extra=...]

Customer routes:        ?c=customer&a=products
Seller routes:          ?c=seller&a=dashboard
Admin routes:           ?c=admin&a=dashboard
Delivery routes:        ?c=delivery&a=dashboard
Auth (shared):          ?c=auth&a=login | register | sellerRegister | logout
```

> Method names use **camelCase** (`sellerRegister`, not `seller_register`). The router strips non-alphanumerics so URL params must match the PHP method name exactly.

---

## 8. AJAX endpoints

Each role must have **at least one** AJAX feature using `XMLHttpRequest` and returning JSON.

### Convention:
- File location: `ajax/<role>_<action>.php`
- Must `session_start()`, check auth, return `header('Content-Type: application/json')`, then `echo json_encode([...])`.
- Use `mysqli_prepare` for any DB query (same rule as everywhere).

### Existing examples (Customer + Seller — copy this style):

| File | Role | Purpose |
|---|---|---|
| `ajax/cart_action.php` | Customer | Add/remove/update cart items |
| `ajax/wishlist_toggle.php` | Customer | Heart icon on product cards |
| `ajax/validate_coupon.php` | Customer | Apply coupon at checkout |
| `ajax/order_status.php` | Customer | Poll order status badge |
| `ajax/toggle_coupon.php` | Seller | Activate/deactivate coupons |
| `ajax/seller_reply.php` | Seller | Reply to reviews |
| `ajax/review_delete.php` | Customer | Delete own review |

### Admin & Delivery — must add at least one each:

Suggestions:
- **Admin:** AJAX search suggestion as you type in user/product search.
- **Delivery:** AJAX agent assignment dropdown (load available agents for a given zone).

---

## 9. Validation

### JS validation — `public/js/validation.js` (legacy style)
- Uses `var`, `XMLHttpRequest`, no arrow functions.
- One function per form, named `validate<FormName>Form()`.
- Add `onsubmit="return validateXForm()"` to every `<form>`.

### PHP validation — in the controller, before any DB call
- Use `$errors = array();` to collect.
- `trim()` every input.
- Use `filter_var($email, FILTER_VALIDATE_EMAIL)`, `strlen()` for length, `is_numeric()` for numbers.
- If `!empty($errors)`, re-render the form with `$errors` and `$old` (so user input isn't lost).

---

## 10. Per-role feature checklists

### Role 1 — Customer (sakib2588) ✅ Done
- [x] Register / Login / Logout
- [x] Profile + saved addresses
- [x] Browse, search, filter products
- [x] Cart (session-based)
- [x] Coupon validation (AJAX)
- [x] Checkout + place order
- [x] Order status tracking (AJAX polling)
- [x] Order history, return requests
- [x] Reviews (1–5 stars + text)
- [x] Wishlist
- [x] Submit + view disputes

### Role 2 — Seller / Vendor (sakib2588) ✅ Done
- [x] Seller registration (with admin approval flow)
- [x] Shop profile management
- [x] Product CRUD + multi-image upload
- [x] Stock management + low-stock alerts
- [x] Coupon creation + toggle (AJAX)
- [x] Order management (filter by status)
- [x] Confirm/ship order items
- [x] Return request approve/reject
- [x] Reply to reviews (AJAX)
- [x] Sales analytics dashboard

### Role 3 — Delivery Manager 🚧 Implemented locally
- [x] Login + logistics dashboard
- [x] Manage delivery agents (CRUD)
- [x] Manage delivery zones (CRUD)
- [x] View ready-for-dispatch orders
- [x] Assign agent to order
- [x] Update delivery status (Picked Up → In Transit → Delivered/Failed)
- [x] Failed delivery re-assignment
- [x] Delivery history (via Active + Failed views)
- [x] Agent performance report
- [x] Zone performance report
- [x] Daily/weekly delivery summary
- [x] **AJAX:** `ajax/delivery_update_status.php` (live status changes via `XMLHttpRequest`)

### Role 4 — Platform Admin (ampfibi1)
- [x] Admin dashboard
- [x] Seller approval
- [x] Category management
- [x] User management
- [x] Product oversight
- [x] Order oversight
- [x] Dispute handling
- [x] Commission rates
- [x] Platform coupons
- [x] Analytics
- [x] Announcements
- [ ] **🔴 SQL injection in `model/logModel.php` — FIX BEFORE SUBMISSION**
- [ ] **🔴 Port from `controller/` flat structure to `app/controllers/AdminController.php` MVC**
- [ ] **🔴 Use `password_verify()` not plaintext comparison**
- [ ] **🔴 Match session keys: `uid` / `uname` / `role` (not `user_id` etc.)**
- [ ] AJAX feature (search suggestion already partial — confirm it works end-to-end)

---

## 11. Submission checklist (from MD, line 187–195)

Before pushing `development` → `main`:

- [ ] Each role individually accessible via its own login + dashboard
- [ ] Role-based access control prevents cross-role access
- [ ] Shared database schema imports cleanly on a fresh XAMPP
- [ ] At least one AJAX feature per role works
- [ ] All forms have server-side validation with clear errors
- [ ] Git history shows feature branches + PRs (no direct pushes)
- [ ] Hardcopy report per role (each member writes their own)
- [ ] `README.md` updated with setup steps + role list

---

## 12. Who to ping for what

| Question | Who |
|---|---|
| Customer or Seller code | sakib2588 |
| Admin code | ampfibi1 |
| Delivery code | (whoever claims Role 3) |
| Schema changes | open a PR adding to `migrations/` |
| Git / merge conflict help | discuss in the group chat first |

---

**Last reminder:** the project is a SHARED submission. One person's broken code = everyone's lower grade. Test your code on a fresh DB import before you ship it.
