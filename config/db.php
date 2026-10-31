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
        return $pdo;
    } catch (PDOException $sqle) {
        die("Fatal Database Error: Unable to connect to MySQL or initialize local SQLite database: " . htmlspecialchars($sqle->getMessage()));
    }
}

/**
 * Initialize SQLite database with identical tables and seed data
 */
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
    (1, 'silk-gowns', 'Silk Gowns', 'Handcrafted fluid mulberry silk gowns with sculptural drapes', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVUy18Ne2i8V7QKdxsrdj0ATFp6g4FErMN4BNyxclm3zYHYxr67ulcs59uKp43z2lEV0k0MeghhoDnjz0rmXmwqsGvmA8kGKug80ieIzkL-iS-Guta2i6QSHTF_PsuqpgddSTUmdZwbZ3tBiP-82yuKTWwQsrq6_g2E8wujKAvAIhibUo7Mz010rxSYOhtwm5XZCCndk20-8Mp5-XpJz1JCI7zylr5wyxeuRoQryxFtDexhwOv06dh', 16),
    (2, 'kurtas', 'Bespoke Kurtas', 'Intricately embroidered festive kurtas in soft heritage silks', 'https://lh3.googleusercontent.com/aida-public/AB6AXuC5FGxjwfjdl_nme8NGkzrjHoQryolFwFgwngtTZivDzgx4dIUwxv5HBoV_WO90LgsCjQJ_sg7kAxNW755OJJ4ZJ-zDxtkeOzmk7njIvx8UT25DK4fukN1qj33JT3TIFdzGpCPfE0YB2qIgaubjGFsFNfAryflPn5oUjWfla-08WgOjcPxt2IvWmAlsa3x_t23NlL7atDV5iGhXNoeUYi9UqnfEkgxwO3OEy_uXVK3wq8OU6gy__jlS', 12),
    (3, 'cocktail', 'Cocktail Dresses', 'Contemporary asymmetric silhouettes designed for evening salons', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDJSZ_drxA0fcXIzzCANArTQEf1cOvZUTeagyS16dLdjLZBSQ9Kig3SC5vueTAEJq0mhNpRvEBL1N1ncUyOz_FzvFuqs7-r-9QwLtmHVbaOKh7FA7r6_SNMm0Nvk1bFO4B6hyO2RAsGzJu4aI790LHCxPx_je8NeQB3swXAmWO657qnJ0SrIeMtzKEIZSPKUpFvGqlUAhloCy67Hs1Rg54bbnTyAlPgO9rsCWW-6yJgy-2qYX8xMUqD', 8),
    (4, 'handwoven', 'Handwoven Co-ords', 'Artisanal loom pieces tailored in breathable organic cottons', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBc95bWZT-6xh3YfAnatmNQGg5WeM5npqYMeJu_ywcQwHNjlFSLbi3kYHaeIGsiLrbhY5tN4AreknJ5LrSH1MDMlwKGxN_gWpRnXOflxPbDr9fSV3auoIw9-tuhNMeYoVO39VzFpMYEEGdILiT4gg01OxsZ6aQovIJW3WgqH7pJ3sOJTlCQlSI3bLNP1NskEkQErdBoS10gUKfpOUckqf7k8bGwl0aJPWN-hI60O_kq8iWtgSfz7oer', 10),
    (5, 'bridal-edit', 'Bridal & Heirloom', 'Regal heirloom ensembles embroidered with antique gold zardozi', 'https://lh3.googleusercontent.com/aida-public/AB6AXuA0IwndwlsYizaHSWCd7fOnZm8wPKtUOsP6B00Fy0VVXk1x36hiK50Ll9oLf-ve0jCPhRLv5hMsxgQ2OUWk7grdM_DKbU8XwcXKPZlhFMFjU4kLJNFguM8iP2WqCblallVUBTHznm9F4LBs_Ec7oKFwx-Ya4_cMWkVvc_NHSIGPKlutmCEb6U3n6mNAFKFaHWMbUmDFXJhDi-IcGhGA64fKnrtQQpqoZcljBPygG0OtG3tqUmC6SoUR', 6);
    ");

    // Products
    $pdo->exec("
    INSERT INTO products (id, slug, title, subtitle, category_id, silhouette, fabric, occasion, price, original_price, discount_percent, rating, review_count, badge, badge_type, primary_image, model_info, craftsmanship, care_instructions, is_featured, is_bestseller, is_new_arrival, stock_quantity) VALUES
    (1, 'miraya-pleated-wine-maxi', 'The Miraya Pleated Mulberry Silk Maxi', 'Handcrafted Tiered Mulberry Silk Gown with Knife Pleats', 1, 'Maxi', 'Mulmul Silk', 'Cocktail Soirée', 5490.00, 7990.00, 31, 4.9, 142, 'Bestseller', 'primary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuB7N8hI9Ytou4emI8dNENEiOuPOUmIFdZ0uJ6-98Gq1CxxrppuRq9QXPS2iYYWxiorNOfRafXSFcm4Wl1631QPBG4dWuZ8SS0DA-YZNceP60w10o22DD_cL87m_jiWhNQxcFNV5Xc-v81lhkSmKYW35T18djrwy3TT7E8OJcd6XhAj-wWWVg4kyyjsa3lMXTPCA_pmjvN0syuXSsFOoYmS3iapBrD2Wbi4hDf4QtprPZILD6jfynS03', 'Model is 5''9\", Bust 32B, Waist 25\", wearing size S', 'Artisanal 100% Pure Mulberry Silk harvested from heritage sericulture farms in Karnataka. Woven on traditional handlooms with a micro-knife pleat technique that retains dynamic memory and fluid drape.', 'Strictly Professional Dry Clean Only. Store hung inside provided muslin garment bag.', 1, 1, 0, 18),
    (2, 'alia-ivory-draped-silk-midi', 'Alia Ivory Draped Silk Midi', 'Sculptural Asymmetrical Cowl Collar Midi Dress', 1, 'Midi', 'Mulmul Silk', 'Atelier Salon', 6800.00, 6800.00, 0, 4.8, 86, 'New Arrival', 'secondary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDUTViIMU7xBBCA2dVn7D66EzPA2Q9JznFXQpkohehmlgOcrN-KpqPSB0MQvAiBtXgb_FlJR2jABajcMT2PUDQQk9jnuLcVaUwMlXblAYnsfTfQKhLyZzYbkUhRca_VGxkjbMsqXWSxRVTHXDDbo5eTpkZcfc0n5v_3XTKO9G1MxkGx9eqXHY6WTRY_D2v9cxMSmeCCm729Ampi5TGPj_KkoOwEjRPzolKoeH-uc9fhTm8ouGeXhuHh', 'Model is 5''8\", wearing size S', 'Hand-draped pure raw silk with natural unbleached ivory texture. Tailored with seamless French seams and antique brass horn button closure.', 'Dry clean only. Cool iron on reverse side.', 1, 0, 1, 12),
    (3, 'zoya-organza-flared-gown', 'Zoya Organza Flared Gown', 'Dusty Mauve-Wine Flared Ballgown with Hand Zardozi', 1, 'Flared Gown', 'Chiffon Organza', 'Festive Gathering', 8990.00, 11500.00, 22, 4.9, 128, 'Limited', 'tertiary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDX8ywG0lwOLXtWOj0bN0jTSbj4bI3fmDlvvN2_BX3P5crSIToL2V9YimD3lgfn_FIxA6XP4NqxnAN0VYOXKYkGmVjiP3m0iuFO3WRp8hQW6bQQ22bxA5ah-I6J8TVuSqcIqzLQR_IcVHzt1aK0BhcfFVpMc8iHpCM8sNyfn12iEeNBe1MpMtwpGqKln622tcr_Ipi44VJ1YRG5elueWGtceee2vfgL5FrdorKvUIvZZ1dL-4Skp-ZE', 'Model is 5''10\", wearing size M', 'Sheer Italian organza overlaid on mulberry silk satin. Decorated with hand-appliqued silver and rosegold zari along borders.', 'Delicate dry clean only. Do not spray perfume directly on embroidery.', 1, 1, 0, 7),
    (4, 'sultana-kaftan-dress', 'Sultana Embroidered Kaftan Dress', 'Jewel-Toned Emerald Silk Kaftan with Artisanal Neckline', 3, 'Free Silhouette', 'Mulmul Silk', 'Day Luncheon', 4250.00, 5500.00, 23, 4.7, 64, 'Handcrafted', 'secondary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuA5xKTAfw76Qsm-tHvYpYbBrGIgUaEOAjISWOhrdbOh9Luw4HMe6YjDMWfVnsMIbE63isx_-idy_L2mohHMRV5vV-IfdzUoC7yRH5jRJ9Oi5Szmv3vP7AyyrBQ_u209pHmNutdsqDUg5DlTszhNKkUjop11g-gHdBIOMvjUuS3YtQmGfOgfcU4NtBpvW15lMEO_yDa8SriVetN2HMUdLQD4X51fAEL03iK21JL9qm2o5UfM4CAMnCvZ', 'One Size Free Silhouette fits XS to XL comfortably', 'Fluid silk habotai drape with Kashmiri tilla needlework framing collar and batwing sleeve cuffs.', 'Gentle hand wash or dry clean recommended.', 0, 0, 0, 25),
    (5, 'nisha-tiered-brocade-dress', 'Nisha Tiered Brocade Dress', 'Rose Bronze Banarasi Brocade Tiered Empire Silhouette', 1, 'Maxi', 'Mulmul Silk', 'Festive Gathering', 7190.00, 7190.00, 0, 4.9, 52, 'Artisanal', 'secondary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCoxYma9cAYdCjJ_hhO3xuv8fQlS1XZ0cFQmn7I79ScdKDvXLRL01J0lcDl6ARVrrX1VCV02EGZUX1RkYo5Efmjt6Mdz3o-yjdynaTrPSMup-7A3_yr_zeBTiPlNbQQD7KJS7-kD_lQZw12JczOxIdtclPtYKW7C_vrCyR-veu2wQswb08Ium044c9w5LJUeUn0svThHCm-xxqApLr3Ip1G7VhDFtQMWC8v4sdz52iJn1HhkLvkSlht', 'Model is 5''9\", wearing size S', 'Authentic Banarasi handloom brocade woven with tested metallic threads. Features tiered gathers and concealed pockets.', 'Specialist dry clean only.', 0, 1, 0, 10),
    (6, 'meera-corset-georgette', 'Meera Corset Georgette Dress', 'Fitted Corset Georgette with Ruffled Fluid Hem', 3, 'Midi', 'Georgette', 'Cocktail Soirée', 5990.00, 5990.00, 0, 4.8, 77, 'Couture', 'primary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDu5RYLpWeEVVSbLGhifHROYU125fAc88tjQyODyJZt8BA5PEfTPWc2wrVvfWVzx_p4WQdxsYfcJePjlMkeTmRGyJXB8M88oezLtQ9e3COD0ZfOAqE4B6BClNE5yqpKCIFX-YzJHNSWA8KAB3PvgtLCiRWmbkuz1SGdcyAuP08C5_Ds59f_NM6jgSShVBGyuuMnQMXycLnxOdQVuUi_NTsdG8V-MvLT5VehNsggQ9eJbQk4njAI4T-8', 'Model is 5''8\", wearing size XS', 'Viscose georgette with hand-stitched boned corset structure and invisible zipper. Inner lining crafted in pure cotton mulmul.', 'Professional dry clean only.', 0, 0, 1, 14),
    (7, 'aarohi-tiered-georgette', 'Aarohi Tiered Georgette Maxi', 'Cascading Layers in Deep Wine Tones with Rosegold Accents', 1, 'Maxi', 'Georgette', 'Festive Gathering', 4890.00, 6990.00, 30, 4.9, 110, 'Exclusive', 'primary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBGLa2zQ4vA4vtflWHZ7Pr1qN78MFOSrlUYQw5Mrutcj3zp0WEtPhILfwjdnkdf7zqdnAGknie0B0tCb_JzDVyaGD15ZLz4zVYqOLzWpBbRF2elLnLMmwgG1bSdBx3RCLqoTbCHvQ-zR9r_xUArVJTHQ-z1WmwzhHGA2nbY-e-TtOKizyjJAewvoufvteDkiog2AKzUC-smkuI7cqmjdbJjJ9RBAjDtLhBSQg_cOhH5Hp97drSW4I5r', 'Model is 5''9\", wearing size S', 'Micro-pleated tier design finished with subtle scalloped hand embroidery.', 'Dry clean only.', 1, 1, 0, 16),
    (8, 'zuri-wine-silk-midi', 'Zuri Wine Silk Midi', 'Artisanal Neck Pleating with Mulberry Silk Body', 1, 'Midi', 'Mulmul Silk', 'Atelier Salon', 6450.00, 7500.00, 14, 4.9, 95, 'Hand-Embroidered', 'secondary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCTcJXEtsH0hlCU-tNtHyFjvjKX1zMHu8ZQMteQU2x5V_svAcpzoYgCW3PZvhIiRZppVswX_51L3d7piFO6vbbDUpQiUMkBq4aU31MTJ-wuSjiTuBlzreEow9xq6gOcsmTeUEr7RH7qYVk9gLrKh_sS4J5NpdOIyBK3KEtGHmxi7K4AC5ziI8WCZG0wg_7A_XH0-KIvOJqGBrLPcudCmUeVvUvj-4Ie8RyXKonztSgszCDLw3XqtRwD', 'Model is 5''8\", wearing size S', 'Hand-tucked origami collar pleats on organic mulberry silk.', 'Dry clean only.', 1, 1, 0, 11),
    (9, 'tara-draped-cape-set', 'Tara Draped Cape Set', 'Statement Draped Cape with Ankle Silhouette Trousers', 2, 'Anarkali Dress', 'Mulmul Silk', 'Festive Gathering', 6290.00, 8990.00, 30, 4.9, 128, '-30%', 'error', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAGrHW8pYGzPn--Ar8VvvBLji3-CKoWltKhOkfG8yD3bUvEe6kkFr0ZBqY-ZTVCQaFHsIdxa0aJ-hI_3hWFs4bVeK_vnzZCjyFYYHtQUfmJxwWNLm2K1nOII9i-Fx16AE6Jm4bemcDiDMMQ8rlqi-mdS9ebrZlVZrXE9tHKJVOgbWVKqzGKY6Sia9o4PdNhKiHzMcQjAXhGpDEYMPdApeUwL_pi2VJCmJfXlyrR89iPv7B3Jw0oJf1r', 'Model is 5''9\", wearing size S', 'Flowing silk chiffon cape with hand-tasseled edges and silk cigarette trousers.', 'Dry clean only.', 1, 1, 0, 9),
    (10, 'nitya-chanderi-co-ord', 'Nitya Chanderi Co-ord', 'Subtle Gold Embroidery on Handspun Chanderi Weave', 4, 'Midi', 'Cotton Linen', 'Day Luncheon', 5490.00, 7800.00, 29, 4.9, 94, 'Iconic', 'primary', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD_SEK3-BVHwoPQCiH0STmZxLup3UEP0Py8knyJqhb70VZUTTfjRMbPy2yU3NxWKUj4uP1kKWvgLJKeL7Y9tBQr0AHYeBnlBaffaPbnJV10eRqSI1cgt8R3Eajypuu1TJEgmu_vkLARCXRwtcT3C3rx3DU_ce1CTP3hFv3kZhWK07LwXRkMW0QgXn0NdBrbypFN-5WGUV1Fh76RDqJdxGWJRxDznpA2Izxdh1MFge1l1feRnIKMt5yQ', 'Model is 5''8\", wearing size M', 'Handspun Madhya Pradesh Chanderi silk-cotton with zari buttis.', 'Dry clean or cold gentle hand soak.', 1, 1, 0, 15);
    ");

    // Images
    $pdo->exec("
    INSERT INTO product_images (product_id, image_url, alt_text, display_order, is_video_preview) VALUES
    (1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuB7N8hI9Ytou4emI8dNENEiOuPOUmIFdZ0uJ6-98Gq1CxxrppuRq9QXPS2iYYWxiorNOfRafXSFcm4Wl1631QPBG4dWuZ8SS0DA-YZNceP60w10o22DD_cL87m_jiWhNQxcFNV5Xc-v81lhkSmKYW35T18djrwy3TT7E8OJcd6XhAj-wWWVg4kyyjsa3lMXTPCA_pmjvN0syuXSsFOoYmS3iapBrD2Wbi4hDf4QtprPZILD6jfynS03', 'Miraya Silk Maxi - Front View', 0, 0),
    (1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDZfCFtItHe_6zf0hssdafMuXm7J-rjRdfppkVsohAG5-l3hraxtTwmLK8g9M1ZfjCFvz_vrcRytP5WyO0vYdWIzZUXmQ0MP07wWWFgUc2WXR3ou6JvyfgpFIn3AjYX2rywIZ073rkx9C59keJ1tdUx8aQGU9jLGRLsdKaI4I98nGqAlTWLkTJajlaSy5iauQVt1y7RZ0c70-BIIdtHCu7HT9DBRYiPdmISuYuwrCEY6b2-grIyvZSX', 'Miraya Silk Maxi - Studio Portrait Close-up', 1, 0),
    (1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuBmQsjH47jIUdFN3wvPptl9ocusBZWKHpE-oEKvqWipHNq4Re3oekmasN2X6J_3BN-gtG5S94lBlEYyeDK24TkpxXj5QmOQCGrvwOYPBCXCARfCe5kaT0VyuOcotjTyf8KJesRdU1VrXHc6CJ-5_40vIYiRojQLFxMr9NrsAXPhBpYSHV7DB-eiPXlqcDVIAeEc5d0ixAq-RYhY25jUL72aN3CXUxFF-W-KsT-5np2fZLlQeNsYociW', 'Miraya Silk Maxi - Draped Cascade Back', 2, 0),
    (1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDV7KOCGOYnw-HisRwFVeqYvh1rNiD1BTxWmCwMG7vnQEdauyQaRIEy02FMGEkxSXl0mULPcDdyRb6pLLZUxehvLC7ZRKqrYc2CnigfA3nm6Jjx1xe3niITenGrVM9YZCHOAGfj-S4tRI9TW5mPBWu9dsHVMx9D1l23jrC1C9JNtF4z00GVzpRaz0HOuRRoC0VYfBl2p2JZ672g3NfbBoJDTf8YzI1_FbSjB5H7Cttr7Dmr9zFH7e5A', 'Miraya Silk Maxi - Macro Zari & Pleat Weave Texture', 3, 0),
    (1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuB2O7Krd5_mEAsSJNVige6v-Viiao9lqUFu8GRexCObJ88JI6KGxfGEXAf9tYQL8BZVu_Bc7l4n6zSr9T1BvmWWhUbf-xD-MeyZ56wBN7KyoBAxEFee9_f5oRm-Ip6C_apv0vYWlW2kfq9ddPrBG3titHG2VxfYyp0Pwk8cLhKgKjcEG2cJUrlGw4Rwgxc1a-pX6VE5KKkxApAQnxaufkZPxJKRTUFUMKkXT6gcGoBDNuHaLFQrGaTi', 'Miraya Silk Maxi - Fluid Runway Motion', 4, 1);
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
    (1, 'Ananya Goswami', 'AG', 'Verified Connoisseur', 5, 'The weight of this silk maxi is pure poetry. I wore it for my sister''s Sangeet in Jaipur and received countless compliments. The pleats fall like liquid rubies.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDhCztGVZxdBQtdnceHv7RgAlXdgWgEb_VzMkR5X49M0UqPewTV0ljVnwtDacm0EQOC_1sBBhTrM7IIWaFxijwjkO-R-5l-_REKEUXdGfZCqPAJIaYVCouLcXttrQWHbgRf3KF2v0e_3AGUID7GWNLwRPiEY7Fsq0nw8CZTq9XPK6ZZKTbcZ0aPHALWuyA5moPljxAhDFtdTj5SF_cgGvlmtMkpA3iIKmkrW3b6IkMA8Oc6pAdJ_dip'),
    (1, 'Meera Ranganathan', 'MR', 'Verified Connoisseur', 5, 'Impeccable cut. I was nervous about ordering size M online, but the complimentary doorstep alteration guarantee made it completely risk-free. Fits like couture!', NULL),
    (1, 'Tara Oberoi', 'TO', 'Verified Connoisseur', 5, 'The zari detailing along the micro-pleats reflects candlelight in such a subtle, understated way. Exactly what quiet luxury should feel like.', NULL);
    ");

    // Bundled
    $pdo->exec("
    INSERT INTO bundled_accessories (parent_product_id, name, tag, price, image_url) VALUES
    (1, 'Velora Gold Filigree Choker', 'Stylist Pick', 2190.00, 'https://lh3.googleusercontent.com/aida-public/AB6AXuAW-JTYqFYIC9OOucBE2fz4TXcgOevVxIWUSMS7Iv1ubMPMlLs0sSESl5FSV7Ovl6X6SPRAd0vHYvUfQG34dO7xd5GHih6bKnhLOLUPuF154j9fsInT0i2tg0TJN6DzEk8KwR8S9VNlTNFiEejokRELT1Xi7LYF9xB-H0YnheReGpCx4Ak61Pji38h_J7PYJfTnvJ73dsCb-I0dk3eElcSX3_PqStA-yTEMR9QIMORH_BrtN7d2FILr'),
    (1, 'Velvet Minaudière Clutch', 'Handcrafted', 3490.00, 'https://lh3.googleusercontent.com/aida-public/AB6AXuD8upy5NViSmssBTwDnDkxnWsT_6JsA4lOzO9uENXaiqka2AMoVzqj6q8loJn7srZzWe2jAaARJROkdLMSgQAa0VtjQbrS2M4lUYAP9eGBE41k9EjTtBGT8h3DnAgFCZRKO1aZAKXbSjs9Sw_drzWzxjNS7RieiDdKkAie5x8I2cCulwrqJxsAsaRi3nVDS8yW9XrYYecHfajpDlE39WEPk6z9jeCPRwltuJeCWkMCpgbaK8tcHB4xI');
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
