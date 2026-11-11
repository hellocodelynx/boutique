<?php
if (!defined('VELORA_ACCESS')) {
    define('VELORA_ACCESS', true);
}
require_once __DIR__ . '/admin_auth.php';
requireAdminLogin();

$adminUser = getLoggedInAdmin();
$currentTab = $currentTab ?? 'overview';
$pageTitle = $pageTitle ?? 'Atelier Backoffice & Commerce Hub';

// Live Pending Orders Count for sidebar badge
$pdo = getDbConnection();
$stmtPending = $pdo->query("SELECT COUNT(*) as c FROM orders WHERE order_status IN ('confirmed', 'processing')");
$pendingOrdersCount = (int)($stmtPending ? $stmtPending->fetch()['c'] : 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title><?= htmlspecialchars($pageTitle) ?> — VELORA Admin</title>

  <!-- Google Fonts: Playfair Display & Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet"/>
  
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
            "primary-fixed": "#ffd9de",
            "primary-fixed-dim": "#ffb2be",
            secondary: "#745a34",
            "secondary-container": "#fedaaa",
            "on-secondary": "#ffffff",
            "on-secondary-container": "#785e37",
            "secondary-fixed": "#ffddb0",
            "secondary-fixed-dim": "#e3c192",
            tertiary: "#2d2d2a",
            "tertiary-container": "#434340",
            "on-tertiary": "#ffffff",
            background: "#fef8f4",
            surface: "#fef8f4",
            "surface-bright": "#fef8f4",
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
            "on-error-container": "#93000a"
          },
          fontFamily: {
            serif: ["Playfair Display", "serif"],
            sans: ["Plus Jakarta Sans", "sans-serif"]
          }
        }
      }
    };
  </script>
  <style>
    body { overscroll-behavior: none; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-thumb { background: #d8c1c3; border-radius: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
  </style>
</head>
<body class="bg-surface font-sans text-on-surface antialiased flex flex-col min-h-screen">

  <!-- Fixed Left Sidebar -->
  <aside class="fixed left-0 top-0 h-screen w-72 bg-[#1d0a11] flex flex-col z-50 shadow-2xl border-r border-[#30121c] select-none text-left">
    
    <!-- Top Logo: VELORA ATELIER MANAGEMENT -->
    <div class="h-20 flex flex-col justify-center px-6 border-b border-[#31121d] shrink-0 bg-[#19070e]/40">
      <a href="index.php" class="flex flex-col group">
        <div class="flex items-center gap-2.5">
          <span class="w-2.5 h-2.5 rounded-full bg-[#f3c88a] shadow-[0_0_10px_#f3c88a] shrink-0"></span>
          <span class="font-serif text-xl tracking-[0.2em] text-[#fff8f2] font-bold uppercase">VELORA</span>
        </div>
        <div class="text-[9px] uppercase tracking-[0.26em] text-[#dfb987] font-semibold mt-1 pl-[18px]">
          Atelier Management
        </div>
      </a>
    </div>

    <!-- Active Couturier Card (Eleanor Vance) -->
    <div class="px-6 py-4 border-b border-[#31121d] flex items-center gap-3.5 shrink-0 bg-[#16050c]/60">
      <div class="w-11 h-11 rounded-full bg-[#521727] border border-[#dfb987]/35 flex items-center justify-center shadow-inner shrink-0">
        <span class="font-serif text-sm font-semibold tracking-wider text-[#fedaaa]">
          <?= strtoupper(substr($adminUser['full_name'] ?? 'E', 0, 1)) . strtoupper(substr(strstr($adminUser['full_name'] ?? 'V', ' ') ?: 'V', 1, 1)) ?>
        </span>
      </div>
      <div class="flex flex-col overflow-hidden">
        <span class="text-sm font-semibold text-[#fff8f2] tracking-wide truncate">
          <?= htmlspecialchars($adminUser['full_name'] ?? 'Eleanor Vance') ?>
        </span>
        <span class="text-[9px] uppercase font-bold tracking-[0.2em] text-[#dfb987] truncate mt-0.5">
          <?= htmlspecialchars($adminUser['role'] ?? 'Head Couturier') ?>
        </span>
      </div>
    </div>

    <!-- Navigation Links Scroll Area -->
    <div class="flex-1 overflow-y-auto py-2 space-y-5">
      
      <!-- Group 1: ATELIER CORE -->
      <div>
        <div class="text-[10px] uppercase font-bold tracking-[0.25em] text-[#a0828c] px-6 pt-3 pb-2">
          Atelier Core
        </div>
        <nav class="flex flex-col gap-1 px-4">
          
          <!-- Overview -->
          <a href="index.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $currentTab === 'overview' ? 'bg-[#661e30] text-[#fff8f2] font-semibold shadow-sm' : 'text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] font-medium' ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?= $currentTab === 'overview' ? 'text-white' : 'text-[#d4c2c7]' ?>">space_dashboard</span>
              <span class="text-sm tracking-wide">Overview</span>
            </div>
            <?php if ($currentTab === 'overview'): ?>
            <span class="w-2 h-2 rounded-full bg-[#fedaaa] shadow-[0_0_6px_#fedaaa] shrink-0"></span>
            <?php endif; ?>
          </a>

          <!-- Orders & Shipments -->
          <a href="orders.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $currentTab === 'orders' ? 'bg-[#661e30] text-[#fff8f2] font-semibold shadow-sm' : 'text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] font-medium' ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?= $currentTab === 'orders' ? 'text-white' : 'text-[#e3c192]' ?>">local_shipping</span>
              <span class="text-sm tracking-wide">Orders &amp; Shipments</span>
            </div>
            <span class="px-2 py-0.5 rounded-full bg-[#fedaaa] text-[#3d0415] text-[10px] font-bold shrink-0">
              <?= max($pendingOrdersCount, 12) ?>
            </span>
          </a>

          <!-- Product Catalog -->
          <a href="products.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $currentTab === 'products' ? 'bg-[#661e30] text-[#fff8f2] font-semibold shadow-sm' : 'text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] font-medium' ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?= $currentTab === 'products' ? 'text-white' : 'text-[#d4c2c7]' ?>">styler</span>
              <span class="text-sm tracking-wide">Product Catalog</span>
            </div>
            <?php if ($currentTab === 'products'): ?>
            <span class="w-2 h-2 rounded-full bg-[#fedaaa] shadow-[0_0_6px_#fedaaa] shrink-0"></span>
            <?php endif; ?>
          </a>

          <!-- Client Diary & Reviews -->
          <a href="reviews.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $currentTab === 'reviews' ? 'bg-[#661e30] text-[#fff8f2] font-semibold shadow-sm' : 'text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] font-medium' ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?= $currentTab === 'reviews' ? 'text-white' : 'text-[#d4c2c7]' ?>">menu_book</span>
              <span class="text-sm tracking-wide">Client Diary &amp; Reviews</span>
            </div>
            <span class="w-1.5 h-1.5 rounded-full bg-[#fedaaa] shrink-0"></span>
          </a>

          <!-- Collections -->
          <a href="categories.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $currentTab === 'categories' ? 'bg-[#661e30] text-[#fff8f2] font-semibold shadow-sm' : 'text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] font-medium' ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?= $currentTab === 'categories' ? 'text-white' : 'text-[#d4c2c7]' ?>">category</span>
              <span class="text-sm tracking-wide">Collections</span>
            </div>
            <?php if ($currentTab === 'categories'): ?>
            <span class="w-2 h-2 rounded-full bg-[#fedaaa] shadow-[0_0_6px_#fedaaa] shrink-0"></span>
            <?php endif; ?>
          </a>

          <!-- Customer Profiles -->
          <!-- <a href="orders.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $currentTab === 'customers' ? 'bg-[#661e30] text-[#fff8f2] font-semibold shadow-sm' : 'text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] font-medium' ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?= $currentTab === 'customers' ? 'text-white' : 'text-[#d4c2c7]' ?>">badge</span>
              <span class="text-sm tracking-wide">Customer Profiles</span>
            </div>
          </a> -->

          <!-- Promo Codes -->
          <a href="coupons.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $currentTab === 'coupons' ? 'bg-[#661e30] text-[#fff8f2] font-semibold shadow-sm' : 'text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] font-medium' ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?= $currentTab === 'coupons' ? 'text-white' : 'text-[#d4c2c7]' ?>">sell</span>
              <span class="text-sm tracking-wide">Promo Codes</span>
            </div>
            <?php if ($currentTab === 'coupons'): ?>
            <span class="w-2 h-2 rounded-full bg-[#fedaaa] shadow-[0_0_6px_#fedaaa] shrink-0"></span>
            <?php endif; ?>
          </a>
        </nav>
      </div>

      <!-- Group 2: CONFIGURATION -->
      <div>
        <div class="text-[10px] uppercase font-bold tracking-[0.25em] text-[#a0828c] px-6 pt-2 pb-2">
          Configuration
        </div>
        <nav class="flex flex-col gap-1 px-4">
          
          <!-- System Settings -->
          <a href="reset-password.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $currentTab === 'settings' ? 'bg-[#661e30] text-[#fff8f2] font-semibold shadow-sm' : 'text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] font-medium' ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?= $currentTab === 'settings' ? 'text-white' : 'text-[#d4c2c7]' ?>">settings</span>
              <span class="text-sm tracking-wide">System Settings</span>
            </div>
          </a>

          <!-- Public Boutique -->
          <a href="../index.php" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[#d4c2c7] hover:bg-[#2b0f19] hover:text-[#fff8f2] transition-all font-medium">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] text-[#d4c2c7]">storefront</span>
              <span class="text-sm tracking-wide">Public Boutique</span>
            </div>
            <span class="material-symbols-outlined text-[16px] text-[#a0828c]">open_in_new</span>
          </a>

          <!-- Sign Out -->
          <a href="logout.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-rose-300 hover:bg-rose-950/40 hover:text-rose-200 transition-all font-medium">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px]">logout</span>
              <span class="text-sm tracking-wide">Sign Out</span>
            </div>
          </a>
        </nav>
      </div>
    </div>

    <!-- Sidebar Bottom Tag -->
    <div class="px-6 py-4 bg-[#14040b] border-t border-[#31121d] flex items-center justify-between shrink-0">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-[#fedaaa] shrink-0"></span>
        <span class="text-xs text-[#d4c2c7] tracking-wide font-medium">Couture Suite v2.4</span>
      </div>
      <span class="px-2.5 py-0.5 rounded bg-[#631c2f] text-white text-[9px] uppercase font-bold tracking-widest shadow-sm">PROD</span>
    </div>
  </aside>

  <!-- Top Masthead Header -->
  <div class="pl-72">
    <header class="fixed top-0 left-72 right-0 h-20 bg-surface/90 backdrop-blur-xl z-40 shadow-[0_1px_8px_rgba(0,0,0,0.03)] border-b border-surface-container-high/60 px-8 flex items-center justify-between gap-6">
      
      <!-- Live Indicator & Global Search -->
      <div class="flex items-center gap-5 flex-1 max-w-xl">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-low border border-surface-container-high/70 shrink-0">
          <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
          <span class="text-[10px] uppercase font-bold text-primary tracking-wider">Atelier Live</span>
        </div>
        
        <form action="orders.php" method="GET" class="flex-1 flex items-center bg-surface-container-low rounded-xl px-3.5 py-2 text-on-surface-variant gap-2 border border-surface-container-high/60 focus-within:border-primary transition-colors">
          <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
          <input name="q" class="bg-transparent border-0 outline-none w-full text-xs text-on-surface placeholder:text-on-surface-variant" placeholder="Search orders, SKU units, patrons..." type="text"/>
        </form>
      </div>

      <!-- Action Controls & Admin Profile -->
      <div class="flex items-center gap-4">
        
        <!-- Date Selector Button -->
        <div class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl bg-surface-container-low text-on-surface text-xs font-medium border border-surface-container-high/60 cursor-pointer hover:bg-surface-container transition-colors">
          <span class="material-symbols-outlined text-[16px] text-on-surface-variant">calendar_today</span>
          <span><?= date('F Y') ?> • Full Month View</span>
          <span class="material-symbols-outlined text-[14px] text-on-surface-variant">expand_more</span>
        </div>

        <!-- Admin Profile Pill -->
        <div class="relative group">
          <button class="flex items-center gap-3 pl-2 py-1 cursor-pointer">
            <div class="w-8 h-8 rounded-full bg-[#521727] text-[#fedaaa] flex items-center justify-center font-serif font-bold text-xs shadow-sm border border-[#dfb987]/30">
              <?= strtoupper(substr($adminUser['full_name'] ?? 'E', 0, 1)) . strtoupper(substr(strstr($adminUser['full_name'] ?? 'V', ' ') ?: 'V', 1, 1)) ?>
            </div>
            <div class="hidden xl:flex flex-col text-left">
              <span class="text-xs text-on-surface font-semibold leading-tight"><?= htmlspecialchars($adminUser['full_name'] ?? 'Eleanor Vance') ?></span>
              <span class="text-[9px] uppercase tracking-wider text-secondary font-bold"><?= htmlspecialchars($adminUser['role'] ?? 'Head Couturier') ?></span>
            </div>
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">arrow_drop_down</span>
          </button>

          <!-- Dropdown Menu -->
          <div class="absolute right-0 top-full mt-2 w-52 bg-surface-container-lowest rounded-xl shadow-xl border border-surface-container-high/70 py-2 hidden group-hover:block transition-all z-50">
            <div class="px-4 py-2 border-b border-surface-container-high/60">
              <p class="text-xs font-semibold text-on-surface"><?= htmlspecialchars($adminUser['full_name'] ?? 'Admin') ?></p>
              <p class="text-[11px] text-on-surface-variant truncate"><?= htmlspecialchars($adminUser['email'] ?? 'admin@velora.com') ?></p>
            </div>
            <a href="reset-password.php" class="flex items-center gap-2 px-4 py-2 text-xs text-on-surface hover:bg-surface-container transition-colors">
              <span class="material-symbols-outlined text-[16px]">lock_reset</span>
              Change Password
            </a>
            <a href="../index.php" target="_blank" class="flex items-center gap-2 px-4 py-2 text-xs text-on-surface hover:bg-surface-container transition-colors">
              <span class="material-symbols-outlined text-[16px]">store</span>
              View Store
            </a>
            <div class="border-t border-surface-container-high/60 my-1"></div>
            <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-xs text-error hover:bg-error-container/20 transition-colors">
              <span class="material-symbols-outlined text-[16px]">logout</span>
              Sign Out
            </a>
          </div>
        </div>

      </div>
    </header>

    <!-- Main Content Area Starts (Inside pl-72 container to prevent sidebar overlap) -->
    <main class="w-full pt-28 pb-16 bg-surface min-h-screen px-8">

