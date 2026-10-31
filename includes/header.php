<?php
if (!defined('VELORA_ACCESS')) {
    define('VELORA_ACCESS', true);
}
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
$pageTitle = $pageTitle ?? 'VELORA — Wear Your Story | Luxury Fashion Boutique';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" name="viewport"/>
  <meta content="VELORA Luxury Fashion Boutique - Handcrafted silhouetted celebrating modern Indian heritage and poetic couture drapes." name="description"/>
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Google Fonts: Playfair Display & Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  
  <!-- Material Symbols Outlined -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
  
  <!-- Tailwind CSS Engine -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#541626",
            "primary-container": "#702C3B",
            "on-primary": "#ffffff",
            "on-primary-container": "#f195a5",
            secondary: "#745a34",
            "secondary-container": "#fedaaa",
            "on-secondary": "#ffffff",
            "on-secondary-container": "#785e37",
            "secondary-fixed": "#ffddb0",
            tertiary: "#2d2d2a",
            "tertiary-container": "#434340",
            "on-tertiary": "#ffffff",
            surface: "#fef8f4",
            "surface-dim": "#dfd9d5",
            "surface-container-lowest": "#ffffff",
            "surface-container-low": "#f9f2ef",
            "surface-container": "#f3ede9",
            "surface-container-high": "#ede7e3",
            "surface-container-highest": "#e7e1de",
            "on-surface": "#1d1b19",
            "on-surface-variant": "#534345",
            outline: "#867275",
            "outline-variant": "#d8c1c3",
            error: "#ba1a1a",
            "error-container": "#ffdad6",
            "on-error": "#ffffff",
            "inverse-surface": "#32302e",
            "inverse-on-surface": "#f6f0ec"
          },
          fontFamily: {
            serif: ["Playfair Display", "serif"],
            sans: ["Plus Jakarta Sans", "sans-serif"]
          }
        }
      }
    };
  </script>

  <!-- VELORA Luxury Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body class="bg-surface font-sans text-on-surface antialiased flex flex-col min-h-screen">

  <!-- Header -->
  <header class="fixed top-0 inset-x-0 z-50 pt-safe bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container-high/60">
    <!-- Top Announcement Strip -->
    <div class="bg-primary text-on-primary py-1.5 px-4 flex items-center justify-center text-center">
      <p class="font-label-caps tracking-widest text-[10px] sm:text-[11px] uppercase truncate">
        Complimentary Pan-India Shipping Above ₹2,999 • Code: <span class="font-bold text-secondary-fixed">VELORA10</span>
      </p>
    </div>

    <!-- Main Navigation Bar -->
    <div class="h-20 max-w-velora px-4 sm:px-8 flex items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <a href="shop.php" aria-label="Menu Toggle" class="w-11 h-11 flex items-center justify-center text-on-surface hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[24px]">menu</span>
        </a>
        <a href="index.php" class="flex items-center gap-2 group">
          <img alt="VELORA Luxury Logo" class="h-8 w-auto object-contain transition-transform group-hover:scale-105" src="https://lh3.googleusercontent.com/aida/AEtjO1Wl4YLNj-dOM-dn-JkZONeHH1n-vsK73LmNbVVAU1Qne88mayxeeXXwPkPDVp_n5lfG--n43ALT2cU0-iI_9iJ1DnzQHVQwy6iHkKJTetq5MBedhKUOCuXZPus2XFTnT2BeycGBz2wJhLn3q31Xaz9vTRqj23s8n8U1dwRwxnsAFUWQSHKFNJHCBF4DPjfY8nJ55K8ueYPlYQmw6JVENWwm9ceQ9Q518QKbniWS1lp7Zt8NlJOYw-mz_XE"/>
          <span class="font-serif text-2xl uppercase tracking-widest text-primary font-medium hidden min-[360px]:inline-block">VELORA</span>
        </a>
      </div>

      <!-- Action Icons -->
      <div class="flex items-center gap-1">
        <a href="shop.php" aria-label="Search Collection" class="w-11 h-11 flex items-center justify-center text-on-surface hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[22px]">search</span>
        </a>
        
        <!-- Wishlist -->
        <a href="wishlist.php" aria-label="Saved Wishlist" class="relative w-11 h-11 flex items-center justify-center text-on-surface hover:text-primary transition-colors" data-path="wishlist">
          <span class="material-symbols-outlined text-[22px]">favorite</span>
          <span class="wishlist-count-badge absolute top-2 right-2 flex items-center justify-center w-4 h-4 rounded-full bg-primary text-on-primary text-[10px] font-semibold leading-none" style="display: <?= $wishlistCount > 0 ? 'flex' : 'none' ?>;">
            <?= $wishlistCount ?>
          </span>
        </a>

        <!-- Shopping Bag -->
        <a href="cart.php" aria-label="Shopping Bag" class="relative w-11 h-11 flex items-center justify-center text-on-surface hover:text-primary transition-colors" data-path="bag">
          <span class="material-symbols-outlined text-[22px]">shopping_bag</span>
          <span class="cart-count-badge absolute top-2 right-2 flex items-center justify-center w-4 h-4 rounded-full bg-primary text-on-primary text-[10px] font-semibold leading-none" style="display: <?= $cartCount > 0 ? 'flex' : 'none' ?>;">
            <?= $cartCount ?>
          </span>
        </a>

        <!-- Account Profile -->
        <a href="index.php" aria-label="Atelier Member Account" class="w-11 h-11 flex items-center justify-center pl-1" data-path="account">
          <img alt="Atelier Member Profile" class="w-8 h-8 rounded-full object-cover ring-1 ring-primary/20" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBJk3k8ZhR53atfvbAeQcDTIUB-YAOk8VlEo1RQUlWenzw_Kf7H8TG3O7FSsvodtx9qeRrK7edw46Ggse6RHzzWzDGDeS_WilmJXZ22pGprhocrjxgQJzvutcQxGRH0zbRmugTZXJ7dXD2m1MuSAP0mFCtd_34W-Y9qxzS935wDmhPEw3XbWhGDhhjaPRF7vs8X98idhbzWohEqqDebMkBKsNFL4YWd_zaG-Ixl8MouCTSC7GbFhSvr"/>
        </a>
      </div>
    </div>

    <!-- Curated Category Sub-Bar -->
    <div class="h-10 max-w-velora px-4 sm:px-8 overflow-x-auto no-scrollbar flex items-center gap-6 bg-surface-container-low/80 border-t border-surface-container-high/40">
      <a class="font-label-caps text-on-surface hover:text-primary whitespace-nowrap transition-colors" href="shop.php">All Pieces</a>
      <a class="font-label-caps text-on-surface hover:text-primary whitespace-nowrap transition-colors" href="shop.php?category=silk-gowns">Silk Gowns</a>
      <a class="font-label-caps text-on-surface hover:text-primary whitespace-nowrap transition-colors" href="shop.php?category=kurtas">Ethnic Kurtas</a>
      <a class="font-label-caps text-on-surface hover:text-primary whitespace-nowrap transition-colors" href="shop.php?category=cocktail">Cocktail Wear</a>
      <a class="font-label-caps text-on-surface hover:text-primary whitespace-nowrap transition-colors" href="shop.php?category=handwoven">Handwoven</a>
      <a class="font-label-caps text-primary font-bold whitespace-nowrap transition-colors" href="shop.php?category=bridal-edit">Bridal Edit</a>
    </div>
  </header>
