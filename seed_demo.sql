-- Demo seed data for ShopHub
-- Adds realistic categories + 12 products to existing sellers.
-- IMPORTANT: products INSERT is NOT idempotent — run ONCE on a fresh DB.
-- If duplicates appear, run:  DELETE FROM products WHERE id > <last_intended_id>;

-- ---- Top-level categories ----
INSERT IGNORE INTO categories (id, parent_id, name, description) VALUES
(1, NULL, 'Electronics',       'Phones, laptops, audio, accessories'),
(2, NULL, 'Clothing',           'Men, women, kids'),
(5, NULL, 'Home & Kitchen',     'Appliances, cookware, decor'),
(6, NULL, 'Books',              'Fiction, non-fiction, textbooks'),
(7, NULL, 'Beauty & Personal',  'Skincare, haircare, fragrance'),
(8, NULL, 'Sports & Outdoors',  'Fitness, outdoor, gear');

-- ---- Subcategories ----
INSERT IGNORE INTO categories (id, parent_id, name, description) VALUES
(3,  1, 'Mobile Phones',     'Smartphones and feature phones'),
(4,  1, 'Laptops',           'Notebooks and ultrabooks'),
(9,  1, 'Audio',             'Headphones, speakers, earbuds'),
(10, 2, 'Men',               'Mens clothing'),
(11, 2, 'Women',             'Womens clothing'),
(12, 5, 'Cookware',          'Pots, pans, knife sets'),
(13, 5, 'Small Appliances',  'Blenders, toasters, kettles'),
(14, 7, 'Skincare',          'Cleansers, moisturizers'),
(15, 8, 'Fitness',           'Home gym, yoga, accessories');

-- ---- 12 demo products (seller_id 1 + 2) ----
-- Views fall back to "No image" placeholder when local file missing.
INSERT INTO products (seller_id, category_id, name, description, price, stock_qty, primary_image_path, is_available)
VALUES
(1, 3,  'Galaxy A55 5G 128GB Awesome Blue',
        'Mid-range hero phone with 50MP camera, 5000mAh battery, and a smooth 120Hz Super AMOLED display.',
        45999.00, 25, '', 1),
(1, 3,  'iPhone 14 128GB Midnight',
        'A15 Bionic chip, dual-camera system with Night mode and Cinematic mode, Ceramic Shield front.',
        89999.00, 12, '', 1),
(1, 4,  'MacBook Air M2 13-inch 256GB',
        'Apple silicon M2 chip, 8GB unified memory, 13.6-inch Liquid Retina, 18-hour battery life.',
        134999.00, 8, '', 1),
(1, 4,  'HP Pavilion 15 Core i5 12th Gen',
        'Intel Core i5-1235U, 16GB DDR4, 512GB NVMe SSD, 15.6-inch FHD, backlit keyboard.',
        82500.00, 18, '', 1),
(2, 9,  'Sony WH-1000XM5 Wireless Headphones',
        'Industry-leading noise cancellation, 30-hour battery, multipoint Bluetooth 5.2.',
        38500.00, 22, '', 1),
(2, 9,  'Anker Soundcore Liberty 4 NC Earbuds',
        'Active noise cancellation, LDAC hi-res audio, 50-hour total playtime with case.',
        9500.00, 60, '', 1),
(2, 12, 'Prestige Granite Non-Stick 5-piece Cookware Set',
        'PFOA-free granite coating, induction-compatible base, includes lids and turner.',
        6800.00, 35, '', 1),
(2, 13, 'Philips HR2096 Daily Collection Blender 600W',
        'ProBlend 4 technology, 2L jar, ice-crush mode, dishwasher-safe parts.',
        7250.00, 28, '', 1),
(1, 10, 'Cotton Henley T-Shirt (Charcoal)',
        '100% combed cotton, 200 GSM, slim fit, three-button placket. Sizes M-XXL.',
        890.00, 120, '', 1),
(1, 11, 'Floral Print Maxi Dress',
        'Lightweight rayon, side pockets, elastic waistband, knee-grazing length.',
        1850.00, 45, '', 1),
(2, 14, 'CeraVe Hydrating Facial Cleanser 236ml',
        'Non-foaming cleanser with hyaluronic acid and three essential ceramides.',
        1450.00, 80, '', 1),
(2, 15, 'Adjustable Dumbbell Set 24kg (Pair)',
        'Cast iron plates with chrome handles, spin-lock collars, two carry bags included.',
        5400.00, 14, '', 1);

-- Mark old "Test Laptop Pro" placeholders as unavailable so they don't show.
-- (Cannot DELETE: order_items FK references them.)
UPDATE products SET is_available = 0 WHERE name = 'Test Laptop Pro';

-- Reviews need a real order_id (FK NOT NULL). Skipped in seed; star widget
-- will render 0.0 cleanly for fresh products until real orders exist.

-- ---- Delivery zones (used at checkout) ----
INSERT IGNORE INTO delivery_zones (id, zone_name, delivery_fee, estimated_days) VALUES
(1, 'Dhaka City',     60,  2),
(2, 'Dhaka Outskirts',80,  3),
(3, 'Chittagong',     120, 4),
(4, 'Sylhet',         130, 4),
(5, 'Rajshahi',       130, 4),
(6, 'Khulna',         140, 5),
(7, 'Outside Major Cities', 180, 6);
