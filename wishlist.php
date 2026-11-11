<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/functions.php';

$pdo = getDbConnection();
$sessionId = getSessionId();

$stmt = $pdo->prepare("
    SELECT p.*, w.created_at as saved_at, c.name as category_name
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE w.session_id = ?
    ORDER BY w.id DESC
");
$stmt->execute([$sessionId]);
$wishlistItems = $stmt->fetchAll();

$pageTitle = 'Saved Pieces & Wishlist | VELORA Atelier';
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 flex flex-col relative w-full pt-[7.5rem] pb-safe bg-surface">
  <div class="max-w-velora w-full mx-auto px-4 sm:px-8 flex flex-col gap-6">

    <div class="flex items-baseline justify-between border-b border-surface-container-high pb-4">
      <div>
        <h1 class="font-serif text-3xl text-on-surface">Saved Atelier Pieces</h1>
        <p class="font-sans text-xs text-on-surface-variant mt-1">
          Your private curations saved for bespoke fittings and upcoming celebrations.
        </p>
      </div>
      <span class="font-sans text-xs text-on-surface-variant font-medium">
        <?= count($wishlistItems) ?> Saved
      </span>
    </div>

    <?php if (empty($wishlistItems)): ?>
      <div class="py-20 text-center bg-surface-container-low rounded-xl border border-surface-container-high/60 my-6">
        <span class="material-symbols-outlined text-5xl text-outline mb-2">favorite</span>
        <h2 class="font-serif text-2xl text-on-surface">No pieces saved yet</h2>
        <p class="font-sans text-xs text-on-surface-variant mt-1 max-w-sm mx-auto">
          Tap the heart on any gown, saree, or kurta in our collection to save your favorites here.
        </p>
        <a href="shop.php" class="mt-6 inline-block px-8 py-3 bg-primary text-on-primary font-label-caps text-xs tracking-widest uppercase hover:bg-primary-container transition-colors shadow-md">
          Explore The Collection
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <?php foreach ($wishlistItems as $item): ?>
          <article class="velora-card flex flex-col bg-surface-container-lowest shadow-sm rounded-none overflow-hidden group border border-surface-container-high/60">
            <div class="relative w-full aspect-[3/4] bg-surface-container overflow-hidden">
              <a href="product.php?id=<?= $item['id'] ?>" class="block w-full h-full">
                <img class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105" src="<?= htmlspecialchars(veloraImageUrl($item['primary_image'])) ?>" alt="<?= htmlspecialchars($item['title']) ?>"/>
              </a>

              <!-- Remove from Wishlist button -->
              <button onclick="removeWishlistItem(<?= $item['id'] ?>)" aria-label="Remove from wishlist" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-surface/90 backdrop-blur-sm flex items-center justify-center text-primary shadow-sm hover:scale-110 active:scale-95 transition-transform">
                <span class="material-symbols-outlined text-[18px] fill-1">favorite</span>
              </button>
            </div>

            <div class="p-3.5 flex flex-col gap-2 justify-between flex-1">
              <div>
                <span class="font-label-caps text-[9px] text-on-surface-variant uppercase tracking-widest block">
                  <?= htmlspecialchars($item['fabric'] ?? 'Handcrafted') ?>
                </span>
                
                <h3 class="font-serif text-[15px] text-on-surface leading-tight line-clamp-1 mt-0.5">
                  <a href="product.php?id=<?= $item['id'] ?>" class="hover:text-primary transition-colors">
                    <?= htmlspecialchars($item['title']) ?>
                  </a>
                </h3>

                <p class="font-price-display text-primary text-[16px] font-bold mt-1">
                  <?= formatINR($item['price']) ?>
                </p>
              </div>

              <div class="pt-2 border-t border-surface-container-high/40 flex flex-col gap-1.5">
                <button onclick="quickAddToCart(<?= $item['id'] ?>)" class="w-full py-2 bg-primary text-on-primary font-label-caps text-[10px] uppercase tracking-wider text-center hover:bg-primary-container transition-colors shadow-sm flex items-center justify-center gap-1">
                  <span class="material-symbols-outlined text-[14px]">local_mall</span> Move To Bag
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<script>
  async function removeWishlistItem(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);

    const res = await fetch('api/wishlist.php?action=toggle', {
      method: 'POST',
      body: formData
    });
    const d = await res.json();
    if (d.success) {
      window.location.reload();
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
