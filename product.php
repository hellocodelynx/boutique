<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/functions.php';

$pdo = getDbConnection();
$productId = (int)($_GET['id'] ?? 1);

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

// If product not found, fallback to first product
if (!$product) {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LIMIT 1");
    $product = $stmt->fetch();
    $productId = $product['id'];
}

// Fetch images
$stmtImg = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY display_order ASC");
$stmtImg->execute([$productId]);
$images = $stmtImg->fetchAll();
if (empty($images)) {
    $images = [
        ['image_url' => $product['primary_image'], 'alt_text' => $product['title'], 'is_video_preview' => 0]
    ];
}

// Fetch colors
$stmtCol = $pdo->prepare("SELECT * FROM product_colors WHERE product_id = ?");
$stmtCol->execute([$productId]);
$colors = $stmtCol->fetchAll();
if (empty($colors)) {
    $colors = [
        ['color_name' => 'Royal Wine Burgundy', 'hex_code' => '#702C3B', 'is_default' => 1]
    ];
}

// Fetch sizes
$stmtSizes = $pdo->prepare("SELECT * FROM product_sizes WHERE product_id = ?");
$stmtSizes->execute([$productId]);
$sizes = $stmtSizes->fetchAll();
if (empty($sizes)) {
    $sizes = [
        ['size_code' => 'XS', 'stock_count' => 4],
        ['size_code' => 'S', 'stock_count' => 2],
        ['size_code' => 'M', 'stock_count' => 6],
        ['size_code' => 'L', 'stock_count' => 3],
        ['size_code' => 'XL', 'stock_count' => 2]
    ];
}

// Fetch reviews
$stmtRev = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
$stmtRev->execute([$productId]);
$reviews = $stmtRev->fetchAll();

// Fetch accessories
$stmtAcc = $pdo->prepare("SELECT * FROM bundled_accessories WHERE parent_product_id = ?");
$stmtAcc->execute([$productId]);
$accessories = $stmtAcc->fetchAll();

$wishlistIds = getWishlistProductIds();
$pageTitle = htmlspecialchars($product['title']) . ' | VELORA Atelier';

