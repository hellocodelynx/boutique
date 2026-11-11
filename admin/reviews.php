<?php
define('VELORA_ACCESS', true);
$currentTab = 'reviews';
$pageTitle = 'Client Diary & Reviews';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = getDbConnection();

// Handle deletion
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    $revId = (int)($_POST['review_id'] ?? 0);
    if ($revId > 0) {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$revId]);
        $message = "Review #$revId removed from public gaze.";
    }
}

$reviews = $pdo->query("
    SELECT r.*, 
           COALESCE(p.title, 'Atelier Couture Edition') as product_title, 
           COALESCE(p.primary_image, 'assets/img/miraya-pleated-wine-maxi.jpg') as product_image
    FROM reviews r
    LEFT JOIN products p ON r.product_id = p.id
    ORDER BY r.id DESC
")->fetchAll();

$totalReviewsCount = count($reviews);
$avgRating = $totalReviewsCount > 0 ? round(array_sum(array_column($reviews, 'rating')) / $totalReviewsCount, 1) : 5.0;
$fiveStarCount = count(array_filter($reviews, fn($x) => (int)$x['rating'] === 5));
?>

<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-8">
  <div>
    <div class="flex items-center gap-2 text-on-surface-variant text-[10px] uppercase font-bold tracking-widest mb-1">
      <span>Atelier Core</span>
      <span class="text-outline">/</span>
      <span class="text-primary font-bold">Patron Testimonials</span>
    </div>
    <h1 class="font-serif text-3xl md:text-4xl text-primary font-medium tracking-tight">Client Diary &amp; Connoisseur Reviews</h1>
    <p class="text-xs text-on-surface-variant mt-1">Review feedback, doorstep fitting scores, and testimonials submitted by patrons across salons.</p>
  </div>

  <!-- Summary Metric Badges -->
  <div class="flex items-center gap-3">
    <div class="px-4 py-2.5 rounded-xl bg-surface-container-lowest border border-surface-container-high/60 shadow-sm flex items-center gap-2.5 text-xs font-semibold">
      <span class="material-symbols-outlined text-secondary text-[18px]">auto_stories</span>
      <span><?= $totalReviewsCount ?> Total Reviews</span>
    </div>
    <div class="px-4 py-2.5 rounded-xl bg-surface-container-lowest border border-surface-container-high/60 shadow-sm flex items-center gap-2 text-xs font-semibold">
      <span class="material-symbols-outlined text-[#D4AF37] text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
      <span class="text-primary font-bold"><?= $avgRating ?> Avg Rating</span>
      <span class="text-[10px] text-on-surface-variant">(<?= $fiveStarCount ?> 5★)</span>
    </div>
  </div>
</div>

<?php if ($message): ?>
<div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-2.5 text-xs font-medium">
  <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
  <span><?= htmlspecialchars($message) ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php foreach ($reviews as $r): ?>
  <div class="p-6 rounded-2xl bg-surface-container-lowest border border-surface-container-high/60 shadow-sm flex flex-col justify-between">
    <div class="space-y-4">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-xs shadow-sm">
            <?= htmlspecialchars($r['author_initials'] ?? 'C') ?>
          </div>
          <div>
            <h4 class="text-xs font-semibold text-on-surface"><?= htmlspecialchars($r['author_name']) ?></h4>
            <span class="text-[10px] uppercase tracking-wider text-secondary font-bold block"><?= htmlspecialchars($r['author_badge'] ?? 'Verified Connoisseur') ?></span>
          </div>
        </div>
        <div class="flex text-secondary text-[14px]">
          <?php for ($i = 0; $i < (int)$r['rating']; $i++): ?>
          <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
          <?php endfor; ?>
        </div>
      </div>

      <p class="text-xs text-on-surface-variant italic leading-relaxed">
        “<?= htmlspecialchars($r['comment']) ?>”
      </p>

      <?php if (!empty($r['customer_image'])): ?>
      <div class="relative w-full h-36 rounded-xl overflow-hidden bg-surface-container">
        <img src="<?= htmlspecialchars(veloraImageUrl($r['customer_image'])) ?>" class="w-full h-full object-cover" alt="Patron Photo"/>
      </div>
      <?php endif; ?>

      <div class="pt-3 border-t border-surface-container-high/40 flex items-center gap-2.5">
        <img src="<?= htmlspecialchars(veloraImageUrl($r['product_image'])) ?>" alt="<?= htmlspecialchars($r['product_title']) ?>" class="w-8 h-10 object-cover rounded bg-surface-container"/>
        <span class="text-[11px] font-semibold text-on-surface line-clamp-1"><?= htmlspecialchars($r['product_title']) ?></span>
      </div>
    </div>

    <div class="pt-4 mt-4 border-t border-surface-container-high/40 flex items-center justify-between">
      <span class="text-[10px] text-outline font-mono"><?= date('M d, Y', strtotime($r['created_at'])) ?></span>
      <form action="reviews.php" method="POST" onsubmit="return confirm('Remove this review from public display?');">
        <input type="hidden" name="action" value="delete_review"/>
        <input type="hidden" name="review_id" value="<?= $r['id'] ?>"/>
        <button type="submit" class="text-[10px] uppercase tracking-wider text-error font-semibold hover:underline flex items-center gap-1">
          <span class="material-symbols-outlined text-[14px]">delete</span>
          Remove
        </button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
