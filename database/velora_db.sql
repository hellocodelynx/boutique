-- =====================================================================
-- VELORA Luxury Fashion Boutique - MySQL Database Schema & Seed Data
-- Database Name: velora_db
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `velora_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `velora_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Table: categories
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `image_url` VARCHAR(500) NULL,
  `item_count` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: products
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `silhouette` VARCHAR(100) NULL,
  `fabric` VARCHAR(150) NULL,
  `occasion` VARCHAR(150) NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `original_price` DECIMAL(10,2) NULL,
  `discount_percent` INT UNSIGNED DEFAULT 0,
  `rating` DECIMAL(2,1) DEFAULT 4.9,
  `review_count` INT UNSIGNED DEFAULT 0,
  `badge` VARCHAR(50) NULL,
  `badge_type` ENUM('primary', 'secondary', 'tertiary', 'error') DEFAULT 'primary',
  `primary_image` VARCHAR(500) NOT NULL,
  `model_info` VARCHAR(255) DEFAULT 'Model is 5\'9", Bust 32B, Waist 25", wearing size S',
  `craftsmanship` TEXT NULL,
  `care_instructions` TEXT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_bestseller` TINYINT(1) DEFAULT 0,
  `is_new_arrival` TINYINT(1) DEFAULT 0,
  `stock_quantity` INT UNSIGNED DEFAULT 15,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: product_images
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `alt_text` VARCHAR(255) NULL,
  `display_order` INT UNSIGNED DEFAULT 0,
  `is_video_preview` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: product_colors
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `product_colors`;
CREATE TABLE `product_colors` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `color_name` VARCHAR(100) NOT NULL,
  `hex_code` VARCHAR(10) NOT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: product_sizes
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `product_sizes`;
CREATE TABLE `product_sizes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `size_code` VARCHAR(10) NOT NULL,
  `stock_count` INT UNSIGNED DEFAULT 5,
  `low_stock_threshold` INT UNSIGNED DEFAULT 2,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: reviews
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `author_name` VARCHAR(150) NOT NULL,
  `author_initials` VARCHAR(5) NOT NULL,
  `author_badge` VARCHAR(100) DEFAULT 'Verified Connoisseur',
  `rating` INT UNSIGNED DEFAULT 5,
  `comment` TEXT NOT NULL,
  `customer_image` VARCHAR(500) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: bundled_accessories
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `bundled_accessories`;
CREATE TABLE `bundled_accessories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_product_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `tag` VARCHAR(50) DEFAULT 'Stylist Pick',
  `price` DECIMAL(10,2) NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  FOREIGN KEY (`parent_product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: coupons
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('percentage', 'flat') DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_spend` DECIMAL(10,2) DEFAULT 0,
  `description` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: cart_items
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(128) NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `color_name` VARCHAR(100) NULL,
  `size_code` VARCHAR(10) NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: wishlist
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(128) NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `session_product` (`session_id`, `product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: orders
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(10) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'COD',
  `subtotal` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `shipping_fee` DECIMAL(10,2) DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `coupon_code` VARCHAR(50) NULL,
  `order_status` ENUM('confirmed', 'processing', 'in_transit', 'delivered', 'cancelled') DEFAULT 'confirmed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: order_items
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `product_title` VARCHAR(255) NOT NULL,
  `color_name` VARCHAR(100) NULL,
  `size_code` VARCHAR(10) NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: admins
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `role` VARCHAR(100) DEFAULT 'Head Couturier',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: newsletter_subscribers
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `coupon_awarded` VARCHAR(50) DEFAULT 'VELORA500',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SEED DATA
-- =====================================================================

-- Categories
INSERT INTO `categories` (`id`, `slug`, `name`, `description`, `image_url`, `item_count`) VALUES
(1, 'silk-gowns', 'Silk Gowns', 'Handcrafted fluid mulberry silk gowns with sculptural drapes', 'assets/img/category-silk-gowns.jpg', 16),
(2, 'kurtas', 'Bespoke Kurtas', 'Intricately embroidered festive kurtas in soft heritage silks', 'assets/img/category-kurtas.jpg', 12),
(3, 'cocktail', 'Cocktail Dresses', 'Contemporary asymmetric silhouettes designed for evening salons', 'assets/img/category-cocktail.jpg', 8),
(4, 'handwoven', 'Handwoven Co-ords', 'Artisanal loom pieces tailored in breathable organic cottons', 'assets/img/category-handwoven.jpg', 10),
(5, 'bridal-edit', 'Bridal & Heirloom', 'Regal heirloom ensembles embroidered with antique gold zardozi', 'assets/img/category-bridal-edit.jpg', 6);