require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 flex flex-col relative w-full pt-[7.5rem] pb-safe bg-surface">
  <div class="max-w-velora w-full mx-auto px-4 sm:px-8 flex flex-col gap-6">

    <!-- Breadcrumb Bar -->
    <nav aria-label="Breadcrumb" class="py-2 flex items-center space-x-2 overflow-x-auto no-scrollbar font-label-caps text-[11px] text-on-surface-variant uppercase tracking-wider">
      <a class="hover:text-primary transition-colors whitespace-nowrap" href="index.php">Home</a>
      <span class="text-outline text-xs">/</span>
      <a class="hover:text-primary transition-colors whitespace-nowrap" href="shop.php">Dresses</a>
      <span class="text-outline text-xs">/</span>
      <a class="hover:text-primary transition-colors whitespace-nowrap" href="shop.php?category=<?= urlencode($product['category_slug'] ?? '') ?>">
        <?= htmlspecialchars($product['category_name'] ?? 'Couture') ?>
      </a>
      <span class="text-outline text-xs">/</span>
      <span class="text-primary font-semibold whitespace-nowrap truncate max-w-[200px]">
        <?= htmlspecialchars($product['title']) ?>
      </span>
    </nav>

    <!-- Product Layout: Image Gallery (Left) & Details (Right) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-start">
      
      <!-- Interactive Gallery Module -->
      <section class="flex flex-col gap-3">
        <!-- Main Hero Presentation -->
        <div class="relative w-full aspect-[4/5] bg-surface-container overflow-hidden rounded-xl shadow-md group">
          <img id="main-product-img" src="<?= htmlspecialchars($images[0]['image_url']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"/>

          <!-- Limited Edition Silk Crest -->
          <div class="absolute top-4 left-4 bg-primary text-on-primary px-3 py-1 rounded shadow-sm font-label-caps text-[10px] tracking-widest uppercase">
            <?= htmlspecialchars($product['fabric'] ?? 'Mulberry Silk') ?>
          </div>

          <!-- Wishlist Tap on Hero -->
          <button data-product-id="<?= $product['id'] ?>" class="wishlist-toggle-btn absolute top-4 right-4 w-10 h-10 rounded-full bg-surface/90 backdrop-blur-md flex items-center justify-center text-on-surface shadow-sm hover:text-primary hover:scale-110 active:scale-90 transition-transform <?= in_array($product['id'], $wishlistIds) ? 'text-primary' : '' ?>">
            <span class="material-symbols-outlined text-[22px] <?= in_array($product['id'], $wishlistIds) ? 'fill-1' : '' ?>">favorite</span>
          </button>
        </div>

        <!-- Thumbnail Strip -->
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar py-1">
          <?php foreach ($images as $idx => $img): ?>
            <button class="thumb-btn relative flex-shrink-0 w-16 h-20 rounded overflow-hidden bg-surface-container border-2 transition-all <?= $idx === 0 ? 'border-primary opacity-100 shadow-md' : 'border-transparent opacity-70 hover:opacity-100' ?>" onclick="switchHero(<?= $idx ?>, '<?= htmlspecialchars($img['image_url']) ?>')">
              <img class="w-full h-full object-cover" src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? $product['title']) ?>"/>
              <?php if (!empty($img['is_video_preview'])): ?>
                <span class="material-symbols-outlined absolute inset-0 m-auto text-white text-[22px] drop-shadow-md flex items-center justify-center bg-black/30">
                  play_circle
                </span>
              <?php endif; ?>
            </button>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Editorial Product Specs & Action Form -->
      <section class="flex flex-col gap-5">
        
        <!-- Header & Ratings -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="font-label-caps text-secondary tracking-[0.2em] uppercase font-bold text-xs">
              VELORA Atelier Couture
            </span>
            <div class="flex items-center gap-1 bg-surface-container px-2.5 py-1 rounded-full">
              <div class="flex text-[#D4AF37]">
                <span class="material-symbols-outlined text-[14px] fill-1">star</span>
                <span class="material-symbols-outlined text-[14px] fill-1">star</span>
                <span class="material-symbols-outlined text-[14px] fill-1">star</span>
                <span class="material-symbols-outlined text-[14px] fill-1">star</span>
                <span class="material-symbols-outlined text-[14px] fill-1">star</span>
              </div>
              <span class="font-sans text-xs font-semibold text-on-surface"><?= $product['rating'] ?></span>
              <span class="font-sans text-[11px] text-on-surface-variant">(<?= $product['review_count'] ?>)</span>
            </div>
          </div>

          <h1 class="font-serif text-2xl sm:text-3xl text-on-surface leading-tight mt-1">
            <?= htmlspecialchars($product['title']) ?>
          </h1>

          <!-- Price Block -->
          <div class="flex items-baseline gap-3 mt-3">
            <span class="font-price-display text-2xl sm:text-3xl text-primary font-bold">
              <?= formatINR($product['price']) ?>
            </span>
            <?php if (!empty($product['original_price'])): ?>
              <span class="font-sans text-base text-outline line-through">
                <?= formatINR($product['original_price']) ?>
              </span>
              <?php if ($product['discount_percent'] > 0): ?>
                <span class="font-label-caps text-[10px] px-2 py-0.5 bg-secondary-container text-on-secondary-container rounded font-bold">
                  SAVE <?= formatINR($product['original_price'] - $product['price']) ?> (<?= $product['discount_percent'] ?>% OFF)
                </span>
              <?php endif; ?>
            <?php endif; ?>
          </div>

          <div class="flex items-center gap-2 text-on-surface-variant mt-2 text-xs">
            <span class="material-symbols-outlined text-[16px] text-secondary">local_shipping</span>
            <p>Inclusive of all taxes. Free 3-day express delivery across India.</p>
          </div>
        </div>

        <hr class="border-surface-container-high"/>

        <!-- Colorways Selector -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="font-label-caps text-on-surface font-semibold text-xs">
              Color: <span class="text-primary font-bold" id="color-label"><?= htmlspecialchars($colors[0]['color_name'] ?? 'Royal Wine Burgundy') ?></span>
            </span>
            <span class="font-label-caps text-[10px] text-outline">Artisanal Natural Dyes</span>
          </div>
          <div class="flex items-center gap-3 pt-1">
            <?php foreach ($colors as $cIdx => $col): ?>
              <button onclick="selectColorway('<?= htmlspecialchars($col['color_name']) ?>', this)" class="color-dot relative w-9 h-9 rounded-full shadow-md transition-transform border border-white <?= $cIdx === 0 ? 'scale-110 ring-2 ring-primary ring-offset-2' : 'hover:scale-105' ?>" style="background-color: <?= htmlspecialchars($col['hex_code']) ?>;" title="<?= htmlspecialchars($col['color_name']) ?>">
                <span class="material-symbols-outlined <?= $cIdx === 0 ? '' : 'hidden' ?> inset-0 m-auto text-white text-[16px] flex items-center justify-center">check</span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Size Selection Module -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="font-label-caps text-on-surface font-semibold text-xs">Select Size</span>
            <button onclick="toggleSizeGuideModal(true)" class="font-label-caps text-secondary underline hover:text-primary flex items-center gap-1 font-semibold text-xs">
              <span class="material-symbols-outlined text-[14px]">straighten</span> Size Guide (Bust, Waist, Hip)
            </button>
          </div>
          <div class="grid grid-cols-6 gap-2" id="size-selector-grid">
            <?php foreach ($sizes as $sIdx => $s): ?>
              <button onclick="selectSizeButton(this, '<?= htmlspecialchars($s['size_code']) ?>')" class="size-btn h-12 flex flex-col items-center justify-center rounded border transition-colors <?= $s['size_code'] === 'S' ? 'bg-tertiary text-on-tertiary border-tertiary shadow-md' : 'bg-surface-container hover:bg-surface-container-high border-outline-variant/40 text-on-surface' ?>">
                <span class="font-sans font-bold text-xs"><?= htmlspecialchars($s['size_code']) ?></span>
                <?php if ($s['stock_count'] <= 2): ?>
                  <span class="font-label-caps text-[8px] text-error font-bold leading-none"><?= $s['stock_count'] ?> left</span>
                <?php endif; ?>
              </button>
            <?php endforeach; ?>
          </div>
          <div class="flex items-center gap-2 p-2.5 rounded bg-error-container/40 text-on-error-container border border-error/20">
            <span class="material-symbols-outlined text-[16px] text-error">timer</span>
            <p class="font-sans text-xs font-semibold">Low Stock Alert: Only 2 handcrafted pieces left in Size S!</p>
          </div>
        </div>

        <!-- Quantity & Primary Action Strip -->
        <div class="flex items-center gap-3 pt-2">
          <!-- Step Counter -->
          <div class="flex items-center bg-surface-container-high rounded h-[52px] px-2 shadow-sm border border-outline-variant/40">
            <button onclick="changeProductQty(-1)" class="w-8 h-8 flex items-center justify-center text-on-surface hover:text-primary font-bold">
              <span class="material-symbols-outlined text-[18px]">remove</span>
            </button>
            <span id="product-quantity-val" class="w-8 text-center font-sans font-bold text-on-surface text-base">1</span>
            <button onclick="changeProductQty(1)" class="w-8 h-8 flex items-center justify-center text-on-surface hover:text-primary font-bold">
              <span class="material-symbols-outlined text-[18px]">add</span>
            </button>
          </div>

          <!-- Add To Bag -->
          <button onclick="handleAddCurrentProduct()" class="flex-1 h-[52px] bg-primary text-on-primary rounded flex items-center justify-center gap-2 shadow-lg hover:bg-primary-container active:scale-[0.98] transition-all font-label-caps uppercase tracking-wider font-bold text-xs sm:text-sm">
            <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
            <span>Add to Bag • <?= formatINR($product['price']) ?></span>
          </button>
        </div>

        <!-- Pincode Checker -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-high/60 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[20px]">pin_drop</span>
              <span class="font-sans text-xs font-bold text-on-surface">Delivery & Express Alteration</span>
            </div>
            <span class="font-label-caps text-[10px] text-secondary font-bold">VIP EXPRESS</span>
          </div>
          <div class="flex gap-2">
            <input class="flex-1 h-11 px-3 bg-surface border border-outline-variant/60 rounded text-on-surface text-sm focus:outline-none focus:ring-1 focus:ring-primary shadow-inner" id="pincode-input" placeholder="Enter Delivery Pincode" type="number" value="400001"/>
            <button onclick="verifyPincode()" class="px-5 h-11 bg-tertiary text-on-tertiary rounded font-label-caps tracking-wider text-xs font-bold hover:bg-primary transition-colors">
              Check
            </button>
          </div>
          <div class="flex items-start gap-2 pt-1 text-on-surface" id="pincode-feedback">
            <span class="material-symbols-outlined text-[18px] text-secondary flex-shrink-0 mt-0.5">verified</span>
            <div class="space-y-0.5">
              <p class="font-sans text-xs font-semibold text-on-surface">
                Delivers to <strong class="text-primary font-bold">400001 (Mumbai)</strong> by <span class="text-secondary font-bold">Friday, 24 Oct</span>
              </p>
              <p class="font-sans text-[11px] text-on-surface-variant">
                Complimentary Doorstep Alteration Service guarantee included.
              </p>
            </div>
          </div>
        </div>

        <!-- Accordions -->
        <div class="space-y-2 pt-2">
          <details class="group bg-surface-container-low rounded-lg border border-surface-container-high/60" open>
            <summary class="flex items-center justify-between p-4 cursor-pointer list-none">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">texture</span>
                <span class="font-serif text-base text-on-surface">Fabric & Craftsmanship</span>
              </div>
              <span class="material-symbols-outlined text-outline transition-transform duration-300 group-open:rotate-180">expand_more</span>
            </summary>
            <div class="px-4 pb-4 font-sans text-xs text-on-surface-variant leading-relaxed space-y-2">
              <p><?= htmlspecialchars($product['craftsmanship'] ?? 'Handcrafted by generational artisans with archival techniques.') ?></p>
            </div>
          </details>

          <details class="group bg-surface-container-low rounded-lg border border-surface-container-high/60">
            <summary class="flex items-center justify-between p-4 cursor-pointer list-none">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">accessibility_new</span>
                <span class="font-serif text-base text-on-surface">Bespoke Fit & Measurements</span>
              </div>
              <span class="material-symbols-outlined text-outline transition-transform duration-300 group-open:rotate-180">expand_more</span>
            </summary>
            <div class="px-4 pb-4 font-sans text-xs text-on-surface-variant leading-relaxed">
              <p><?= htmlspecialchars($product['model_info'] ?? 'Tailored bespoke contour with invisible closure.') ?></p>
            </div>
          </details>

          <details class="group bg-surface-container-low rounded-lg border border-surface-container-high/60">
            <summary class="flex items-center justify-between p-4 cursor-pointer list-none">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">sync</span>
                <span class="font-serif text-base text-on-surface">Complimentary 7-Day Returns & Exchanges</span>
              </div>
              <span class="material-symbols-outlined text-outline transition-transform duration-300 group-open:rotate-180">expand_more</span>
            </summary>
            <div class="px-4 pb-4 font-sans text-xs text-on-surface-variant leading-relaxed">
              <p>We provide hassle-free home reverse pick-up across 18,000+ PIN codes with immediate store credit or direct bank refund upon boutique inspection.</p>
            </div>
          </details>
        </div>

      </section>
    </div>

    <!-- Complete The Look / Bundled Styling Section -->
    <?php if (!empty($accessories)): ?>
      <section class="p-6 bg-surface-container-low rounded-xl border border-surface-container-high/60 mt-8">
        <div class="flex items-end justify-between mb-4">
          <div>
            <span class="font-label-caps text-secondary uppercase tracking-widest font-bold">Curated Ensemble</span>
            <h3 class="font-serif text-xl sm:text-2xl text-on-surface">Complete The Look</h3>
          </div>
          <button onclick="bundleAllAccessories()" class="font-label-caps text-primary underline font-bold text-xs">
            Bundle & Save 15%
          </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <?php foreach ($accessories as $acc): ?>
            <div class="bg-surface rounded-xl p-3 shadow-sm border border-outline-variant/40 flex flex-col justify-between">
              <div class="w-full aspect-square rounded-lg bg-surface-container overflow-hidden mb-2 relative">
                <img class="w-full h-full object-cover" src="<?= htmlspecialchars($acc['image_url']) ?>" alt="<?= htmlspecialchars($acc['name']) ?>"/>
                <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded bg-tertiary text-on-tertiary font-label-caps text-[8px]">
                  <?= htmlspecialchars($acc['tag']) ?>
                </span>
              </div>
              <div>
                <h4 class="font-serif text-xs sm:text-sm text-on-surface line-clamp-1"><?= htmlspecialchars($acc['name']) ?></h4>
                <span class="font-price-display text-sm text-primary font-bold block mt-1"><?= formatINR($acc['price']) ?></span>
              </div>
              <button onclick="triggerAddedToast('<?= htmlspecialchars($acc['name']) ?> added to bundle')" class="mt-2 w-full py-1.5 bg-surface-container hover:bg-primary hover:text-on-primary text-on-surface rounded font-label-caps text-[10px] font-semibold tracking-wider uppercase transition-colors">
                + Add Pair
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Customer Reviews Section -->
    <section class="py-8 border-t border-surface-container-high/60 flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div>
          <span class="font-label-caps text-secondary uppercase tracking-widest font-bold">Client Diary</span>
          <h3 class="font-serif text-2xl text-on-surface">Customer Reviews</h3>
        </div>
        <button onclick="triggerAddedToast('Review submission opened')" class="px-4 py-2 rounded-full bg-surface-container-high text-on-surface font-label-caps text-[11px] font-semibold hover:bg-surface-container-highest transition-colors">
          Write Review
        </button>
      </div>

      <!-- Rating Summary Dashboard -->
      <div class="p-5 rounded-xl bg-surface-container-low border border-surface-container-high/60 flex flex-col sm:flex-row items-center gap-6">
        <div class="text-center sm:min-w-[100px]">
          <span class="font-serif text-4xl text-primary font-bold block"><?= $product['rating'] ?></span>
          <div class="flex text-[#D4AF37] justify-center my-1">
            <span class="material-symbols-outlined text-[14px] fill-1">star</span>
            <span class="material-symbols-outlined text-[14px] fill-1">star</span>
            <span class="material-symbols-outlined text-[14px] fill-1">star</span>
            <span class="material-symbols-outlined text-[14px] fill-1">star</span>
            <span class="material-symbols-outlined text-[14px] fill-1">star</span>
          </div>
          <span class="font-label-caps text-[10px] text-outline"><?= $product['review_count'] ?> Ratings</span>
        </div>

        <div class="flex-1 w-full space-y-1.5 font-label-caps text-[10px] text-on-surface-variant">
          <div class="flex items-center gap-2">
            <span>5★</span>
            <div class="flex-1 h-2 bg-surface-container-highest rounded-full overflow-hidden">
              <div class="bg-primary h-full w-[88%] rounded-full"></div>
            </div>
            <span>88%</span>
          </div>
          <div class="flex items-center gap-2">
            <span>4★</span>
            <div class="flex-1 h-2 bg-surface-container-highest rounded-full overflow-hidden">
              <div class="bg-primary h-full w-[9%] rounded-full"></div>
            </div>
            <span>9%</span>
          </div>
          <div class="flex items-center gap-2">
            <span>3★</span>
            <div class="flex-1 h-2 bg-surface-container-highest rounded-full overflow-hidden">
              <div class="bg-primary h-full w-[2%] rounded-full"></div>
            </div>
            <span>2%</span>
          </div>
        </div>
      </div>

      <!-- Verified Review Cards -->
      <div class="space-y-4">
        <?php foreach ($reviews as $rev): ?>
          <div class="p-5 rounded-xl bg-surface-container-lowest border border-surface-container-high/60 shadow-sm space-y-2.5">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center font-label-caps text-xs font-bold">
                  <?= htmlspecialchars($rev['author_initials']) ?>
                </div>
                <div>
                  <p class="font-sans font-bold text-xs text-on-surface"><?= htmlspecialchars($rev['author_name']) ?></p>
                  <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-secondary text-[12px]">verified</span>
                    <span class="font-label-caps text-[9px] text-secondary"><?= htmlspecialchars($rev['author_badge']) ?></span>
                  </div>
                </div>
              </div>
              <span class="font-label-caps text-[10px] text-outline">Verified Purchase</span>
            </div>
            
            <p class="font-sans text-xs text-on-surface leading-relaxed">
              "<?= htmlspecialchars($rev['comment']) ?>"
            </p>

            <?php if (!empty($rev['customer_image'])): ?>
              <div class="w-16 h-16 rounded overflow-hidden mt-2 border border-outline-variant/40">
                <img class="w-full h-full object-cover" src="<?= htmlspecialchars($rev['customer_image']) ?>" alt="Review photo"/>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

  </div>
