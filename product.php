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

// ─── Handle Review Submission ─────────────────────────────────────────────────
$reviewSuccessMsg = null;
$reviewErrorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $authorName = trim($_POST['author_name'] ?? '');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');
    $customerImage = trim($_POST['customer_image'] ?? '');

    if (empty($authorName) || empty($comment)) {
        $reviewErrorMsg = 'Kindly share your name and your bespoke fitting impressions.';
    } else {
        // Generate patron initials
        $parts = preg_split('/\s+/', $authorName);
        $initials = '';
        foreach ($parts as $p) {
            if (!empty($p)) {
                $initials .= strtoupper(substr($p, 0, 1));
            }
            if (strlen($initials) >= 2) break;
        }
        if (empty($initials)) $initials = 'VP';

        $insertReview = $pdo->prepare("
            INSERT INTO reviews (product_id, author_name, author_initials, author_badge, rating, comment, customer_image, created_at)
            VALUES (?, ?, ?, 'Verified Connoisseur', ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $insertReview->execute([
            $productId,
            $authorName,
            $initials,
            $rating,
            $comment,
            !empty($customerImage) ? $customerImage : null
        ]);

        // Recalculate average rating and review count for this couture edition
        $stmtStats = $pdo->prepare("
            SELECT COUNT(*) as cnt, ROUND(AVG(rating), 1) as avg_rating 
            FROM reviews 
            WHERE product_id = ?
        ");
        $stmtStats->execute([$productId]);
        $stats = $stmtStats->fetch();

        if ($stats) {
            $upProd = $pdo->prepare("UPDATE products SET review_count = ?, rating = ? WHERE id = ?");
            $upProd->execute([$stats['cnt'], $stats['avg_rating'], $productId]);
            $product['review_count'] = (int)$stats['cnt'];
            $product['rating'] = (float)$stats['avg_rating'];
        }

        $reviewSuccessMsg = 'Thank you! Your testimonial has been archived in the Client Diary and shared with our atelier.';
    }
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

// Dynamic Star Distribution Breakdown from Database
$ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $r) {
    $sVal = max(1, min(5, (int)$r['rating']));
    $ratingCounts[$sVal]++;
}
$totalRevCount = count($reviews);
$ratingPercents = [];
for ($s = 1; $s <= 5; $s++) {
    $ratingPercents[$s] = $totalRevCount > 0 ? round(($ratingCounts[$s] / $totalRevCount) * 100) : 0;
}

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
          <img id="main-product-img" src="<?= htmlspecialchars(veloraImageUrl($images[0]['image_url'])) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"/>

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
            <button class="thumb-btn relative flex-shrink-0 w-16 h-20 rounded overflow-hidden bg-surface-container border-2 transition-all <?= $idx === 0 ? 'border-primary opacity-100 shadow-md' : 'border-transparent opacity-70 hover:opacity-100' ?>" onclick="switchHero(<?= $idx ?>, '<?= htmlspecialchars(veloraImageUrl($img['image_url'])) ?>')">
              <img class="w-full h-full object-cover" src="<?= htmlspecialchars(veloraImageUrl($img['image_url'])) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? $product['title']) ?>"/>
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
                <img class="w-full h-full object-cover" src="<?= htmlspecialchars(veloraImageUrl($acc['image_url'])) ?>" alt="<?= htmlspecialchars($acc['name']) ?>"/>
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
    <section id="reviewsSection" class="py-8 border-t border-surface-container-high/60 flex flex-col gap-6">

      <?php if ($reviewSuccessMsg): ?>
      <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3">
        <span class="material-symbols-outlined text-emerald-600 text-[20px]">verified</span>
        <span class="font-medium"><?= htmlspecialchars($reviewSuccessMsg) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($reviewErrorMsg): ?>
      <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
        <span class="material-symbols-outlined text-rose-600 text-[20px]">error</span>
        <span class="font-medium"><?= htmlspecialchars($reviewErrorMsg) ?></span>
      </div>
      <?php endif; ?>

      <div class="flex items-center justify-between">
        <div>
          <span class="font-label-caps text-secondary uppercase tracking-widest font-bold">Client Diary</span>
          <h3 class="font-serif text-2xl text-on-surface">Customer Reviews</h3>
        </div>
        <button type="button" onclick="openReviewModal()" class="px-5 py-2.5 rounded-full bg-primary hover:bg-[#3d0415] text-on-primary font-label-caps text-xs font-semibold transition-all shadow-md flex items-center gap-2 cursor-pointer">
          <span class="material-symbols-outlined text-[16px]">rate_review</span>
          <span>Write Review</span>
        </button>
      </div>

      <!-- Rating Summary Dashboard (Click any star or bar to filter reviews) -->
      <div class="p-5 rounded-xl bg-surface-container-low border border-surface-container-high/60 flex flex-col sm:flex-row items-center gap-6">
        <div class="text-center sm:min-w-[120px]">
          <span class="font-serif text-4xl text-primary font-bold block"><?= number_format((float)$product['rating'], 1) ?></span>
          <div class="flex text-[#D4AF37] justify-center my-1 gap-0.5">
            <?php for ($starI = 1; $starI <= 5; $starI++): ?>
            <button type="button" onclick="filterReviewsByStar(<?= $starI ?>)" title="Click to filter by <?= $starI ?> stars" class="hover:scale-125 transition-transform cursor-pointer p-0.5">
              <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' <?= $starI <= round((float)$product['rating']) ? 1 : 0 ?>;">star</span>
            </button>
            <?php endfor; ?>
          </div>
          <span class="font-label-caps text-[10px] text-outline block"><?= count($reviews) ?> Ratings</span>
          <button type="button" onclick="filterReviewsByStar(0)" id="resetStarFilterBtn" class="hidden mt-1.5 text-[10px] text-primary font-bold hover:underline cursor-pointer">
            Clear Filter (Show All)
          </button>
        </div>

        <!-- Dynamic Star Distribution Bars with Click-To-Filter -->
        <div class="flex-1 w-full space-y-1.5 font-label-caps text-[11px] text-on-surface-variant">
          <?php for ($s = 5; $s >= 1; $s--): 
            $pct = $ratingPercents[$s] ?? 0;
            $cnt = $ratingCounts[$s] ?? 0;
          ?>
          <button type="button" onclick="filterReviewsByStar(<?= $s ?>)" id="starRow_<?= $s ?>" title="Click to filter only <?= $s ?>-star reviews" class="star-filter-row w-full flex items-center gap-3 px-2.5 py-1 rounded-lg hover:bg-surface-container-high/70 transition-all cursor-pointer group text-left">
            <span class="w-6 font-bold group-hover:text-primary transition-colors"><?= $s ?>★</span>
            <div class="flex-1 h-2.5 bg-surface-container-highest rounded-full overflow-hidden">
              <div class="bg-primary h-full rounded-full transition-all duration-300" style="width: <?= $pct ?>%"></div>
            </div>
            <span class="w-14 text-right font-mono text-[10px] text-outline group-hover:text-primary font-semibold"><?= $pct ?>% (<?= $cnt ?>)</span>
          </button>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Active Star Filter Notification Pill -->
      <div id="activeFilterBanner" class="hidden items-center justify-between p-3.5 rounded-xl bg-secondary-container/40 border border-secondary/30 text-xs text-on-secondary-container">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-[18px] text-secondary">filter_alt</span>
          <span>Showing only <strong id="selectedStarLabel" class="text-primary font-bold">3-Star</strong> reviews (<span id="filteredMatchCount">0</span> reviews found)</span>
        </div>
        <button type="button" onclick="filterReviewsByStar(0)" class="text-xs font-bold text-primary hover:underline flex items-center gap-1 cursor-pointer">
          <span>Show All Reviews</span>
          <span class="material-symbols-outlined text-[14px]">close</span>
        </button>
      </div>

      <!-- No Matching Reviews Notice -->
      <div id="noReviewsNotice" class="hidden p-8 rounded-xl bg-surface-container-low text-center border border-surface-container-high/60 space-y-2">
        <span class="material-symbols-outlined text-secondary text-[32px]">star_half</span>
        <p class="font-serif text-base text-on-surface font-semibold">No reviews found for this star tier yet.</p>
        <p class="text-xs text-on-surface-variant">Be the first patron to submit your fitting impressions for this rating.</p>
        <button type="button" onclick="filterReviewsByStar(0)" class="mt-2 px-4 py-2 bg-primary hover:bg-[#3d0415] text-on-primary rounded-full text-xs font-semibold transition-colors cursor-pointer">
          Show All Reviews
        </button>
      </div>

      <!-- Verified Review Cards (Tagged with data-rating for instant filtering) -->
      <div class="space-y-4" id="reviewsCardsContainer">
        <?php foreach ($reviews as $rev): ?>
          <div class="review-card p-5 rounded-xl bg-surface-container-lowest border border-surface-container-high/60 shadow-sm space-y-2.5 transition-all" data-rating="<?= (int)$rev['rating'] ?>">
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
              <div class="flex items-center gap-3">
                <!-- Star Rating Display on Each Card -->
                <div class="flex text-[#D4AF37]">
                  <?php for ($sc = 1; $sc <= 5; $sc++): ?>
                  <span class="material-symbols-outlined text-[15px]" style="font-variation-settings: 'FILL' <?= $sc <= (int)$rev['rating'] ? 1 : 0 ?>;">star</span>
                  <?php endfor; ?>
                </div>
                <span class="font-label-caps text-[10px] text-outline">Verified Purchase</span>
              </div>
            </div>
            
            <p class="font-sans text-xs text-on-surface leading-relaxed">
              "<?= htmlspecialchars($rev['comment']) ?>"
            </p>

            <?php if (!empty($rev['customer_image'])): ?>
              <div class="w-16 h-16 rounded overflow-hidden mt-2 border border-outline-variant/40">
                <img class="w-full h-full object-cover" src="<?= htmlspecialchars(veloraImageUrl($rev['customer_image'])) ?>" alt="Review photo"/>
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

<!-- Write Review Modal -->
<div id="reviewModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface w-full max-w-lg rounded-2xl shadow-2xl border border-surface-container-high overflow-hidden flex flex-col max-h-[92vh]">
    <div class="p-5 bg-surface-container-low border-b border-surface-container-high flex items-center justify-between">
      <div>
        <span class="font-label-caps text-[10px] uppercase font-bold tracking-widest text-secondary block">Atelier Client Diary</span>
        <h3 class="font-serif text-xl text-primary font-medium">Write Connoisseur Review</h3>
      </div>
      <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-surface-container text-on-surface-variant cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form action="product.php?id=<?= $productId ?>#reviewsSection" method="POST" class="p-6 overflow-y-auto space-y-4 text-xs">
      <input type="hidden" name="action" value="submit_review"/>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Couture Edition</label>
        <div class="p-3 rounded-xl bg-surface-container-low border border-surface-container-high/60 flex items-center gap-3">
          <img src="<?= htmlspecialchars(veloraImageUrl($product['primary_image'])) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="w-10 h-12 object-cover rounded bg-surface-container"/>
          <div>
            <span class="font-medium text-xs text-on-surface block"><?= htmlspecialchars($product['title']) ?></span>
            <span class="text-[10px] text-secondary font-semibold uppercase">₹<?= number_format((float)$product['price']) ?></span>
          </div>
        </div>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1.5">Rating *</label>
        <div class="flex items-center gap-1.5" id="starRatingContainer">
          <input type="hidden" name="rating" id="reviewRatingInput" value="5"/>
          <?php for ($s = 1; $s <= 5; $s++): ?>
          <button type="button" onclick="setReviewRating(<?= $s ?>)" class="text-[#D4AF37] hover:scale-110 transition-transform cursor-pointer p-0.5">
            <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;" id="starIcon_<?= $s ?>">star</span>
          </button>
          <?php endfor; ?>
          <span class="text-xs font-semibold text-secondary ml-2" id="ratingScoreLabel">5.0 — Exceptional Couture</span>
        </div>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Your Name / Patron Title *</label>
        <input name="author_name" required placeholder="e.g. Devika Sharma / Tara Mehta" class="w-full bg-surface-container-low text-on-surface p-3 rounded-xl border border-outline-variant/60 outline-none focus:border-primary text-xs"/>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Your Fitting &amp; Silk Experience *</label>
        <textarea name="comment" rows="4" required placeholder="Describe the silk drape, tailoring finish, fit precision, and salon service..." class="w-full bg-surface-container-low text-on-surface p-3 rounded-xl border border-outline-variant/60 outline-none focus:border-primary text-xs resize-none"></textarea>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Patron Photo URL (Optional)</label>
        <input name="customer_image" type="text" placeholder="assets/img/customer-photo.jpg" class="w-full bg-surface-container-low text-on-surface p-3 rounded-xl border border-outline-variant/60 outline-none focus:border-primary text-xs"/>
      </div>

      <div class="pt-4 flex items-center justify-end gap-3 border-t border-surface-container-high/60">
        <button type="button" onclick="closeReviewModal()" class="px-5 py-2.5 rounded-xl bg-surface border border-outline-variant/60 text-xs font-semibold uppercase tracking-wider text-on-surface cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-[#3d0415] text-on-primary text-xs font-semibold uppercase tracking-wider shadow-md cursor-pointer transition-colors">
          Publish to Client Diary
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  let selectedColor = '<?= htmlspecialchars($colors[0]['color_name'] ?? 'Royal Wine Burgundy') ?>';
  let selectedSize = 'S';
  let currentQty = 1;

  function openReviewModal() {
    const m = document.getElementById('reviewModal');
    if (m) {
      m.classList.remove('hidden');
      m.classList.add('flex');
    }
  }

  function closeReviewModal() {
    const m = document.getElementById('reviewModal');
    if (m) {
      m.classList.remove('flex');
      m.classList.add('hidden');
    }
  }

  const ratingDescriptions = {
    1: '1.0 — Below Expectations',
    2: '2.0 — Needs Alterations',
    3: '3.0 — Satisfactory Silk',
    4: '4.0 — High Craftsmanship',
    5: '5.0 — Exceptional Couture'
  };

  function setReviewRating(stars) {
    document.getElementById('reviewRatingInput').value = stars;
    document.getElementById('ratingScoreLabel').textContent = ratingDescriptions[stars] || (stars + '.0');
    for (let i = 1; i <= 5; i++) {
      const star = document.getElementById('starIcon_' + i);
      if (star) {
        if (i <= stars) {
          star.style.fontVariationSettings = "'FILL' 1";
          star.classList.add('text-[#D4AF37]');
          star.classList.remove('text-outline-variant');
        } else {
          star.style.fontVariationSettings = "'FILL' 0";
          star.classList.remove('text-[#D4AF37]');
          star.classList.add('text-outline-variant');
        }
      }
    }
  }

  let activeStarFilter = 0;

  function filterReviewsByStar(star) {
    if (activeStarFilter === star && star !== 0) {
      // Toggle off to show all reviews if clicked again
      star = 0;
    }
    activeStarFilter = star;

    const cards = document.querySelectorAll('.review-card');
    const filterBanner = document.getElementById('activeFilterBanner');
    const noReviewsNotice = document.getElementById('noReviewsNotice');
    const resetBtn = document.getElementById('resetStarFilterBtn');
    const label = document.getElementById('selectedStarLabel');
    const matchCountSpan = document.getElementById('filteredMatchCount');

    // Remove active styles from star rows
    document.querySelectorAll('.star-filter-row').forEach(row => {
      row.classList.remove('bg-surface-container-high', 'ring-1', 'ring-primary');
    });

    if (star === 0) {
      // Show all reviews
      cards.forEach(card => card.style.display = '');
      if (filterBanner) {
        filterBanner.classList.add('hidden');
        filterBanner.classList.remove('flex');
      }
      if (noReviewsNotice) noReviewsNotice.classList.add('hidden');
      if (resetBtn) resetBtn.classList.add('hidden');
      triggerAddedToast('Showing all connoisseur reviews');
      return;
    }

    // Highlight selected filter row
    const selectedRow = document.getElementById('starRow_' + star);
    if (selectedRow) {
      selectedRow.classList.add('bg-surface-container-high', 'ring-1', 'ring-primary');
    }

    let matchCount = 0;
    cards.forEach(card => {
      const cardRating = parseInt(card.getAttribute('data-rating') || '0', 10);
      if (cardRating === star) {
        card.style.display = '';
        matchCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (filterBanner) {
      filterBanner.classList.remove('hidden');
      filterBanner.classList.add('flex');
    }
    if (label) label.textContent = star + '-Star';
    if (matchCountSpan) matchCountSpan.textContent = matchCount;
    if (resetBtn) resetBtn.classList.remove('hidden');

    if (matchCount === 0) {
      if (noReviewsNotice) noReviewsNotice.classList.remove('hidden');
    } else {
      if (noReviewsNotice) noReviewsNotice.classList.add('hidden');
    }

    // Smooth scroll down to reviews if filter is triggered
    const reviewsEl = document.getElementById('reviewsCardsContainer');
    if (reviewsEl) {
      reviewsEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    triggerAddedToast(`Filtered to ${star}-star reviews (${matchCount} found)`);
  }

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
