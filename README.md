# E-Commerce Store

## Project — E-Commerce Store

### Overview

A multi-vendor online marketplace where customers shop across multiple seller stores, sellers manage their own product catalogs and orders, a delivery manager coordinates the logistics network, and a platform admin oversees the entire marketplace. The system covers the full commerce cycle: product discovery, purchase, fulfilment, delivery, and post-purchase reviews.

---

# Roles

| Role             | Responsibility                                          |
| ---------------- | ------------------------------------------------------- |
| Customer         | Browse, cart, checkout, track, review, wishlist         |
| Seller/Vendor    | Product management, order fulfilment, analytics         |
| Delivery Manager | Agent management, order dispatch, delivery tracking     |
| Platform Admin   | Seller approval, categories, disputes, platform reports |

---

# Shared Database Schema

All four students create and use the same database. The schema must include at minimum the following tables:

## users — All platform users

* id, name, email, password_hash, phone, role (customer/seller/delivery_manager/admin), profile_pic, is_active, created_at

## sellers — Extended profile for seller users

* id, user_id, shop_name, shop_description, shop_logo_path, address, is_approved, commission_rate, created_at

## categories — Hierarchical product categories (parent–child)

* id, parent_id, name, description

## products — Product listings by sellers

* id, seller_id, category_id, name, description, price, stock_qty, primary_image_path, is_available, created_at

## product_images — Additional images per product

* id, product_id, image_path, display_order

## coupons — Promotional coupon codes created by sellers

* id, seller_id, code, discount_pct, max_uses, uses_count, valid_until, is_active

## orders — Customer purchase orders

* id, customer_id, shipping_address, payment_method, subtotal, discount_amount, total_amount, status (pending/confirmed/processing/shipped/delivered/cancelled/return_requested/returned), coupon_id, created_at

## order_items — Line items within an order (may span multiple sellers)

* id, order_id, product_id, seller_id, quantity, unit_price, item_status (pending/confirmed/shipped/delivered)

## delivery_agents — Agents managed by the delivery manager

* id, user_id, vehicle_type, phone, is_active, created_at

## delivery_assignments — Assignment of an agent to a shipment

* id, order_id, agent_id, assigned_at, status (assigned/picked_up/in_transit/delivered/failed), delivery_zone

## reviews — Product ratings and reviews by customers

* id, product_id, order_id, customer_id, rating (1–5), review_text, seller_reply, created_at

## wishlists — Customer saved products

* id, customer_id, product_id, added_at

## return_requests — Customer return or refund requests

* id, order_id, order_item_id, customer_id, reason, status (pending/approved/rejected/completed), created_at

## delivery_zones — Geographic zones and their delivery fees

* id, zone_name, delivery_fee, estimated_days

## disputes — Customer complaints escalated to admin

* id, customer_id, seller_id, order_id, description, status (open/resolved), admin_note, created_at

Each group member is responsible for creating their own data in the database. Each member may only insert the data that is required for demonstrating their own work.

---

# Technical Requirements

* Follow the MVC pattern: separate Models, Views, and Controllers; no business logic in view files
* Use PHP for all server-side logic and MySQL as the database
* All database queries must use mysqli with prepared statements — no raw string insertion allowed
* Authentication must use PHP sessions with role-based access control on every protected page
* Each student must implement at least one feature using AJAX (XMLHttpRequest) communicating with a PHP API endpoint that returns JSON
* Maintain a central repository for the group where each student will push their code. It is the responsibility of each group member to ensure their code works when cloned and run in a XAMPP Apache server.
* Use Git on the command line for version control. Do not push to main/master branch directly. Create feature branches and submit a Pull Request for each major feature
* Submit:

  1. Working online codebase
  2. Hardcopy report describing your role's features

---

# Role 1 — Customer

## Description

Customers are the buyers on the platform. They discover products across multiple seller stores, manage a persistent cart, complete purchases with coupon support, track their deliveries, and engage through reviews and wishlists.

## Features

* Register with name, email, phone, and password; log in and log out
* Manage profile: update personal information, change password, upload profile picture
* Manage saved shipping addresses: add, edit, delete, set default
* Browse all available products across all sellers; navigate by category and subcategory
* Search products by keyword; filter results by category, price range, minimum rating, and availability
* View product detail page: name, description, price, seller shop name, stock status, multiple images, average rating, and all customer reviews
* Add products to a session-based cart; update quantities; remove items; view cart summary with subtotal
* Apply a coupon code at checkout; system validates and displays discount amount via AJAX
* Proceed to checkout: select delivery address, choose delivery zone, select payment method (Cash on Delivery / Card), review full order summary with total
* Place order and view order confirmation page with order ID and estimated delivery window
* Track order status in real time on the order detail page (status badge auto-updates via AJAX polling)
* View complete order history; view itemised detail for each past order
* Request order cancellation for orders not yet shipped; submit return requests for delivered orders with a reason
* Rate (1–5 stars) and write reviews for purchased products; edit or delete own reviews
* Add and remove products from a personal wishlist; view the wishlist as a dedicated page
* View all submitted disputes and their resolution status

