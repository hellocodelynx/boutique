<?php
define('VELORA_ACCESS', true);
$currentTab = 'categories';
$pageTitle = 'Collections & Silhouettes';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = getDbConnection();

$categories = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as live_count
    FROM categories c
    ORDER BY c.id ASC
")->fetchAll();
?>

<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-8">
  <div>
    <div class="flex items-center gap-2 text-on-surface-variant text-[10px] uppercase font-bold tracking-widest mb-1">
      <span>Atelier Core</span>
      <span class="text-outline">/</span>
      <span class="text-primary font-bold">Curated Collections</span>
    </div>
    <h1 class="font-serif text-3xl md:text-4xl text-primary font-medium tracking-tight">Atelier Collections &amp; Silhouettes</h1>
    <p class="text-xs text-on-surface-variant mt-1">Organize haute couture categories, editorial cover banners, and catalogue taxonomies.</p>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php foreach ($categories as $cat): ?>
  <div class="bg-surface-container-lowest rounded-2xl border border-surface-container-high/60 shadow-sm overflow-hidden flex flex-col justify-between">
    <div class="relative w-full h-44 bg-surface-container overflow-hidden">
      <img src="<?= htmlspecialchars(veloraImageUrl($cat['image_url'])) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover"/>
      <div class="absolute inset-0 bg-gradient-to-t from-primary/80 via-transparent to-transparent"></div>
      <div class="absolute bottom-3 left-4 right-4 text-on-primary flex items-center justify-between">
        <h3 class="font-serif text-lg font-medium"><?= htmlspecialchars($cat['name']) ?></h3>
        <span class="px-2 py-0.5 rounded-full bg-surface text-primary text-[10px] uppercase font-bold">
          <?= $cat['live_count'] ?> Pieces
        </span>
      </div>
    </div>

    <div class="p-5 space-y-3">
      <p class="text-xs text-on-surface-variant leading-relaxed">
        <?= htmlspecialchars($cat['description']) ?>
      </p>
      <div class="pt-2 border-t border-surface-container-high/40 flex items-center justify-between text-[11px]">
        <span class="text-outline font-mono">slug: <?= htmlspecialchars($cat['slug']) ?></span>
        <a href="../shop.php?category=<?= urlencode($cat['slug']) ?>" target="_blank" class="text-primary font-semibold hover:underline flex items-center gap-1">
          View in Store
          <span class="material-symbols-outlined text-[14px]">open_in_new</span>
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
