# VELORA — Luxury Fashion Boutique
### Full Stack Export (HTML, CSS, JavaScript, PHP, MySQL)
**Exported from Stitch Project:** `projects/1541379722541090552`  
**Brand Identity:** Haute-Couture Indian Heritage Atelier & Fluid Silk Silhouettes  
**Design Theme:** Warm Couture Editorial (Playfair Display + Plus Jakarta Sans, Wine Burgundy `#541626`, Muted Antique Gold `#745A34`, Warm Ivory `#FEF8F4`)

---

## 📁 Project Architecture & Files

```
velora-luxury-boutique/
├── index.php                 # Editorial Home & Landing Page
├── shop.php                  # Shop Catalogue with live filters & sorting
├── product.php               # Dynamic Product Detail & Gallery (e.g. ?id=1)
├── cart.php                  # Shopping Bag with voucher applicator
├── checkout.php              # Express White-Glove Checkout & payment selector
├── order-success.php         # Order Receipt & Confirmation view
├── wishlist.php              # Saved Pieces / Wishlist page
│
├── config/
│   └── db.php                # PDO connection (MySQL + auto-fallback to SQLite)
│
├── database/
│   ├── velora_db.sql         # Complete MySQL schema & seed data
│   └── velora.sqlite         # Local SQLite database (auto-generated)
│
├── api/
│   ├── products.php          # REST API for products, search, & filters
│   ├── cart.php              # REST API for bag additions, quantity, & vouchers
│   ├── wishlist.php          # REST API for wishlist toggle & listing
│   └── checkout.php          # REST API for order processing
│
├── includes/
│   ├── header.php            # Announcement strip, masthead, & counters
│   ├── footer.php            # Gazette newsletter, salons, & mobile nav
│   └── functions.php         # Helpers (formatting, sessions, counters)
│
└── assets/
    ├── css/
    │   └── style.css         # Warm Couture Editorial styling & animations
    └── js/
        └── main.js           # AJAX cart, wishlist, toasts, quick view modal
```

---

## 🚀 Instant Quickstart (Zero Configuration)

You can run the application immediately with PHP's built-in development server:

1. Open PowerShell or Command Prompt.
2. Navigate to this directory:
   ```powershell
   cd "velora-luxury-boutique"
   ```
3. Start the PHP server:
   ```powershell
   php -S localhost:8000
   ```
4. Open your browser and navigate to:
   ```
   http://localhost:8000
   ```

> **Note on Database:** The application includes an intelligent dual-mode PDO driver. If a MySQL server is not currently running locally, it seamlessly auto-initializes and seeds a local SQLite database (`database/velora.sqlite`), so you can test every feature (cart, wishlist, checkout, filters) immediately without configuring a database server!

---

## 🗄️ Setting Up with MySQL (XAMPP / WAMP / Production)

When you're ready to deploy to MySQL or phpMyAdmin:

### 1. Create Database & Import Schema
1. Open **phpMyAdmin** or your MySQL command line:
   ```sql
   CREATE DATABASE velora_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE velora_db;
   ```
2. Import the provided SQL script:
   - File location: `database/velora_db.sql`
   - In phpMyAdmin: Click **Import** -> Choose file `database/velora_db.sql` -> Click **Go**.
   - Or via MySQL CLI:
     ```bash
     mysql -u root -p velora_db < database/velora_db.sql
     ```

### 2. Configure Database Credentials
Edit `config/db.php`:
```php
define('DB_HOST', '127.0.0.1'); // Database Host
define('DB_PORT', '3306');      // Database Port
define('DB_NAME', 'velora_db');  // Database Name
define('DB_USER', 'root');       // Database Username
define('DB_PASS', '');           // Database Password (default empty on XAMPP)
```

---

## ✨ Features Included

1. **Editorial Home Page (`index.php`)**:
   - Announcement bar with discount code `VELORA10`
   - Haute-couture hero banner with Lookbook exploration link
   - Curated story category circles (Silk Gowns, Bespoke Kurtas, Cocktail, Handwoven, Bridal Edit)
   - Limited edition drops with instant Quick Add
   - Atelier Manifesto quote & muse editorial
   - Bestsellers showcase with live color swatches
   - Newsletter voucher claim for ₹500 off (`VELORA500`)

2. **Shop Catalogue (`shop.php`)**:
   - Live category filtering (Silk Gowns, Kurtas, Cocktail, Handwoven, Bridal)
   - Sorting by: Featured, Newest, Price Low to High, Price High to Low, Best Selling
   - Filter drawer with Silhouette, Fabric, and Occasion multi-select
   - Active removable tag pills
   - Quick View modal preview
   - Wishlist toggle on every card

3. **Product Details Page (`product.php`)**:
   - Multi-image gallery with zoom/runway preview and thumbnail switcher
   - Colorway selection with active check feedback
   - Size selector (XS, S, M, L, XL, XXL) with low stock alert
   - Size guide table modal (Bust, Waist, Hip)
   - Real-time Pincode delivery and alteration estimator
   - Craftsmanship, bespoke fit, care, and 7-day return accordions
   - Complete The Look styling accessory bundle
   - Customer ratings breakdown and verified connoisseur photo reviews

4. **Shopping Bag & Checkout (`cart.php`, `checkout.php`, `order-success.php`)**:
   - Real-time quantity adjustment & deletion
   - Promo code validation (`VELORA10` for 10% off, `VELORA500` for ₹500 off)
   - Complimentary shipping threshold calculation
   - White-glove checkout with Cash on Delivery, Instant UPI, and Card selection
   - Order receipt with unique reference number (e.g. `VEL-20261021-XXXX`)

5. **REST API (`/api/`)**:
   - `GET /api/products.php` (Listing, search, filters, and detail)
   - `GET /api/cart.php` & `POST /api/cart.php?action=add|update|remove|apply_coupon`
   - `GET /api/wishlist.php` & `POST /api/wishlist.php?action=toggle`
   - `POST /api/checkout.php`