---

# Role 2 — Seller / Vendor

## Description

Sellers run their own mini-store within the marketplace. They manage their product catalog, process incoming orders, handle returns, run promotional offers, and track their store's performance through a built-in analytics dashboard.

## Features

* Register a seller account with shop name, description, address, and logo; submit for admin approval; log in after approval
* Manage shop profile: update shop name, description, logo, and contact information
* Manage product categories within their shop scope (admin manages platform-level categories, sellers add products to existing categories)
* Add new products: name, description, price, stock quantity, category, primary image, and up to 4 additional images
* Edit product details; toggle product availability; delete products (blocked if linked to pending orders)
* Manage stock quantities: update stock per product; view low-stock alerts (products below a configurable threshold)
* Create promotional coupon codes: code string, discount percentage, maximum uses, and validity date; activate or deactivate coupons
* View incoming orders for their products: filter by item status (pending, confirmed, shipped, delivered)
* Confirm individual order items (mark as processing); update item status to Shipped with a tracking note
* View full order detail: customer shipping address, all items in the order from their store, and payment method
* Manage return requests for their products: approve or reject with a reason
* View and reply to product reviews left by customers
* View sales analytics dashboard: total revenue per period, top-selling products, order volume by day/week/month, average order value
* View earnings summary: total earned, platform commission deducted, net payout per period

---

# Role 3 — Delivery Manager

## Description

The Delivery Manager is responsible for the logistics network. They maintain a pool of delivery agents, assign orders to agents based on zone and availability, monitor active deliveries, and report on the delivery operation's performance.

## Features

* Log in and manage profile; view the logistics dashboard: pending dispatch count, active deliveries, delivered today
* Manage delivery agents: add new agents (name, phone, vehicle type), edit details, activate or deactivate agents
* View all agents and their current status (active deliveries count)
* Manage delivery zones: add, edit, and delete zones; set delivery fee and estimated delivery days per zone
* View all orders that are ready for dispatch (seller has confirmed and shipped their items) and not yet assigned to an agent
* Assign a delivery agent to an order: select an available agent and confirm assignment; agent is notified via a status update on their dashboard
* View all active deliveries: order ID, customer delivery zone, assigned agent, current status, and time since assignment
* Update delivery status on behalf of an agent when needed: Picked Up → In Transit → Delivered or Failed
* Handle failed deliveries: mark as failed with a reason, re-assign to a different agent or notify the customer
* View delivery history: all completed and failed deliveries with full details and timestamps
* View agent performance report: deliveries completed per agent, average delivery time, failed delivery rate
* View zone performance report: volume of deliveries per zone, average delivery time per zone
* View daily and weekly delivery summary: total delivered, total failed, total in transit

---

# Role 4 — Platform Admin

## Description

The Platform Admin manages the entire marketplace ecosystem. They approve sellers, govern the product category taxonomy, handle inter-party disputes, configure platform-wide settings, and monitor overall marketplace health through comprehensive analytics.

## Features

* Log in to an admin dashboard: total registered users (by role), total active sellers, total orders today, total platform revenue this month
* Manage seller accounts: view all sellers (pending approval and approved), approve or reject new registrations with a reason, suspend or reactivate sellers
* Manage all product categories and subcategories: add, rename, and delete (blocked if products exist in that category)
* Manage customer and delivery manager accounts: search, view, deactivate, and reactivate
* View all products across the platform: search, filter by category or seller, and remove policy-violating listings
* View all orders across the platform with filters by status, date range, seller, and customer
* Handle customer disputes: view dispute details, communicate resolution notes, and close disputes
* Set platform-wide commission rates per seller (override the default for individual sellers)
* Manage platform-level coupon campaigns (platform-funded discounts applied at checkout)
* View platform-wide revenue analytics: gross merchandise value, platform commission earned, top-performing sellers, top-selling categories
* View platform-wide delivery performance overview (summary pulled from the delivery manager's data)
* Manage featured products and seller banners shown on the marketplace homepage
* Post platform-wide announcements to all users
* Generate and view comprehensive monthly reports for the entire marketplace
* Export any report as a printable HTML summary

---

# Submission Checklist

* Each group member is responsible that each of their roles are individually accessible with their own login and dashboard
* Role-based access control prevents any user from accessing another role's pages
* The shared database schema is implemented correctly and consistently across all roles
* At least one AJAX-based feature is implemented per role
* All forms include server-side validation with descriptive error messages
* Git history shows feature branches and pull requests for major features
* Hardcopy report describes all features for the assigned role

---

# Separation of Concerns

* Each group member is responsible for their own code and submission. Other members work will not affect one's development and submission.
* DO NOT rely on any other of your group member for anything, including DB table creation, data insertion, session management etc.
