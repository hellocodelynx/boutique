<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'VELORA — Wear Your Story | Luxury Indian Fashion Atelier';
$pdo = getDbConnection();

// Fetch curated categories
$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY id ASC LIMIT 5");
$categories = $stmtCat->fetchAll();

// Fetch monsoon featured collection (e.g. products 7 and 8 or featured)
$stmtMonsoon = $pdo->query("SELECT * FROM products WHERE id IN (7, 8) OR is_featured = 1 LIMIT 2");
$monsoonProducts = $stmtMonsoon->fetchAll();

// Fetch trending bestsellers
$stmtTrending = $pdo->query("SELECT * FROM products WHERE is_bestseller = 1 OR id IN (9, 10) LIMIT 2");
$trendingProducts = $stmtTrending->fetchAll();

$wishlistIds = getWishlistProductIds();

require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 flex flex-col relative w-full pt-[7.5rem] pb-safe bg-surface">
  <div class="max-w-velora w-full mx-auto px-4 sm:px-8 flex flex-col gap-8">
    
    <!-- Subtle Editorial Ambient Banner -->
    <div class="pt-3">
      <div class="bg-secondary-container/40 py-2 px-5 rounded-full flex items-center justify-between text-on-secondary-container border border-secondary/20">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-[16px] text-secondary">workspace_premium</span>
          <span class="font-label-caps tracking-wider text-[10px] sm:text-[11px]">Pan-India Express & Atelier Fit</span>
        </div>
        <span class="font-label-caps text-[10px] tracking-widest text-primary font-bold">COMPLIMENTARY</span>
      </div>
    </div>

    <!-- Editorial Hero Section: Asymmetric & Haute Couture -->
    <section class="relative">
      <div class="relative w-full aspect-[4/5] sm:aspect-[16/9] md:aspect-[21/9] rounded-xl overflow-hidden shadow-xl bg-surface-container">
        <div class="w-full h-full bg-cover bg-center transition-transform duration-700 hover:scale-105" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBPOYP4B2690p0XTcHYbinlyYqGKmkQqYV0pooWkmIPC2pOoxDkTI9wm8LmaHHm7ALoiTAGbpYmJkukv5HIOD0lwrL4Lp_foufW1qij4rgS40qwlcyQOiZgFUc48yPw41engK4X7D7vV0o76HUckWeDw3xEVjp41vHk2lfj-MQI9i05lidvgv8BHCBz57rPoxCi2yBSdXb6SY2Xvqp_1xWOyOjaHDi_kw3TtFeW00IjOr7toBcqtnZO');">
          <!-- Vignette Gradient Scrim -->
          <div class="absolute inset-0 bg-gradient-to-t from-primary/95 via-primary/30 to-transparent"></div>
          
          <div class="absolute inset-0 p-6 sm:p-12 flex flex-col justify-end text-on-primary">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface/20 backdrop-blur-md w-fit mb-3 border border-white/20">
              <span class="w-1.5 h-1.5 rounded-full bg-secondary-fixed"></span>
              <span class="font-label-caps text-[10px] uppercase tracking-widest text-secondary-fixed">Atelier Autumn/Winter ’25</span>
            </div>
            
            <h1 class="font-serif text-3xl sm:text-5xl md:text-6xl italic leading-tight text-on-primary max-w-xl">
              Find Your Kind of Beautiful
            </h1>
            
            <p class="font-sans text-xs sm:text-base text-surface-container-low mt-2 line-clamp-2 max-w-md opacity-90 leading-relaxed">
              Handcrafted silhouettes celebrating modern heritage & timeless, poetic drapes.
            </p>
            
            <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
              <a href="shop.php" class="h-12 px-8 bg-primary-container hover:bg-primary text-on-primary flex items-center justify-center gap-2 shadow-lg transition-transform active:scale-[0.99] font-label-caps tracking-widest uppercase font-semibold">
                <span>Shop The Festive Atelier</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
              </a>
              <a href="shop.php?collection=lookbook" class="h-10 px-4 flex items-center justify-center gap-1.5 text-on-primary/90 hover:text-on-primary font-label-caps text-[11px] uppercase tracking-widest underline underline-offset-4">
                <span>Explore Lookbook 2025</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Quick Story Category Scroller -->
    <section class="py-2">
      <div class="mb-4 flex items-center justify-between">
        <span class="font-label-caps text-on-surface-variant font-semibold tracking-wider">The Curated Edits</span>
        <span class="font-label-caps text-[10px] text-primary uppercase font-bold tracking-widest">5 Collections</span>
      </div>
      <div class="flex gap-4 sm:gap-8 overflow-x-auto no-scrollbar pb-2">
        <?php foreach ($categories as $cat): ?>
          <a href="shop.php?category=<?= urlencode($cat['slug']) ?>" class="flex flex-col items-center gap-2 shrink-0 group">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-0.5 bg-secondary-container shadow-sm group-hover:scale-105 transition-transform border border-secondary/30">
              <div class="w-full h-full rounded-full overflow-hidden bg-surface-container">
                <img class="w-full h-full object-cover" src="<?= htmlspecialchars($cat['image_url']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>"/>
              </div>
            </div>
            <span class="font-label-caps text-[10px] sm:text-[11px] uppercase tracking-wider text-on-surface text-center group-hover:text-primary transition-colors">
              <?= htmlspecialchars($cat['name']) ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Featured Collection: The Monsoon Velvet & Chiffon Drop -->
    <section class="flex flex-col gap-4">
      <div class="flex flex-col gap-1">
        <span class="font-label-caps text-[11px] uppercase tracking-widest text-primary font-bold">Limited Edition Drop</span>
        <div class="flex items-end justify-between">
          <h2 class="font-serif text-2xl text-on-surface">The Monsoon Velvet & Chiffon</h2>
          <a href="shop.php" class="font-label-caps text-[11px] uppercase tracking-wider text-secondary flex items-center hover:text-primary transition-colors">
            View All <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          </a>
        </div>
      </div>

      <!-- 2-Column Product Grid -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach ($monsoonProducts as $p): ?>
          <div class="velora-card rounded-xl overflow-hidden shadow-sm flex flex-col group relative">
            <div class="relative w-full aspect-[3/4] bg-surface-container overflow-hidden">
              <a href="product.php?id=<?= $p['id'] ?>">
                <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="<?= htmlspecialchars($p['primary_image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>"/>
              </a>
              <?php if (!empty($p['badge'])): ?>
                <span class="absolute top-2.5 left-2.5 bg-primary text-on-primary font-label-caps text-[9px] uppercase px-2 py-0.5 rounded-full tracking-wider shadow-sm">
                  <?= htmlspecialchars($p['badge']) ?>
                </span>
              <?php endif; ?>
              <button aria-label="Add to Wishlist" data-product-id="<?= $p['id'] ?>" class="wishlist-toggle-btn absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-surface/80 backdrop-blur-md flex items-center justify-center text-on-surface hover:text-primary transition-colors <?= in_array($p['id'], $wishlistIds) ? 'text-primary' : '' ?>">
                <span class="material-symbols-outlined text-[18px] <?= in_array($p['id'], $wishlistIds) ? 'fill-1' : '' ?>">favorite</span>
              </button>
            </div>
            
            <div class="p-3.5 flex flex-col justify-between flex-1 gap-2">
              <div>
                <span class="font-label-caps text-[9px] text-on-surface-variant uppercase tracking-widest">
                  <?= htmlspecialchars($p['fabric'] ?? 'Handcrafted') ?>
                </span>
                <h3 class="font-serif text-[15px] sm:text-base leading-snug text-on-surface line-clamp-1 mt-0.5">
                  <a href="product.php?id=<?= $p['id'] ?>" class="hover:text-primary transition-colors"><?= htmlspecialchars($p['title']) ?></a>
                </h3>
                <p class="font-price-display text-[16px] text-primary font-semibold mt-1">
                  <?= formatINR($p['price']) ?>
                </p>
              </div>
              <button onclick="quickAddToCart(<?= $p['id'] ?>)" class="w-full py-2 bg-surface-container text-on-surface hover:bg-primary hover:text-on-primary transition-colors font-label-caps text-[10px] uppercase tracking-widest flex items-center justify-center gap-1 border border-outline-variant/30">
                <span class="material-symbols-outlined text-[14px]">add</span> Quick Add
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Curated Editorial Story Banner: Atelier Manifesto -->
    <section>
      <div class="relative rounded-xl overflow-hidden bg-primary text-on-primary shadow-xl">
        <div class="flex flex-col md:flex-row">
          <div class="relative w-full md:w-1/2 aspect-square md:aspect-auto overflow-hidden">
            <img alt="VELORA Couture Muse" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBJk3k8ZhR53atfvbAeQcDTIUB-YAOk8VlEo1RQUlWenzw_Kf7H8TG3O7FSsvodtx9qeRrK7edw46Ggse6RHzzWzDGDeS_WilmJXZ22pGprhocrjxgQJzvutcQxGRH0zbRmugTZXJ7dXD2m1MuSAP0mFCtd_34W-Y9qxzS935wDmhPEw3XbWhGDhhjaPRF7vs8X98idhbzWohEqqDebMkBKsNFL4YWd_zaG-Ixl8MouCTSC7GbFhSvr"/>
            <div class="absolute inset-0 bg-gradient-to-t md:bg-gradient-to-r from-primary via-transparent to-transparent"></div>
          </div>
          
          <div class="p-6 md:p-12 flex flex-col justify-center gap-3 md:w-1/2">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-[20px] text-secondary-fixed">auto_awesome</span>
              <span class="font-label-caps uppercase tracking-widest text-secondary-fixed font-semibold">Atelier Manifesto</span>
            </div>
            
            <blockquote class="font-serif text-2xl sm:text-3xl italic leading-snug">
              “Every thread narrates an ancestry of elegance.”
            </blockquote>
            
            <p class="font-sans text-xs sm:text-sm text-surface-container-low opacity-90 leading-relaxed">
              Conceived between Delhi’s historic zardozi quarters and the fluid draping of contemporary Paris. Each silhouette is individually numbered and hand-finished by master craftswomen.
            </p>
            
            <a href="shop.php" class="mt-2 inline-flex items-center gap-2 text-secondary-fixed font-label-caps uppercase tracking-widest font-semibold hover:underline">
              Read Atelier Journal <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Trending Now Bestseller Cards -->
    <section class="flex flex-col gap-4">
      <div class="flex items-baseline justify-between">
        <div>
          <span class="font-label-caps text-[10px] uppercase tracking-widest text-primary font-bold">Client Favorites</span>
          <h2 class="font-serif text-2xl text-on-surface">Trending Now</h2>
        </div>
        <a href="shop.php?sort=bestselling" class="font-label-caps text-[10px] uppercase tracking-wider text-on-surface-variant hover:text-primary">
          View Bestsellers
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php foreach ($trendingProducts as $tp): ?>
          <div class="bg-surface-container-lowest rounded-xl p-3.5 shadow-sm flex gap-4 border border-surface-container-high/60">
            <div class="relative w-28 sm:w-36 aspect-[3/4] rounded-lg overflow-hidden shrink-0 bg-surface-container">
              <a href="product.php?id=<?= $tp['id'] ?>">
                <img class="w-full h-full object-cover" src="<?= htmlspecialchars($tp['primary_image']) ?>" alt="<?= htmlspecialchars($tp['title']) ?>"/>
              </a>
              <?php if (!empty($tp['badge'])): ?>
                <span class="absolute top-1.5 left-1.5 bg-primary text-on-primary font-label-caps text-[8px] uppercase px-1.5 py-0.5 rounded-full">
                  <?= htmlspecialchars($tp['badge']) ?>
                </span>
              <?php endif; ?>
            </div>
            
            <div class="flex flex-col justify-between flex-1 min-w-0 py-0.5">
              <div>
                <div class="flex items-center gap-1 text-secondary">
                  <span class="material-symbols-outlined text-[14px] fill-1">star</span>
                  <span class="font-label-caps text-[11px] font-bold"><?= $tp['rating'] ?></span>
                  <span class="font-sans text-[11px] text-on-surface-variant">(<?= $tp['review_count'] ?>)</span>
                </div>
                
                <h3 class="font-serif text-[16px] text-on-surface truncate mt-1">
                  <a href="product.php?id=<?= $tp['id'] ?>" class="hover:text-primary"><?= htmlspecialchars($tp['title']) ?></a>
                </h3>
                
                <div class="flex items-center gap-2 mt-1">
                  <span class="font-price-display text-[17px] text-primary font-bold"><?= formatINR($tp['price']) ?></span>
                  <?php if (!empty($tp['original_price'])): ?>
                    <span class="font-sans text-xs text-on-surface-variant line-through"><?= formatINR($tp['original_price']) ?></span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-surface-container-high/40">
                <div class="flex items-center gap-1.5">
                  <span class="w-4 h-4 rounded-full bg-[#541626] ring-1 ring-primary ring-offset-1" title="Wine"></span>
                  <span class="w-4 h-4 rounded-full bg-[#FAF7F2] ring-1 ring-outline" title="Ivory"></span>
                  <span class="w-4 h-4 rounded-full bg-[#7D8B7A]" title="Sage"></span>
                </div>
                
                <button onclick="quickAddToCart(<?= $tp['id'] ?>)" aria-label="Add to bag" class="px-3 py-1.5 rounded-full bg-surface-container text-on-surface hover:bg-primary hover:text-on-primary transition-colors font-label-caps text-[10px] tracking-wider uppercase flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px]">add</span> Add
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Brand Value Props: Tactile Luxury Assurances -->
    <section class="mt-4">
      <div class="bg-surface-container-low rounded-xl p-6 flex flex-col md:flex-row gap-6 border border-surface-container-high/60">
        <div class="flex items-start gap-3 flex-1">
          <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center shrink-0 text-on-secondary-container">
            <span class="material-symbols-outlined text-[20px]">eco</span>
          </div>
          <div>
            <h4 class="font-serif text-[16px] text-on-surface font-semibold">Pure Mulberry Silk & Cottons</h4>
            <p class="font-sans text-xs text-on-surface-variant mt-1 leading-relaxed">
              Ethically sourced unadulterated weaves, woven by multi-generational weaver collectives across Varanasi and Bengal.
            </p>
          </div>
        </div>

        <div class="flex items-start gap-3 flex-1">
          <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center shrink-0 text-on-secondary-container">
            <span class="material-symbols-outlined text-[20px]">straighten</span>
          </div>
          <div>
            <h4 class="font-serif text-[16px] text-on-surface font-semibold">Complimentary In-Home Alterations</h4>
            <p class="font-sans text-xs text-on-surface-variant mt-1 leading-relaxed">
              Our master tailor visits your doorstep in Delhi NCR, Mumbai & Bengaluru to craft your bespoke drape.
            </p>
          </div>
        </div>

        <div class="flex items-start gap-3 flex-1">
          <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center shrink-0 text-on-secondary-container">
            <span class="material-symbols-outlined text-[20px]">verified_user</span>
          </div>
          <div>
            <h4 class="font-serif text-[16px] text-on-surface font-semibold">Cash on Delivery & Secure UPI</h4>
            <p class="font-sans text-xs text-on-surface-variant mt-1 leading-relaxed">
              Seamless payments, instant bank refunds, and zero-friction 7-day atelier exchange policy.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Editorial Newsletter Callout with ₹500 Voucher -->
    <section>
      <div class="relative bg-tertiary text-on-tertiary rounded-xl p-8 flex flex-col items-center text-center overflow-hidden shadow-lg border border-tertiary-container">
        <span class="material-symbols-outlined text-[32px] text-secondary-fixed mb-1">mark_email_unread</span>
        <span class="font-label-caps text-label-caps uppercase tracking-widest text-secondary-fixed">The Atelier Circle</span>
        <h3 class="font-serif text-2xl sm:text-3xl mt-1 italic">Claim ₹500 On Your First Story</h3>
        <p class="font-sans text-xs sm:text-sm text-on-tertiary-container mt-1 max-w-sm leading-relaxed">
          Subscribe to early previews of festive salon drops and private archive sales.
        </p>
        <div class="w-full max-w-md flex items-center mt-5 bg-tertiary-container rounded-none p-1 border border-outline-variant/30">
          <input id="claim-email" class="flex-1 bg-transparent px-3 text-on-tertiary text-sm focus:outline-none placeholder:text-outline-variant" placeholder="Enter your email address" type="email"/>
          <button onclick="handleClaimVoucher()" class="px-5 py-2.5 bg-secondary-fixed text-on-secondary-fixed font-label-caps tracking-widest uppercase font-bold hover:bg-secondary hover:text-white transition-colors">
            Claim
          </button>
        </div>
        <span class="font-label-caps text-[9px] uppercase tracking-wider text-outline-variant mt-3">Use code: VELORA500 at checkout</span>
      </div>
    </section>

  </div>
</main>

<script>
  function handleClaimVoucher() {
    const email = document.getElementById('claim-email').value;
    if (email && email.includes('@')) {
      triggerAddedToast('Voucher VELORA500 unlocked for your order!');
      document.getElementById('claim-email').value = '';
    } else {
      triggerAddedToast('Please enter a valid email address', false);
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
