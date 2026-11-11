<?php
/**
 * VELORA Luxury Fashion Boutique
 * Database Connection & Initialization Manager
 * 
 * Supports:
 * 1. MySQL / MariaDB via PDO (Default for XAMPP / WAMP / Production)
 * 2. SQLite Fallback (Auto-initializes if local MySQL server is not currently running)
 */

if (!defined('VELORA_ACCESS')) {
    define('VELORA_ACCESS', true);
}

// -------------------------------------------------------------
// Database Configuration Parameters (Adjust as needed for MySQL)
// -------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'velora_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

function localizeLegacyImagePaths(PDO $pdo) {
    $migrations = [
        ['categories', 'slug', 'image_url', [
            'silk-gowns' => 'assets/img/category-silk-gowns.jpg',
            'kurtas' => 'assets/img/category-kurtas.jpg',
            'cocktail' => 'assets/img/category-cocktail.jpg',
            'handwoven' => 'assets/img/category-handwoven.jpg',
            'bridal-edit' => 'assets/img/category-bridal-edit.jpg',
        ]],
        ['products', 'slug', 'primary_image', [
            'miraya-pleated-wine-maxi' => 'assets/img/miraya-pleated-wine-maxi.jpg',
            'alia-ivory-draped-silk-midi' => 'assets/img/alia-ivory-draped-silk-midi.jpg',
            'zoya-organza-flared-gown' => 'assets/img/zoya-organza-flared-gown.jpg',
            'sultana-kaftan-dress' => 'assets/img/sultana-kaftan-dress.jpg',
            'nisha-tiered-brocade-dress' => 'assets/img/nisha-tiered-brocade-dress.jpg',
            'meera-corset-georgette' => 'assets/img/meera-corset-georgette.jpg',
            'aarohi-tiered-georgette' => 'assets/img/aarohi-tiered-georgette.jpg',
            'zuri-wine-silk-midi' => 'assets/img/zuri-wine-silk-midi.jpg',
            'tara-draped-cape-set' => 'assets/img/tara-draped-cape-set.jpg',
            'nitya-chanderi-co-ord' => 'assets/img/nitya-chanderi-co-ord.jpg',
        ]],
        ['product_images', 'display_order', 'image_url', [
            0 => 'assets/img/miraya-pleated-wine-maxi.jpg',
            1 => 'assets/img/miraya-studio-portrait.jpg',
            2 => 'assets/img/miraya-back-drape.jpg',
            3 => 'assets/img/miraya-zari-detail.jpg',
            4 => 'assets/img/miraya-runway.jpg',
        ], 'product_id = 1'],
        ['reviews', 'author_name', 'customer_image', [
            'Ananya Goswami' => 'assets/img/review-ananya-goswami.jpg',
        ]],
        ['bundled_accessories', 'name', 'image_url', [
            'Velora Gold Filigree Choker' => 'assets/img/velora-gold-filigree-choker.jpg',
            'Velvet Minaudière Clutch' => 'assets/img/velvet-minaudiere-clutch.jpg',
        ]],
    ];

    foreach ($migrations as $migration) {
        [$table, $keyColumn, $imageColumn, $paths] = $migration;
        $extraCondition = $migration[4] ?? '1 = 1';
        $statement = $pdo->prepare(
            "UPDATE {$table} SET {$imageColumn} = ? "
            . "WHERE {$keyColumn} = ? AND {$imageColumn} LIKE 'https://lh3.googleusercontent.com/%' "
            . "AND {$extraCondition}"
        );
        foreach ($paths as $key => $path) {
            $statement->execute([$path, $key]);
        }
    }
}

