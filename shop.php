<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/functions.php';

$pdo = getDbConnection();

// Read query filters
$selectedCategory = sanitize($_GET['category'] ?? '');
$selectedSilhouette = sanitize($_GET['silhouette'] ?? '');
$selectedFabric = sanitize($_GET['fabric'] ?? '');
$selectedOccasion = sanitize($_GET['occasion'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'featured');
$maxPrice = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : 25000;
$search = sanitize($_GET['search'] ?? '');

$where = [];
$params = [];

if ($selectedCategory) {
    $where[] = "c.slug = ?";
    $params[] = $selectedCategory;
}
if ($selectedSilhouette) {
    $where[] = "p.silhouette = ?";
    $params[] = $selectedSilhouette;
}
if ($selectedFabric) {
    $where[] = "p.fabric = ?";
    $params[] = $selectedFabric;
}
if ($selectedOccasion) {
    $where[] = "p.occasion = ?";
    $params[] = $selectedOccasion;
}
if ($maxPrice > 0 && $maxPrice < 25000) {
    $where[] = "p.price <= ?";
    $params[] = $maxPrice;
}
if ($search) {
    $where[] = "(p.title LIKE ? OR p.subtitle LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Order clause
$orderClause = "ORDER BY p.is_featured DESC, p.id ASC";
if ($sort === 'newest') $orderClause = "ORDER BY p.is_new_arrival DESC, p.created_at DESC";
elseif ($sort === 'price-asc') $orderClause = "ORDER BY p.price ASC";
elseif ($sort === 'price-desc') $orderClause = "ORDER BY p.price DESC";
elseif ($sort === 'bestselling') $orderClause = "ORDER BY p.is_bestseller DESC, p.review_count DESC";

// Fetch products
$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $whereClause
    $orderClause
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$wishlistIds = getWishlistProductIds();
$pageTitle = 'VELORA — Shop Catalogue | Handcrafted Dresses & Gowns';

require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 flex flex-col relative w-full pt-[7.5rem] pb-safe bg-surface">
  <div class="max-w-velora w-full mx-auto px-4 sm:px-8 flex flex-col gap-4">

    <!-- Editorial Breadcrumb & Category Title -->
    <section class="pt-4 pb-2 flex flex-col gap-2">
      <div class="flex items-center gap-2 text-on-surface-variant font-label-caps text-[11px] uppercase tracking-wider">
        <a class="hover:text-primary transition-colors" href="index.php">Home</a>
        <span>/</span>
        <span class="text-primary font-semibold">
          <?= $selectedCategory ? htmlspecialchars(ucwords(str_replace('-', ' ', $selectedCategory))) : 'Dresses & Gowns' ?>
        </span>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-1">
        <div>
          <h1 class="font-serif text-3xl sm:text-4xl text-on-surface tracking-tight">
            <?= $selectedCategory ? htmlspecialchars(ucwords(str_replace('-', ' ', $selectedCategory))) : 'Dresses & Couture' ?>
          </h1>
          <p class="font-sans text-xs sm:text-sm text-on-surface-variant mt-1">
            <?= count($products) ?> curated couture editions available
          </p>
        </div>

        <!-- Quick Sort Trigger -->
        <div class="relative w-fit">
          <form method="GET" id="sortForm" class="flex items-center">
            <?php if ($selectedCategory): ?><input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>"/><?php endif; ?>
            <?php if ($selectedSilhouette): ?><input type="hidden" name="silhouette" value="<?= htmlspecialchars($selectedSilhouette) ?>"/><?php endif; ?>
            <?php if ($selectedFabric): ?><input type="hidden" name="fabric" value="<?= htmlspecialchars($selectedFabric) ?>"/><?php endif; ?>
            <?php if ($selectedOccasion): ?><input type="hidden" name="occasion" value="<?= htmlspecialchars($selectedOccasion) ?>"><?php endif; ?>
            <select onchange="this.form.submit()" name="sort" aria-label="Sort collection" class="appearance-none bg-surface-container-low pl-4 pr-10 py-2.5 font-label-caps text-[11px] uppercase tracking-wider text-on-surface focus:outline-none border border-outline-variant/60 rounded-none shadow-sm cursor-pointer">
              <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured</option>
              <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
              <option value="price-asc" <?= $sort === 'price-asc' ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price-desc" <?= $sort === 'price-desc' ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="bestselling" <?= $sort === 'bestselling' ? 'selected' : '' ?>>Best Selling</option>
            </select>
            <span class="material-symbols-outlined pointer-events-none absolute right-2.5 text-[18px] text-on-surface-variant">expand_more</span>
          </form>
        </div>
      </div>
    </section>

    <!-- Filter Trigger Bar & Active Pills -->
    <section class="py-3 px-4 bg-surface-container-lowest shadow-sm border border-surface-container-high/60 flex flex-col gap-2.5 sticky top-[7.5rem] z-30">
      <div class="flex items-center justify-between">
        <button onclick="toggleFilterDrawer(true)" class="flex items-center gap-2 bg-primary text-on-primary px-4 py-2 rounded-full font-label-caps text-[11px] uppercase tracking-widest hover:bg-primary-container transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[16px]">tune</span>
          <span>Filters</span>
        </button>
        <span class="font-label-caps text-[11px] text-on-surface-variant tracking-wider uppercase hidden sm:inline-block">
          Handcrafted In India
        </span>
      </div>

      <!-- Active Filter Tags -->
      <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
        <?php if ($selectedCategory): ?>
          <a href="shop.php?<?= http_build_query(array_merge($_GET, ['category' => ''])) ?>" class="flex items-center gap-1.5 bg-surface-container text-on-surface px-3 py-1 rounded-full whitespace-nowrap font-label-caps text-[10px] uppercase tracking-wider shadow-sm border border-outline-variant/40 hover:text-primary">
            <span>Category: <?= htmlspecialchars($selectedCategory) ?></span>
            <span class="font-bold">×</span>
          </a>
        <?php endif; ?>
        <?php if ($selectedSilhouette): ?>
          <a href="shop.php?<?= http_build_query(array_merge($_GET, ['silhouette' => ''])) ?>" class="flex items-center gap-1.5 bg-surface-container text-on-surface px-3 py-1 rounded-full whitespace-nowrap font-label-caps text-[10px] uppercase tracking-wider shadow-sm border border-outline-variant/40 hover:text-primary">
            <span>Silhouette: <?= htmlspecialchars($selectedSilhouette) ?></span>
            <span class="font-bold">×</span>
          </a>
        <?php endif; ?>
        <?php if ($selectedFabric): ?>
          <a href="shop.php?<?= http_build_query(array_merge($_GET, ['fabric' => ''])) ?>" class="flex items-center gap-1.5 bg-surface-container text-on-surface px-3 py-1 rounded-full whitespace-nowrap font-label-caps text-[10px] uppercase tracking-wider shadow-sm border border-outline-variant/40 hover:text-primary">
            <span>Fabric: <?= htmlspecialchars($selectedFabric) ?></span>
            <span class="font-bold">×</span>
          </a>
        <?php endif; ?>
        <?php if ($selectedCategory || $selectedSilhouette || $selectedFabric || $selectedOccasion): ?>
          <a href="shop.php" class="text-primary hover:underline whitespace-nowrap font-label-caps text-[10px] uppercase tracking-wider font-semibold pl-1">
            Clear All
          </a>
        <?php else: ?>
          <span class="font-sans text-xs text-on-surface-variant">Showing all available handcrafted pieces</span>
        <?php endif; ?>
      </div>
    </section>

    <!-- Luxury Product Catalog Grid -->
    <section class="py-4">
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <?php if (empty($products)): ?>
          <div class="col-span-full py-16 text-center bg-surface-container-low rounded-xl">
            <span class="material-symbols-outlined text-4xl text-outline mb-2">style</span>
            <h3 class="font-serif text-xl text-on-surface">No pieces matched your selected filter</h3>
            <p class="font-sans text-xs text-on-surface-variant mt-1">Try resetting the filters to view our full collection.</p>
            <a href="shop.php" class="mt-4 inline-block px-5 py-2.5 bg-primary text-on-primary font-label-caps text-xs tracking-wider uppercase">
              View All Pieces
            </a>
          </div>
        <?php endif; ?>

        <?php foreach ($products as $product): ?>
          <article class="velora-card flex flex-col bg-surface-container-lowest shadow-sm rounded-none overflow-hidden group border border-surface-container-high/60">
            <div class="relative w-full aspect-[3/4] bg-surface-container overflow-hidden">
              <a href="product.php?id=<?= $product['id'] ?>" class="block w-full h-full">
                <img class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105" src="<?= htmlspecialchars($product['primary_image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>"/>
              </a>
              
              <?php if (!empty($product['badge'])): ?>
                <span class="absolute top-2 left-2 bg-primary text-on-primary font-label-caps text-[9px] uppercase tracking-widest px-2 py-0.5 shadow-sm">
                  <?= htmlspecialchars($product['badge']) ?>
                </span>
              <?php endif; ?>

              <!-- Wishlist Trigger -->
              <button data-product-id="<?= $product['id'] ?>" aria-label="Toggle Wishlist" class="wishlist-toggle-btn absolute top-2 right-2 w-8 h-8 rounded-full bg-surface/90 backdrop-blur-sm flex items-center justify-center text-on-surface-variant hover:text-primary shadow-sm hover:scale-110 active:scale-95 transition-transform <?= in_array($product['id'], $wishlistIds) ? 'text-primary' : '' ?>">
                <span class="material-symbols-outlined text-[18px] <?= in_array($product['id'], $wishlistIds) ? 'fill-1' : '' ?>">favorite</span>
              </button>

              <!-- Quick Action Overlay -->
              <div class="absolute inset-x-2 bottom-2 flex flex-col gap-1.5 opacity-90 group-hover:opacity-100 transition-opacity">
                <button data-product-id="<?= $product['id'] ?>" class="open-quickview-btn w-full py-1.5 bg-surface-container-lowest/95 backdrop-blur-sm text-on-surface font-label-caps text-[10px] uppercase tracking-wider text-center shadow-sm hover:bg-surface transition-colors border border-outline-variant/30">
                  Quick View
                </button>
                <button onclick="quickAddToCart(<?= $product['id'] ?>)" class="w-full py-1.5 bg-primary text-on-primary font-label-caps text-[10px] uppercase tracking-wider text-center shadow-sm hover:bg-primary-container transition-colors">
                  + Quick Add
                </button>
              </div>
            </div>

            <!-- Card Content Details -->
            <div class="p-3 flex flex-col gap-1.5 justify-between flex-1">
              <div>
                <span class="font-label-caps text-[9px] text-on-surface-variant uppercase tracking-widest block">
                  <?= htmlspecialchars($product['fabric'] ?? 'Handloom') ?>
                </span>
                
                <h3 class="font-serif text-[15px] text-on-surface leading-tight line-clamp-1 mt-0.5">
                  <a href="product.php?id=<?= $product['id'] ?>" class="hover:text-primary transition-colors">
                    <?= htmlspecialchars($product['title']) ?>
                  </a>
                </h3>

                <!-- Pricing & Promo -->
                <div class="flex items-baseline gap-2 mt-1">
                  <span class="font-price-display text-primary text-[16px] font-bold">
                    <?= formatINR($product['price']) ?>
                  </span>
                  <?php if (!empty($product['original_price'])): ?>
                    <span class="font-sans text-xs text-outline line-through">
                      <?= formatINR($product['original_price']) ?>
                    </span>
                    <?php if ($product['discount_percent'] > 0): ?>
                      <span class="font-label-caps text-[9px] text-primary font-bold">
                        <?= $product['discount_percent'] ?>% OFF
                      </span>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Mini Size Availability Indicator -->
              <div class="flex items-center gap-1.5 pt-2 border-t border-surface-container-high/40 text-[9px] font-label-caps text-on-surface-variant">
                <span class="uppercase text-outline">Sizes:</span>
                <span class="text-on-surface font-semibold">XS</span>
                <span class="text-primary font-bold underline">S</span>
                <span class="text-on-surface font-semibold">M</span>
                <span class="text-on-surface font-semibold">L</span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Handloom Guarantee Strip -->
    <section class="mt-4 p-5 bg-surface-container-low shadow-sm flex items-center gap-4 rounded-xl border border-surface-container-high/60">
      <div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center shrink-0 text-on-secondary-container">
        <span class="material-symbols-outlined text-[24px]">verified</span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-caps text-on-surface tracking-wider font-semibold">
          Verified Handloom & Sustainable Weaves
        </span>
        <p class="font-sans text-xs text-on-surface-variant mt-0.5 leading-relaxed">
          Every garment is individually numbered and woven with authentic natural fibers by traditional artisan guilds.
        </p>
      </div>
    </section>

  </div>
</main>

<!-- Filter Bottom Sheet Drawer -->
<div id="filterDrawer" class="fixed inset-0 z-50 velora-modal-backdrop hidden items-end sm:items-center sm:justify-center transition-opacity">
  <div class="w-full sm:max-w-lg bg-surface max-h-[85vh] flex flex-col shadow-2xl overflow-hidden border border-outline-variant/40">
    <!-- Drawer Header -->
    <div class="px-5 py-4 bg-surface-container flex items-center justify-between border-b border-surface-container-high/60">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[22px]">tune</span>
        <span class="font-serif text-lg text-on-surface font-medium">Filter Collection</span>
      </div>
      <button onclick="toggleFilterDrawer(false)" aria-label="Close filters" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <!-- Scrollable Filter Form -->
    <form method="GET" action="shop.php" class="p-6 flex flex-col gap-6 overflow-y-auto">
      <?php if ($sort): ?><input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>"/><?php endif; ?>

      <!-- Silhouette Filter -->
      <div class="flex flex-col gap-2">
        <span class="font-label-caps text-on-surface font-semibold">Silhouette</span>
        <div class="flex flex-wrap gap-2">
          <?php foreach (['Maxi', 'Midi', 'Flared Gown', 'Anarkali Dress', 'Free Silhouette'] as $sil): ?>
            <label class="cursor-pointer">
              <input type="radio" name="silhouette" value="<?= htmlspecialchars($sil) ?>" class="hidden peer" <?= $selectedSilhouette === $sil ? 'checked' : '' ?>/>
              <span class="px-3.5 py-1.5 border border-outline-variant text-xs font-label-caps rounded-full peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary block transition-colors">
                <?= htmlspecialchars($sil) ?>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Fabric Filter -->
      <div class="flex flex-col gap-2">
        <span class="font-label-caps text-on-surface font-semibold">Fabric & Weave</span>
        <div class="flex flex-wrap gap-2">
          <?php foreach (['Mulmul Silk', 'Chiffon Organza', 'Cotton Linen', 'Georgette'] as $fab): ?>
            <label class="cursor-pointer">
              <input type="radio" name="fabric" value="<?= htmlspecialchars($fab) ?>" class="hidden peer" <?= $selectedFabric === $fab ? 'checked' : '' ?>/>
              <span class="px-3.5 py-1.5 border border-outline-variant text-xs font-label-caps rounded-full peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary block transition-colors">
                <?= htmlspecialchars($fab) ?>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Occasion Filter -->
      <div class="flex flex-col gap-2">
        <span class="font-label-caps text-on-surface font-semibold">Occasion</span>
        <div class="flex flex-wrap gap-2">
          <?php foreach (['Cocktail Soirée', 'Festive Gathering', 'Day Luncheon', 'Atelier Salon'] as $occ): ?>
            <label class="cursor-pointer">
              <input type="radio" name="occasion" value="<?= htmlspecialchars($occ) ?>" class="hidden peer" <?= $selectedOccasion === $occ ? 'checked' : '' ?>/>
              <span class="px-3.5 py-1.5 border border-outline-variant text-xs font-label-caps rounded-full peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary block transition-colors">
                <?= htmlspecialchars($occ) ?>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Drawer Actions -->
      <div class="pt-4 border-t border-surface-container-high/60 flex items-center gap-3">
        <a href="shop.php" class="flex-1 py-3 text-center bg-surface border border-outline-variant text-on-surface font-label-caps text-xs tracking-wider uppercase hover:bg-surface-container">
          Reset All
        </a>
        <button type="submit" class="flex-1 py-3 bg-primary text-on-primary font-label-caps text-xs tracking-wider uppercase hover:bg-primary-container transition-colors shadow-sm">
          Apply Filters
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function toggleFilterDrawer(open) {
    const drawer = document.getElementById('filterDrawer');
    if (drawer) {
      if (open) {
        drawer.classList.remove('hidden');
        drawer.classList.add('flex');
      } else {
        drawer.classList.add('hidden');
        drawer.classList.remove('flex');
      }
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