-- Products
INSERT INTO `products` (`id`, `slug`, `title`, `subtitle`, `category_id`, `silhouette`, `fabric`, `occasion`, `price`, `original_price`, `discount_percent`, `rating`, `review_count`, `badge`, `badge_type`, `primary_image`, `model_info`, `craftsmanship`, `care_instructions`, `is_featured`, `is_bestseller`, `is_new_arrival`, `stock_quantity`) VALUES
(1, 'miraya-pleated-wine-maxi', 'The Miraya Pleated Mulberry Silk Maxi', 'Handcrafted Tiered Mulberry Silk Gown with Knife Pleats', 1, 'Maxi', 'Mulmul Silk', 'Cocktail Soirée', 5490.00, 7990.00, 31, 4.9, 142, 'Bestseller', 'primary', 'assets/img/miraya-pleated-wine-maxi.jpg', 'Model is 5\'9", Bust 32B, Waist 25", wearing size S', 'Artisanal 100% Pure Mulberry Silk harvested from heritage sericulture farms in Karnataka. Woven on traditional handlooms with a micro-knife pleat technique that retains dynamic memory and fluid drape.', 'Strictly Professional Dry Clean Only. Store hung inside provided muslin garment bag.', 1, 1, 0, 18),
(2, 'alia-ivory-draped-silk-midi', 'Alia Ivory Draped Silk Midi', 'Sculptural Asymmetrical Cowl Collar Midi Dress', 1, 'Midi', 'Mulmul Silk', 'Atelier Salon', 6800.00, 6800.00, 0, 4.8, 86, 'New Arrival', 'secondary', 'assets/img/alia-ivory-draped-silk-midi.jpg', 'Model is 5\'8", wearing size S', 'Hand-draped pure raw silk with natural unbleached ivory texture. Tailored with seamless French seams and antique brass horn button closure.', 'Dry clean only. Cool iron on reverse side.', 1, 0, 1, 12),
(3, 'zoya-organza-flared-gown', 'Zoya Organza Flared Gown', 'Dusty Mauve-Wine Flared Ballgown with Hand Zardozi', 1, 'Flared Gown', 'Chiffon Organza', 'Festive Gathering', 8990.00, 11500.00, 22, 4.9, 128, 'Limited', 'tertiary', 'assets/img/zoya-organza-flared-gown.jpg', 'Model is 5\'10", wearing size M', 'Sheer Italian organza overlaid on mulberry silk satin. Decorated with hand-appliqued silver and rosegold zari along borders.', 'Delicate dry clean only. Do not spray perfume directly on embroidery.', 1, 1, 0, 7),
(4, 'sultana-kaftan-dress', 'Sultana Embroidered Kaftan Dress', 'Jewel-Toned Emerald Silk Kaftan with Artisanal Neckline', 3, 'Free Silhouette', 'Mulmul Silk', 'Day Luncheon', 4250.00, 5500.00, 23, 4.7, 64, 'Handcrafted', 'secondary', 'assets/img/sultana-kaftan-dress.jpg', 'One Size Free Silhouette fits XS to XL comfortably', 'Fluid silk habotai drape with Kashmiri tilla needlework framing collar and batwing sleeve cuffs.', 'Gentle hand wash or dry clean recommended.', 0, 0, 0, 25),
(5, 'nisha-tiered-brocade-dress', 'Nisha Tiered Brocade Dress', 'Rose Bronze Banarasi Brocade Tiered Empire Silhouette', 1, 'Maxi', 'Mulmul Silk', 'Festive Gathering', 7190.00, 7190.00, 0, 4.9, 52, 'Artisanal', 'secondary', 'assets/img/nisha-tiered-brocade-dress.jpg', 'Model is 5\'9", wearing size S', 'Authentic Banarasi handloom brocade woven with tested metallic threads. Features tiered gathers and concealed pockets.', 'Specialist dry clean only.', 0, 1, 0, 10),
(6, 'meera-corset-georgette', 'Meera Corset Georgette Dress', 'Fitted Corset Georgette with Ruffled Fluid Hem', 3, 'Midi', 'Georgette', 'Cocktail Soirée', 5990.00, 5990.00, 0, 4.8, 77, 'Couture', 'primary', 'assets/img/meera-corset-georgette.jpg', 'Model is 5\'8", wearing size XS', 'Viscose georgette with hand-stitched boned corset structure and invisible zipper. Inner lining crafted in pure cotton mulmul.', 'Professional dry clean only.', 0, 0, 1, 14),
(7, 'aarohi-tiered-georgette', 'Aarohi Tiered Georgette Maxi', 'Cascading Layers in Deep Wine Tones with Rosegold Accents', 1, 'Maxi', 'Georgette', 'Festive Gathering', 4890.00, 6990.00, 30, 4.9, 110, 'Exclusive', 'primary', 'assets/img/aarohi-tiered-georgette.jpg', 'Model is 5\'9", wearing size S', 'Micro-pleated tier design finished with subtle scalloped hand embroidery.', 'Dry clean only.', 1, 1, 0, 16),
(8, 'zuri-wine-silk-midi', 'Zuri Wine Silk Midi', 'Artisanal Neck Pleating with Mulberry Silk Body', 1, 'Midi', 'Mulmul Silk', 'Atelier Salon', 6450.00, 7500.00, 14, 4.9, 95, 'Hand-Embroidered', 'secondary', 'assets/img/zuri-wine-silk-midi.jpg', 'Model is 5\'8", wearing size S', 'Hand-tucked origami collar pleats on organic mulberry silk.', 'Dry clean only.', 1, 1, 0, 11),
(9, 'tara-draped-cape-set', 'Tara Draped Cape Set', 'Statement Draped Cape with Ankle Silhouette Trousers', 2, 'Anarkali Dress', 'Mulmul Silk', 'Festive Gathering', 6290.00, 8990.00, 30, 4.9, 128, '-30%', 'error', 'assets/img/tara-draped-cape-set.jpg', 'Model is 5\'9", wearing size S', 'Flowing silk chiffon cape with hand-tasseled edges and silk cigarette trousers.', 'Dry clean only.', 1, 1, 0, 9),
(10, 'nitya-chanderi-co-ord', 'Nitya Chanderi Co-ord', 'Subtle Gold Embroidery on Handspun Chanderi Weave', 4, 'Midi', 'Cotton Linen', 'Day Luncheon', 5490.00, 7800.00, 29, 4.9, 94, 'Iconic', 'primary', 'assets/img/nitya-chanderi-co-ord.jpg', 'Model is 5\'8", wearing size M', 'Handspun Madhya Pradesh Chanderi silk-cotton with zari buttis.', 'Dry clean or cold gentle hand soak.', 1, 1, 0, 15);