function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $driver = 'mysql';
    $sqlitePath = __DIR__ . '/../database/velora.sqlite';

    // 1. Attempt MySQL connection first
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        ensureAdminAndSeedData($pdo);
        localizeLegacyImagePaths($pdo);
        return $pdo;
    } catch (PDOException $e) {
        // MySQL connection attempt failed. Log notice and fallback to SQLite for seamless local execution
        error_log("VELORA DB Notice: MySQL connection failed (" . $e->getMessage() . "). Falling back to SQLite local database.");
    }

    // 2. Fallback to SQLite
    try {
        $firstTime = !file_exists($sqlitePath);
        $dsn = "sqlite:" . $sqlitePath;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // If sqlite database was just created, seed tables
        if ($firstTime || filesize($sqlitePath) === 0) {
            initializeSqliteDatabase($pdo);
        }
        ensureAdminAndSeedData($pdo);
        localizeLegacyImagePaths($pdo);
        return $pdo;
    } catch (PDOException $sqle) {
        die("Fatal Database Error: Unable to connect to MySQL or initialize local SQLite database: " . htmlspecialchars($sqle->getMessage()));
    }
}

/**
 * Initialize SQLite database with identical tables and seed data
 */
function ensureAdminAndSeedData(PDO $pdo) {
    static $ensured = false;
    if ($ensured) {
        return;
    }
    $ensured = true;

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            full_name TEXT NOT NULL,
            role TEXT DEFAULT 'Head Couturier',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(150) NOT NULL,
            `role` VARCHAR(100) DEFAULT 'Head Couturier',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    try {
        $checkAdmin = $pdo->query("SELECT COUNT(*) as c FROM admins");
        if ($checkAdmin && (int)$checkAdmin->fetch()['c'] === 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['admin', 'admin@velora.com', $hash, 'Eleanor Vance', 'Head Couturier']);
        }
    } catch (Exception $e) {}

    try {
        $checkOrders = $pdo->query("SELECT COUNT(*) as c FROM orders");
        if ($checkOrders && (int)$checkOrders->fetch()['c'] === 0) {
            $ordersData = [
                [
                    'order_number' => 'VEL-8921',
                    'customer_name' => 'Ananya Goswami',
                    'customer_email' => 'ananya.g@velora-vip.in',
                    'customer_phone' => '+91 98201 44821',
                    'shipping_address' => 'Bungalow 4, Perry Cross Road, Bandra West',
                    'city' => 'Mumbai',
                    'pincode' => '400050',
                    'payment_method' => 'UPI (ananya@okhdfcbank)',
                    'subtotal' => 5490.00,
                    'discount_amount' => 0.00,
                    'shipping_fee' => 0.00,
                    'total_amount' => 5490.00,
                    'coupon_code' => null,
                    'order_status' => 'processing',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'items' => [
                        ['product_id' => 1, 'product_title' => 'The Miraya Pleated Mulberry Silk Maxi', 'color_name' => 'Royal Wine Burgundy', 'size_code' => 'M', 'quantity' => 1, 'unit_price' => 5490.00, 'total_price' => 5490.00]
                    ]
                ],
                [
                    'order_number' => 'VEL-8924',
                    'customer_name' => 'Devika Mehra',
                    'customer_email' => 'devika.m@atelier.co.in',
                    'customer_phone' => '+91 98110 33924',
                    'shipping_address' => 'Suite 12, Golf Links',
                    'city' => 'New Delhi',
                    'pincode' => '110021',
                    'payment_method' => 'Amex Platinum',
                    'subtotal' => 6290.00,
                    'discount_amount' => 500.00,
                    'shipping_fee' => 0.00,
                    'total_amount' => 5790.00,
                    'coupon_code' => 'VELORA500',
                    'order_status' => 'processing',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                    'items' => [
                        ['product_id' => 9, 'product_title' => 'Tara Draped Cape Set', 'color_name' => 'Wine', 'size_code' => 'S', 'quantity' => 1, 'unit_price' => 6290.00, 'total_price' => 6290.00]
                    ]
                ],
                [
                    'order_number' => 'VEL-8923',
                    'customer_name' => 'Pooja Singhania',
                    'customer_email' => 'pooja.singhania@heritage.org',
                    'customer_phone' => '+91 99002 88923',
                    'shipping_address' => 'Penthouse A, Lavelle Road',
                    'city' => 'Bengaluru',
                    'pincode' => '560001',
                    'payment_method' => 'NetBanking (ICICI Private)',
                    'subtotal' => 8990.00,
                    'discount_amount' => 899.00,
                    'shipping_fee' => 0.00,
                    'total_amount' => 8091.00,
                    'coupon_code' => 'VELORA10',
                    'order_status' => 'in_transit',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'items' => [
                        ['product_id' => 3, 'product_title' => 'Zoya Organza Flared Gown', 'color_name' => 'Dusty Mauve Wine', 'size_code' => 'M', 'quantity' => 1, 'unit_price' => 8990.00, 'total_price' => 8990.00]
                    ]
                ],
                [
                    'order_number' => 'VEL-8920',
                    'customer_name' => 'Meera Kapoor',
                    'customer_email' => 'meera.k@kapoorlux.com',
                    'customer_phone' => '+91 97011 55920',
                    'shipping_address' => 'Road No. 36, Jubilee Hills',
                    'city' => 'Hyderabad',
                    'pincode' => '500034',
                    'payment_method' => 'Visa Infinite',
                    'subtotal' => 6800.00,
                    'discount_amount' => 0.00,
                    'shipping_fee' => 0.00,
                    'total_amount' => 6800.00,
                    'coupon_code' => null,
                    'order_status' => 'delivered',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                    'items' => [
                        ['product_id' => 2, 'product_title' => 'Alia Ivory Draped Silk Midi', 'color_name' => 'Pure Ivory', 'size_code' => 'L', 'quantity' => 1, 'unit_price' => 6800.00, 'total_price' => 6800.00]
                    ]
                ],
                [
                    'order_number' => 'VEL-8919',
                    'customer_name' => 'Radhika Sharma',
                    'customer_email' => 'radhika.s@sharmaholdings.in',
                    'customer_phone' => '+91 94140 22919',
                    'shipping_address' => 'Heritage Villa, Civil Lines',
                    'city' => 'Jaipur',
                    'pincode' => '302001',
                    'payment_method' => 'Cash on Delivery (White Glove)',
                    'subtotal' => 4250.00,
                    'discount_amount' => 0.00,
                    'shipping_fee' => 0.00,
                    'total_amount' => 4250.00,
                    'coupon_code' => null,
                    'order_status' => 'confirmed',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                    'items' => [
                        ['product_id' => 4, 'product_title' => 'Sultana Embroidered Kaftan Dress', 'color_name' => 'Forest Emerald', 'size_code' => 'Free', 'quantity' => 1, 'unit_price' => 4250.00, 'total_price' => 4250.00]
                    ]
                ]
            ];

            $stmtOrder = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, shipping_address, city, pincode, payment_method, subtotal, discount_amount, shipping_fee, total_amount, coupon_code, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_title, color_name, size_code, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($ordersData as $o) {
                $stmtOrder->execute([
                    $o['order_number'], $o['customer_name'], $o['customer_email'], $o['customer_phone'],
                    $o['shipping_address'], $o['city'], $o['pincode'], $o['payment_method'],
                    $o['subtotal'], $o['discount_amount'], $o['shipping_fee'], $o['total_amount'],
                    $o['coupon_code'], $o['order_status'], $o['created_at']
                ]);
                $orderId = $pdo->lastInsertId();
                foreach ($o['items'] as $item) {
                    $stmtItem->execute([
                        $orderId, $item['product_id'], $item['product_title'], $item['color_name'],
                        $item['size_code'], $item['quantity'], $item['unit_price'], $item['total_price']
                    ]);
                }
            }
        }
    } catch (Exception $e) {}
}