</main>

<!-- Size Guide Modal -->
<div id="sizeGuideModal" class="fixed inset-0 z-50 velora-modal-backdrop hidden items-center justify-center p-4">
  <div class="w-full max-w-md bg-surface p-6 shadow-2xl border border-outline-variant/60 flex flex-col gap-4">
    <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
      <h3 class="font-serif text-lg text-on-surface font-semibold">Atelier Size Matrix (Inches)</h3>
      <button onclick="toggleSizeGuideModal(false)" class="text-on-surface hover:text-primary">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <div class="text-xs">
      <table class="w-full border-collapse text-left">
        <thead>
          <tr class="bg-surface-container font-label-caps text-[10px]">
            <th class="p-2">Size</th>
            <th class="p-2">Bust</th>
            <th class="p-2">Waist</th>
            <th class="p-2">Hip</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-container-high">
          <tr><td class="p-2 font-bold">XS</td><td class="p-2">32"</td><td class="p-2">26"</td><td class="p-2">36"</td></tr>
          <tr><td class="p-2 font-bold">S</td><td class="p-2">34"</td><td class="p-2">28"</td><td class="p-2">38"</td></tr>
          <tr><td class="p-2 font-bold">M</td><td class="p-2">36"</td><td class="p-2">30"</td><td class="p-2">40"</td></tr>
          <tr><td class="p-2 font-bold">L</td><td class="p-2">38"</td><td class="p-2">32"</td><td class="p-2">42"</td></tr>
          <tr><td class="p-2 font-bold">XL</td><td class="p-2">40"</td><td class="p-2">34"</td><td class="p-2">44"</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  let selectedColor = '<?= htmlspecialchars($colors[0]['color_name'] ?? 'Royal Wine Burgundy') ?>';
  let selectedSize = 'S';
  let currentQty = 1;

  function switchHero(idx, imgUrl) {
    const mainImg = document.getElementById('main-product-img');
    document.querySelectorAll('.thumb-btn').forEach((b, i) => {
      if (i === idx) {
        b.classList.add('border-primary', 'opacity-100', 'shadow-md');
        b.classList.remove('border-transparent', 'opacity-70');
      } else {
        b.classList.remove('border-primary', 'opacity-100', 'shadow-md');
        b.classList.add('border-transparent', 'opacity-70');
      }
    });
    mainImg.style.opacity = '0.7';
    mainImg.src = imgUrl;
    setTimeout(() => { mainImg.style.opacity = '1'; }, 150);
  }

  function selectColorway(name, btn) {
    selectedColor = name;
    document.getElementById('color-label').innerText = name;
    document.querySelectorAll('.color-dot').forEach(d => {
      d.classList.remove('scale-110', 'ring-2', 'ring-primary', 'ring-offset-2');
      const check = d.querySelector('.material-symbols-outlined');
      if (check) check.classList.add('hidden');
    });
    btn.classList.add('scale-110', 'ring-2', 'ring-primary', 'ring-offset-2');
    const activeCheck = btn.querySelector('.material-symbols-outlined');
    if (activeCheck) activeCheck.classList.remove('hidden');
    triggerAddedToast(`Colorway switched to ${name}`);
  }

  function selectSizeButton(btn, size) {
    selectedSize = size;
    document.querySelectorAll('.size-btn').forEach(b => {
      b.className = 'size-btn h-12 flex flex-col items-center justify-center rounded border bg-surface-container hover:bg-surface-container-high border-outline-variant/40 text-on-surface';
    });
    btn.className = 'size-btn h-12 flex flex-col items-center justify-center rounded border bg-tertiary text-on-tertiary border-tertiary shadow-md';
    triggerAddedToast(`Size ${size} selected`);
  }

  function changeProductQty(delta) {
    currentQty = Math.max(1, Math.min(10, currentQty + delta));
    document.getElementById('product-quantity-val').innerText = currentQty;
  }

  function handleAddCurrentProduct() {
    quickAddToCart(<?= (int)$product['id'] ?>, selectedColor, selectedSize, currentQty);
  }

  function toggleSizeGuideModal(open) {
    const m = document.getElementById('sizeGuideModal');
    if (m) {
      if (open) m.classList.remove('hidden'), m.classList.add('flex');
      else m.classList.add('hidden'), m.classList.remove('flex');
    }
  }

  function bundleAllAccessories() {
    triggerAddedToast('Stylist accessories bundle added with 15% discount!');
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