-- Product Additional Gallery Images (for Miraya product 1)
INSERT INTO `product_images` (`product_id`, `image_url`, `alt_text`, `display_order`, `is_video_preview`) VALUES
(1, 'assets/img/miraya-pleated-wine-maxi.jpg', 'Miraya Silk Maxi - Front View', 0, 0),
(1, 'assets/img/miraya-studio-portrait.jpg', 'Miraya Silk Maxi - Studio Portrait Close-up', 1, 0),
(1, 'assets/img/miraya-back-drape.jpg', 'Miraya Silk Maxi - Draped Cascade Back', 2, 0),
(1, 'assets/img/miraya-zari-detail.jpg', 'Miraya Silk Maxi - Macro Zari & Pleat Weave Texture', 3, 0),
(1, 'assets/img/miraya-runway.jpg', 'Miraya Silk Maxi - Fluid Runway Motion', 4, 1);

-- Product Colors
INSERT INTO `product_colors` (`product_id`, `color_name`, `hex_code`, `is_default`) VALUES
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

-- Product Sizes
INSERT INTO `product_sizes` (`product_id`, `size_code`, `stock_count`, `low_stock_threshold`) VALUES
(1, 'XS', 4, 2),
(1, 'S', 2, 2),
(1, 'M', 7, 2),
(1, 'L', 3, 2),
(1, 'XL', 2, 2),
(1, 'XXL', 1, 2),
(2, 'S', 5, 2),
(2, 'M', 4, 2),
(2, 'L', 3, 2),
(3, 'XS', 2, 2),
(3, 'S', 3, 2),
(3, 'M', 2, 2),
(4, 'Free', 25, 2),
(5, 'S', 3, 2),
(5, 'M', 4, 2),
(5, 'L', 3, 2),
(6, 'XS', 3, 2),
(6, 'S', 5, 2),
(6, 'M', 4, 2);

-- Reviews
INSERT INTO `reviews` (`product_id`, `author_name`, `author_initials`, `author_badge`, `rating`, `comment`, `customer_image`, `created_at`) VALUES
(1, 'Ananya Goswami', 'AG', 'Verified Connoisseur', 5, 'The weight of this silk maxi is pure poetry. I wore it for my sister\'s Sangeet in Jaipur and received countless compliments. The pleats fall like liquid rubies.', 'assets/img/review-ananya-goswami.jpg', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'Meera Ranganathan', 'MR', 'Verified Connoisseur', 5, 'Impeccable cut. I was nervous about ordering size M online, but the complimentary doorstep alteration guarantee made it completely risk-free. Fits like couture!', NULL, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 'Tara Oberoi', 'TO', 'Verified Connoisseur', 5, 'The zari detailing along the micro-pleats reflects candlelight in such a subtle, understated way. Exactly what quiet luxury should feel like.', NULL, DATE_SUB(NOW(), INTERVAL 12 DAY));