function initializeSqliteDatabase(PDO $pdo) {
    $schema = "
    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        description TEXT,
        image_url TEXT,
        item_count INTEGER DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE NOT NULL,
        title TEXT NOT NULL,
        subtitle TEXT,
        category_id INTEGER NOT NULL,
        silhouette TEXT,
        fabric TEXT,
        occasion TEXT,
        price REAL NOT NULL,
        original_price REAL,
        discount_percent INTEGER DEFAULT 0,
        rating REAL DEFAULT 4.9,
        review_count INTEGER DEFAULT 0,
        badge TEXT,
        badge_type TEXT DEFAULT 'primary',
        primary_image TEXT NOT NULL,
        model_info TEXT,
        craftsmanship TEXT,
        care_instructions TEXT,
        is_featured INTEGER DEFAULT 0,
        is_bestseller INTEGER DEFAULT 0,
        is_new_arrival INTEGER DEFAULT 0,
        stock_quantity INTEGER DEFAULT 15,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS product_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        image_url TEXT NOT NULL,
        alt_text TEXT,
        display_order INTEGER DEFAULT 0,
        is_video_preview INTEGER DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS product_colors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        color_name TEXT NOT NULL,
        hex_code TEXT NOT NULL,
        is_default INTEGER DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS product_sizes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        size_code TEXT NOT NULL,
        stock_count INTEGER DEFAULT 5,
        low_stock_threshold INTEGER DEFAULT 2
    );

    CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        author_name TEXT NOT NULL,
        author_initials TEXT NOT NULL,
        author_badge TEXT DEFAULT 'Verified Connoisseur',
        rating INTEGER DEFAULT 5,
        comment TEXT NOT NULL,
        customer_image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS bundled_accessories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        parent_product_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        tag TEXT DEFAULT 'Stylist Pick',
        price REAL NOT NULL,
        image_url TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        discount_type TEXT DEFAULT 'percentage',
        discount_value REAL NOT NULL,
        min_spend REAL DEFAULT 0,
        description TEXT NOT NULL,
        is_active INTEGER DEFAULT 1
    );

    CREATE TABLE IF NOT EXISTS cart_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT NOT NULL,
        product_id INTEGER NOT NULL,
        color_name TEXT,
        size_code TEXT,
        quantity INTEGER NOT NULL DEFAULT 1,
        price REAL NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS wishlist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT NOT NULL,
        product_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(session_id, product_id)
    );

    CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_number TEXT UNIQUE NOT NULL,
        customer_name TEXT NOT NULL,
        customer_email TEXT NOT NULL,
        customer_phone TEXT NOT NULL,
        shipping_address TEXT NOT NULL,
        city TEXT NOT NULL,
        pincode TEXT NOT NULL,
        payment_method TEXT NOT NULL DEFAULT 'COD',
        subtotal REAL NOT NULL,
        discount_amount REAL DEFAULT 0,
        shipping_fee REAL DEFAULT 0,
        total_amount REAL NOT NULL,
        coupon_code TEXT,
        order_status TEXT DEFAULT 'confirmed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        product_title TEXT NOT NULL,
        color_name TEXT,
        size_code TEXT,
        quantity INTEGER NOT NULL,
        unit_price REAL NOT NULL,
        total_price REAL NOT NULL
    );

    CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        coupon_awarded TEXT DEFAULT 'VELORA500',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $pdo->exec($schema);

    // Categories
    $pdo->exec("
    INSERT INTO categories (id, slug, name, description, image_url, item_count) VALUES
    (1, 'silk-gowns', 'Silk Gowns', 'Handcrafted fluid mulberry silk gowns with sculptural drapes', 'assets/img/category-silk-gowns.jpg', 16),
    (2, 'kurtas', 'Bespoke Kurtas', 'Intricately embroidered festive kurtas in soft heritage silks', 'assets/img/category-kurtas.jpg', 12),
    (3, 'cocktail', 'Cocktail Dresses', 'Contemporary asymmetric silhouettes designed for evening salons', 'assets/img/category-cocktail.jpg', 8),
    (4, 'handwoven', 'Handwoven Co-ords', 'Artisanal loom pieces tailored in breathable organic cottons', 'assets/img/category-handwoven.jpg', 10),
    (5, 'bridal-edit', 'Bridal & Heirloom', 'Regal heirloom ensembles embroidered with antique gold zardozi', 'assets/img/category-bridal-edit.jpg', 6);
    ");

    // Products
    $pdo->exec("
    INSERT INTO products (id, slug, title, subtitle, category_id, silhouette, fabric, occasion, price, original_price, discount_percent, rating, review_count, badge, badge_type, primary_image, model_info, craftsmanship, care_instructions, is_featured, is_bestseller, is_new_arrival, stock_quantity) VALUES
    (1, 'miraya-pleated-wine-maxi', 'The Miraya Pleated Mulberry Silk Maxi', 'Handcrafted Tiered Mulberry Silk Gown with Knife Pleats', 1, 'Maxi', 'Mulmul Silk', 'Cocktail Soirée', 5490.00, 7990.00, 31, 4.9, 142, 'Bestseller', 'primary', 'assets/img/miraya-pleated-wine-maxi.jpg', 'Model is 5''9\", Bust 32B, Waist 25\", wearing size S', 'Artisanal 100% Pure Mulberry Silk harvested from heritage sericulture farms in Karnataka. Woven on traditional handlooms with a micro-knife pleat technique that retains dynamic memory and fluid drape.', 'Strictly Professional Dry Clean Only. Store hung inside provided muslin garment bag.', 1, 1, 0, 18),
    (2, 'alia-ivory-draped-silk-midi', 'Alia Ivory Draped Silk Midi', 'Sculptural Asymmetrical Cowl Collar Midi Dress', 1, 'Midi', 'Mulmul Silk', 'Atelier Salon', 6800.00, 6800.00, 0, 4.8, 86, 'New Arrival', 'secondary', 'assets/img/alia-ivory-draped-silk-midi.jpg', 'Model is 5''8\", wearing size S', 'Hand-draped pure raw silk with natural unbleached ivory texture. Tailored with seamless French seams and antique brass horn button closure.', 'Dry clean only. Cool iron on reverse side.', 1, 0, 1, 12),
    (3, 'zoya-organza-flared-gown', 'Zoya Organza Flared Gown', 'Dusty Mauve-Wine Flared Ballgown with Hand Zardozi', 1, 'Flared Gown', 'Chiffon Organza', 'Festive Gathering', 8990.00, 11500.00, 22, 4.9, 128, 'Limited', 'tertiary', 'assets/img/zoya-organza-flared-gown.jpg', 'Model is 5''10\", wearing size M', 'Sheer Italian organza overlaid on mulberry silk satin. Decorated with hand-appliqued silver and rosegold zari along borders.', 'Delicate dry clean only. Do not spray perfume directly on embroidery.', 1, 1, 0, 7),
    (4, 'sultana-kaftan-dress', 'Sultana Embroidered Kaftan Dress', 'Jewel-Toned Emerald Silk Kaftan with Artisanal Neckline', 3, 'Free Silhouette', 'Mulmul Silk', 'Day Luncheon', 4250.00, 5500.00, 23, 4.7, 64, 'Handcrafted', 'secondary', 'assets/img/sultana-kaftan-dress.jpg', 'One Size Free Silhouette fits XS to XL comfortably', 'Fluid silk habotai drape with Kashmiri tilla needlework framing collar and batwing sleeve cuffs.', 'Gentle hand wash or dry clean recommended.', 0, 0, 0, 25),
    (5, 'nisha-tiered-brocade-dress', 'Nisha Tiered Brocade Dress', 'Rose Bronze Banarasi Brocade Tiered Empire Silhouette', 1, 'Maxi', 'Mulmul Silk', 'Festive Gathering', 7190.00, 7190.00, 0, 4.9, 52, 'Artisanal', 'secondary', 'assets/img/nisha-tiered-brocade-dress.jpg', 'Model is 5''9\", wearing size S', 'Authentic Banarasi handloom brocade woven with tested metallic threads. Features tiered gathers and concealed pockets.', 'Specialist dry clean only.', 0, 1, 0, 10),
    (6, 'meera-corset-georgette', 'Meera Corset Georgette Dress', 'Fitted Corset Georgette with Ruffled Fluid Hem', 3, 'Midi', 'Georgette', 'Cocktail Soirée', 5990.00, 5990.00, 0, 4.8, 77, 'Couture', 'primary', 'assets/img/meera-corset-georgette.jpg', 'Model is 5''8\", wearing size XS', 'Viscose georgette with hand-stitched boned corset structure and invisible zipper. Inner lining crafted in pure cotton mulmul.', 'Professional dry clean only.', 0, 0, 1, 14),
    (7, 'aarohi-tiered-georgette', 'Aarohi Tiered Georgette Maxi', 'Cascading Layers in Deep Wine Tones with Rosegold Accents', 1, 'Maxi', 'Georgette', 'Festive Gathering', 4890.00, 6990.00, 30, 4.9, 110, 'Exclusive', 'primary', 'assets/img/aarohi-tiered-georgette.jpg', 'Model is 5''9\", wearing size S', 'Micro-pleated tier design finished with subtle scalloped hand embroidery.', 'Dry clean only.', 1, 1, 0, 16),
    (8, 'zuri-wine-silk-midi', 'Zuri Wine Silk Midi', 'Artisanal Neck Pleating with Mulberry Silk Body', 1, 'Midi', 'Mulmul Silk', 'Atelier Salon', 6450.00, 7500.00, 14, 4.9, 95, 'Hand-Embroidered', 'secondary', 'assets/img/zuri-wine-silk-midi.jpg', 'Model is 5''8\", wearing size S', 'Hand-tucked origami collar pleats on organic mulberry silk.', 'Dry clean only.', 1, 1, 0, 11),
    (9, 'tara-draped-cape-set', 'Tara Draped Cape Set', 'Statement Draped Cape with Ankle Silhouette Trousers', 2, 'Anarkali Dress', 'Mulmul Silk', 'Festive Gathering', 6290.00, 8990.00, 30, 4.9, 128, '-30%', 'error', 'assets/img/tara-draped-cape-set.jpg', 'Model is 5''9\", wearing size S', 'Flowing silk chiffon cape with hand-tasseled edges and silk cigarette trousers.', 'Dry clean only.', 1, 1, 0, 9),
    (10, 'nitya-chanderi-co-ord', 'Nitya Chanderi Co-ord', 'Subtle Gold Embroidery on Handspun Chanderi Weave', 4, 'Midi', 'Cotton Linen', 'Day Luncheon', 5490.00, 7800.00, 29, 4.9, 94, 'Iconic', 'primary', 'assets/img/nitya-chanderi-co-ord.jpg', 'Model is 5''8\", wearing size M', 'Handspun Madhya Pradesh Chanderi silk-cotton with zari buttis.', 'Dry clean or cold gentle hand soak.', 1, 1, 0, 15);
    ");

    // Images
    $pdo->exec("
    INSERT INTO product_images (product_id, image_url, alt_text, display_order, is_video_preview) VALUES
    (1, 'assets/img/miraya-pleated-wine-maxi.jpg', 'Miraya Silk Maxi - Front View', 0, 0),
    (1, 'assets/img/miraya-studio-portrait.jpg', 'Miraya Silk Maxi - Studio Portrait Close-up', 1, 0),
    (1, 'assets/img/miraya-back-drape.jpg', 'Miraya Silk Maxi - Draped Cascade Back', 2, 0),
    (1, 'assets/img/miraya-zari-detail.jpg', 'Miraya Silk Maxi - Macro Zari & Pleat Weave Texture', 3, 0),
    (1, 'assets/img/miraya-runway.jpg', 'Miraya Silk Maxi - Fluid Runway Motion', 4, 1);
    ");

    // Colors
    $pdo->exec("
    INSERT INTO product_colors (product_id, color_name, hex_code, is_default) VALUES
    (1, 'Royal Wine Burgundy', '#702C3B', 1),
    (1, 'Moonlit Warm Ivory', '#F4EFE6', 0),
    (1, 'Midnight Indigo Silk', '#1A2238', 0),
    (1, 'Raw Sienna Terracotta', '#B85D43', 0),
    (2, 'Pure Ivory', '#FAF7F2', 1),
    (2, 'Champagne Rose', '#E3C192', 0),
    (3, 'Dusty Mauve Wine', '#702C3B', 1),
    (4, 'Forest Emerald', '#1E4A38', 1),
    (4, 'Deep Wine', '#702C3B', 0),
    (5, 'Rose Bronze', '#B27961', 1),
    (6, 'Garnet Wine', '#541626', 1),
    (6, 'Noir Charcoal', '#2D2D2A', 0),
    (9, 'Wine', '#541626', 1),
    (9, 'Ivory', '#FAF7F2', 0),
    (9, 'Sage', '#7D8B7A', 0),
    (9, 'Terracotta', '#B26E51', 0),
    (10, 'Ivory', '#FAF7F2', 1),
    (10, 'Wine', '#541626', 0),
    (10, 'Sage', '#7D8B7A', 0);
    ");

    // Sizes
    $pdo->exec("
    INSERT INTO product_sizes (product_id, size_code, stock_count, low_stock_threshold) VALUES
    (1, 'XS', 4, 2), (1, 'S', 2, 2), (1, 'M', 7, 2), (1, 'L', 3, 2), (1, 'XL', 2, 2), (1, 'XXL', 1, 2),
    (2, 'S', 5, 2), (2, 'M', 4, 2), (2, 'L', 3, 2),
    (3, 'XS', 2, 2), (3, 'S', 3, 2), (3, 'M', 2, 2),
    (4, 'Free', 25, 2),
    (5, 'S', 3, 2), (5, 'M', 4, 2), (5, 'L', 3, 2),
    (6, 'XS', 3, 2), (6, 'S', 5, 2), (6, 'M', 4, 2);
    ");

    // Reviews
    $pdo->exec("
    INSERT INTO reviews (product_id, author_name, author_initials, author_badge, rating, comment, customer_image) VALUES
    (1, 'Ananya Goswami', 'AG', 'Verified Connoisseur', 5, 'The weight of this silk maxi is pure poetry. I wore it for my sister''s Sangeet in Jaipur and received countless compliments. The pleats fall like liquid rubies.', 'assets/img/review-ananya-goswami.jpg'),
    (1, 'Meera Ranganathan', 'MR', 'Verified Connoisseur', 5, 'Impeccable cut. I was nervous about ordering size M online, but the complimentary doorstep alteration guarantee made it completely risk-free. Fits like couture!', NULL),
    (1, 'Tara Oberoi', 'TO', 'Verified Connoisseur', 5, 'The zari detailing along the micro-pleats reflects candlelight in such a subtle, understated way. Exactly what quiet luxury should feel like.', NULL);
    ");

    // Bundled
    $pdo->exec("
    INSERT INTO bundled_accessories (parent_product_id, name, tag, price, image_url) VALUES
    (1, 'Velora Gold Filigree Choker', 'Stylist Pick', 2190.00, 'assets/img/velora-gold-filigree-choker.jpg'),
    (1, 'Velvet Minaudière Clutch', 'Handcrafted', 3490.00, 'assets/img/velvet-minaudiere-clutch.jpg');
    ");

    // Coupons
    $pdo->exec("
    INSERT INTO coupons (code, discount_type, discount_value, min_spend, description, is_active) VALUES
    ('VELORA10', 'percentage', 10.00, 2999.00, '10% complimentary discount on orders above ₹2,999', 1),
    ('VELORA500', 'flat', 500.00, 3000.00, 'Flat ₹500 off for Atelier Circle members', 1),
    ('FESTIVE15', 'percentage', 15.00, 5000.00, '15% off festive orders above ₹5,000', 1);
    ");

    // Initial cart items for demo
    $pdo->exec("
    INSERT INTO cart_items (session_id, product_id, color_name, size_code, quantity, price) VALUES
    ('demo_session', 1, 'Royal Wine Burgundy', 'S', 1, 5490.00),
    ('demo_session', 8, 'Wine', 'S', 1, 2000.00);

    INSERT INTO wishlist (session_id, product_id) VALUES
    ('demo_session', 1),
    ('demo_session', 7),
    ('demo_session', 9);
    ");
}
