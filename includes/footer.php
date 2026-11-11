<?php
if (!defined('VELORA_ACCESS')) {
    define('VELORA_ACCESS', true);
}
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$cartSubtotal = getCartSubtotal();
?>
  <!-- Footer -->
  <footer class="mt-auto bg-surface-container-low pt-12 pb-24 md:pb-12 px-4 sm:px-8 border-t border-surface-container-high/60 text-on-surface-variant">
    <div class="max-w-velora flex flex-col gap-10">
      
      <!-- Newsletter Gazette -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center border-b border-surface-container-high/60 pb-10">
        <div class="flex flex-col gap-2">
          <span class="font-serif text-2xl text-on-surface italic">The VELORA Gazette</span>
          <p class="font-sans text-sm text-on-surface-variant max-w-md">
            Receive exclusive private previews, atelier salons, and limited couture editions directly to your inbox.
          </p>
        </div>
        <div class="flex w-full items-center gap-2">
          <input id="newsletter-email" class="flex-1 h-12 px-4 bg-surface text-on-surface text-sm border border-outline-variant/60 rounded-none focus:outline-none focus:ring-1 focus:ring-primary placeholder:text-outline" placeholder="Enter your email address" type="email"/>
          <button onclick="handleSubscribe()" class="h-12 px-6 bg-tertiary text-on-tertiary font-label-caps tracking-widest uppercase hover:bg-primary transition-colors">
            Join
          </button>
        </div>
      </div>

      <!-- Salon Locations & Concierge -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        <div class="flex flex-col gap-2">
          <span class="font-label-caps text-on-surface font-semibold tracking-wider">Flagship Salons</span>
          <p class="font-sans text-sm text-on-surface-variant leading-relaxed">
            R.S Puram<br/>
            Coimbatore
          </p>
        </div>
        <div class="flex flex-col gap-2">
          <span class="font-label-caps text-on-surface font-semibold tracking-wider">Concierge</span>
          <p class="font-sans text-sm text-on-surface-variant leading-relaxed">
            Mon – Sun: 10:00 AM – 8:00 PM IST<br/>
            shop@velora.in<br/>
            +91 (0) 22 4580 9100
          </p>
        </div>
        <div class="flex flex-col gap-2">
          <span class="font-label-caps text-on-surface font-semibold tracking-wider">Bespoke Guarantees</span>
          <div class="flex flex-wrap gap-1.5 text-xs">
            <span class="px-2.5 py-1 bg-surface border border-outline-variant/40 rounded-full">UPI & Instant Pay</span>
            <span class="px-2.5 py-1 bg-surface border border-outline-variant/40 rounded-full">Visa & Amex</span>
            <span class="px-2.5 py-1 bg-surface border border-outline-variant/40 rounded-full">Cash On Delivery</span>
            <span class="px-2.5 py-1 bg-surface border border-outline-variant/40 rounded-full">7-Day Atelier Exchange</span>
          </div>
        </div>
      </div>

      <!-- Legal & Stack Attribution -->
      <div class="pt-6 border-t border-surface-container-high/60 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
        <p class="font-label-caps text-[10px] tracking-widest text-outline uppercase">
          VELORA ATELIER PRIVATE LIMITED • <a href="admin/login.php" class="hover:text-primary transition-colors underline underline-offset-2">Atelier Executive Portal</a>
        </p>
        <p class="font-sans text-xs text-outline">
          © <?= date('Y') ?> VELORA. Wear Your Story. Handcrafted in India.
        </p>
      </div>

    </div>
  </footer>

  <!-- Bottom Mobile App Navigation Bar -->
  <nav class="fixed bottom-0 inset-x-0 z-50 md:hidden bg-surface/95 backdrop-blur-xl border-t border-surface-container-high/60 shadow-[0_-2px_12px_rgba(0,0,0,0.05)]">
    <div class="flex justify-around items-center h-16 px-2">
      <a class="flex flex-col items-center justify-center gap-1 min-w-[56px] h-14 text-on-surface-variant hover:text-primary transition-colors" data-path="home" href="index.php">
        <span class="material-symbols-outlined text-[22px]">home</span>
        <span class="font-label-caps text-[9px] uppercase tracking-wider">Home</span>
      </a>
      <a class="flex flex-col items-center justify-center gap-1 min-w-[56px] h-14 text-on-surface-variant hover:text-primary transition-colors" data-path="shop" href="shop.php">
        <span class="material-symbols-outlined text-[22px]">grid_view</span>
        <span class="font-label-caps text-[9px] uppercase tracking-wider">Shop</span>
      </a>
      <a class="flex flex-col items-center justify-center gap-1 min-w-[56px] h-14 text-on-surface-variant hover:text-primary transition-colors relative" data-path="wishlist" href="wishlist.php">
        <span class="material-symbols-outlined text-[22px]">favorite</span>
        <span class="wishlist-count-badge absolute top-1 right-2 flex items-center justify-center w-3.5 h-3.5 rounded-full bg-primary text-on-primary text-[8px] font-semibold" style="display: <?= $wishlistCount > 0 ? 'flex' : 'none' ?>;">
          <?= $wishlistCount ?>
        </span>
        <span class="font-label-caps text-[9px] uppercase tracking-wider">Wishlist</span>
      </a>
      <a class="flex flex-col items-center justify-center gap-1 min-w-[56px] h-14 text-on-surface-variant hover:text-primary transition-colors relative" data-path="bag" href="cart.php">
        <span class="material-symbols-outlined text-[22px]">local_mall</span>
        <span class="cart-count-badge absolute top-1 right-2 flex items-center justify-center w-3.5 h-3.5 rounded-full bg-primary text-on-primary text-[8px] font-semibold" style="display: <?= $cartCount > 0 ? 'flex' : 'none' ?>;">
          <?= $cartCount ?>
        </span>
        <span class="font-label-caps text-[9px] uppercase tracking-wider"><?= formatINR($cartSubtotal) ?></span>
      </a>
      <a class="flex flex-col items-center justify-center gap-1 min-w-[56px] h-14 text-on-surface-variant hover:text-primary transition-colors" data-path="account" href="admin/login.php">
        <span class="material-symbols-outlined text-[22px]">admin_panel_settings</span>
        <span class="font-label-caps text-[9px] uppercase tracking-wider">Atelier</span>
      </a>
    </div>
  </nav>

  <!-- Floating Cart Reminder Pill (Mobile + Desktop) -->
  <div id="floating-cart-pill" class="fixed bottom-20 md:bottom-8 left-1/2 -translate-x-1/2 z-40 justify-center pointer-events-none" style="display: <?= $cartCount > 0 ? 'flex' : 'none' ?>;">
    <a href="cart.php" class="pointer-events-auto bg-primary text-on-primary py-2.5 px-5 rounded-full shadow-2xl flex items-center gap-2 transform active:scale-95 transition-all hover:bg-primary-container border border-primary-fixed/20">
      <span class="material-symbols-outlined text-[18px] text-secondary-fixed">local_mall</span>
      <span id="floating-cart-text" class="font-label-caps text-[11px] tracking-wider uppercase">
        ✨ <?= $cartCount ?> item<?= $cartCount > 1 ? 's' : '' ?> in bag (<?= formatINR($cartSubtotal) ?>) • Free shipping unlocked!
      </span>
      <span class="material-symbols-outlined text-[16px]">chevron_right</span>
    </a>
  </div>

  <!-- Quick View Modal Drawer -->
  <div id="quickViewModal" class="fixed inset-0 z-50 velora-modal-backdrop hidden items-end sm:items-center sm:justify-center p-0 sm:p-4 transition-opacity">
    <div class="w-full sm:max-w-lg bg-surface max-h-[85vh] flex flex-col shadow-2xl overflow-hidden border border-outline-variant/40">
      <div class="p-3.5 bg-surface-container-low flex items-center justify-between border-b border-surface-container-high/60">
        <span class="font-label-caps text-[11px] uppercase tracking-wider text-primary font-bold">Atelier Quick View</span>
        <button onclick="closeQuickView()" class="w-7 h-7 flex items-center justify-center text-on-surface hover:text-primary">
          <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
      </div>
      
      <div class="p-5 overflow-y-auto flex flex-col gap-4">
        <div class="relative w-full aspect-[4/5] bg-surface-container overflow-hidden">
          <img id="qvImage" class="w-full h-full object-cover" src="" alt="Product Quick View"/>
        </div>
        <div class="flex flex-col gap-1">
          <div class="flex justify-between items-baseline gap-2">
            <h2 id="qvTitle" class="font-serif text-xl text-on-surface"></h2>
            <span id="qvPrice" class="font-price-display text-lg text-primary font-bold"></span>
          </div>
          <p id="qvDesc" class="font-sans text-xs text-on-surface-variant leading-relaxed"></p>
        </div>

        <!-- Size Swatches inside Quick View -->
        <div class="flex flex-col gap-1.5 pt-2">
          <span class="font-label-caps text-[10px] text-on-surface font-semibold uppercase">Select Size</span>
          <div class="flex gap-2">
            <button onclick="selectQvSize(this)" class="qv-size-btn px-3 py-1.5 border border-outline-variant text-xs active font-semibold bg-tertiary text-on-tertiary">S</button>
            <button onclick="selectQvSize(this)" class="qv-size-btn px-3 py-1.5 border border-outline-variant text-xs font-semibold bg-surface hover:bg-surface-container">M</button>
            <button onclick="selectQvSize(this)" class="qv-size-btn px-3 py-1.5 border border-outline-variant text-xs font-semibold bg-surface hover:bg-surface-container">L</button>
          </div>
        </div>
      </div>

      <div class="p-4 bg-surface-container-low flex items-center gap-3 border-t border-surface-container-high/60">
        <a id="qvFullDetailsLink" href="#" class="flex-1 py-3 bg-surface border border-outline-variant text-center font-label-caps text-[11px] uppercase tracking-wider hover:bg-surface-container transition-colors">
          Full Story
        </a>
        <button onclick="submitQuickViewAdd()" class="flex-1 py-3 bg-primary text-on-primary text-center font-label-caps text-[11px] uppercase tracking-wider hover:bg-primary-container transition-colors">
          Add To Bag
        </button>
      </div>
    </div>
  </div>

  <!-- JavaScript Modules -->
  <script src="assets/js/main.js"></script>
  <script>
    function selectQvSize(btn) {
      document.querySelectorAll('.qv-size-btn').forEach(b => {
        b.className = 'qv-size-btn px-3 py-1.5 border border-outline-variant text-xs font-semibold bg-surface hover:bg-surface-container';
      });
      btn.className = 'qv-size-btn px-3 py-1.5 border border-outline-variant text-xs font-semibold bg-tertiary text-on-tertiary';
    }

    function handleSubscribe() {
      const email = document.getElementById('newsletter-email').value;
      if (email && email.includes('@')) {
        triggerAddedToast('Welcome to the Atelier Circle! Code: VELORA500');
        document.getElementById('newsletter-email').value = '';
      } else {
        triggerAddedToast('Please enter a valid email address.', false);
      }
    }
  </script>
</body>
</html>