-- Bundled Accessories for Product 1
INSERT INTO `bundled_accessories` (`parent_product_id`, `name`, `tag`, `price`, `image_url`) VALUES
(1, 'Velora Gold Filigree Choker', 'Stylist Pick', 2190.00, 'assets/img/velora-gold-filigree-choker.jpg'),
(1, 'Velvet Minaudière Clutch', 'Handcrafted', 3490.00, 'assets/img/velvet-minaudiere-clutch.jpg');

-- Promotional Coupons
INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_spend`, `description`, `is_active`) VALUES
('VELORA10', 'percentage', 10.00, 2999.00, '10% complimentary discount on orders above ₹2,999', 1),
('VELORA500', 'flat', 500.00, 3000.00, 'Flat ₹500 off for Atelier Circle members', 1),
('FESTIVE15', 'percentage', 15.00, 5000.00, '15% off festive orders above ₹5,000', 1);

-- Sample initial Cart & Wishlist items to replicate the Stitch prototype state
INSERT INTO `cart_items` (`session_id`, `product_id`, `color_name`, `size_code`, `quantity`, `price`) VALUES
('demo_session', 1, 'Royal Wine Burgundy', 'S', 1, 5490.00),
('demo_session', 8, 'Wine', 'S', 1, 2000.00);

INSERT INTO `wishlist` (`session_id`, `product_id`) VALUES
('demo_session', 1),
('demo_session', 7),
('demo_session', 9);

-- Default Admin User (Password: admin123)
INSERT INTO `admins` (`username`, `email`, `password_hash`, `full_name`, `role`) VALUES
('admin', 'admin@velora.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Eleanor Vance', 'Head Couturier');

-- Initial Atelier Orders
INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `city`, `pincode`, `payment_method`, `subtotal`, `discount_amount`, `shipping_fee`, `total_amount`, `coupon_code`, `order_status`, `created_at`) VALUES
(1, 'VEL-8921', 'Ananya Goswami', 'ananya.g@velora-vip.in', '+91 98201 44821', 'Bungalow 4, Perry Cross Road, Bandra West', 'Mumbai', '400050', 'UPI (ananya@okhdfcbank)', 5490.00, 0.00, 0.00, 5490.00, NULL, 'processing', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 'VEL-8924', 'Devika Mehra', 'devika.m@atelier.co.in', '+91 98110 33924', 'Suite 12, Golf Links', 'New Delhi', '110021', 'Amex Platinum', 6290.00, 500.00, 0.00, 5790.00, 'VELORA500', 'processing', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(3, 'VEL-8923', 'Pooja Singhania', 'pooja.singhania@heritage.org', '+91 99002 88923', 'Penthouse A, Lavelle Road', 'Bengaluru', '560001', 'NetBanking (ICICI Private)', 8990.00, 899.00, 0.00, 8091.00, 'VELORA10', 'in_transit', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 'VEL-8920', 'Meera Kapoor', 'meera.k@kapoorlux.com', '+91 97011 55920', 'Road No. 36, Jubilee Hills', 'Hyderabad', '500034', 'Visa Infinite', 6800.00, 0.00, 0.00, 6800.00, NULL, 'delivered', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 'VEL-8919', 'Radhika Sharma', 'radhika.s@sharmaholdings.in', '+91 94140 22919', 'Heritage Villa, Civil Lines', 'Jaipur', '302001', 'Cash on Delivery (White Glove)', 4250.00, 0.00, 0.00, 4250.00, NULL, 'confirmed', DATE_SUB(NOW(), INTERVAL 3 DAY));

INSERT INTO `order_items` (`order_id`, `product_id`, `product_title`, `color_name`, `size_code`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 'The Miraya Pleated Mulberry Silk Maxi', 'Royal Wine Burgundy', 'M', 1, 5490.00, 5490.00),
(2, 9, 'Tara Draped Cape Set', 'Wine', 'S', 1, 6290.00, 6290.00),
(3, 3, 'Zoya Organza Flared Gown', 'Dusty Mauve Wine', 'M', 1, 8990.00, 8990.00),
(4, 2, 'Alia Ivory Draped Silk Midi', 'Pure Ivory', 'L', 1, 6800.00, 6800.00),
(5, 4, 'Sultana Embroidered Kaftan Dress', 'Forest Emerald', 'Free', 1, 4250.00, 4250.00);
