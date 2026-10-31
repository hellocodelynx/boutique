<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/functions.php';

$pdo = getDbConnection();
$orderNumber = sanitize($_GET['order'] ?? '');

$order = null;
$orderItems = [];

if ($orderNumber) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();

    if ($order) {
        $stmtItems = $pdo->prepare("
            SELECT oi.*, p.primary_image 
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmtItems->execute([$order['id']]);
        $orderItems = $stmtItems->fetchAll();
    }
}

$pageTitle = 'Order Confirmed | VELORA Luxury Atelier';
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 flex flex-col relative w-full pt-[7.5rem] pb-safe bg-surface">
  <div class="max-w-velora w-full mx-auto px-4 sm:px-8 flex flex-col gap-8 max-w-2xl py-6">

    <div class="bg-surface-container-lowest p-8 rounded-xl border border-surface-container-high/60 shadow-lg text-center space-y-6">
      
      <div class="w-16 h-16 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center mx-auto shadow-md">
        <span class="material-symbols-outlined text-[36px] text-secondary">verified</span>
      </div>

      <div>
        <span class="font-label-caps text-secondary font-bold tracking-widest text-xs">Atelier Order Confirmed</span>
        <h1 class="font-serif text-3xl sm:text-4xl text-on-surface mt-1">Thank You For Your Story</h1>
        <p class="font-sans text-xs sm:text-sm text-on-surface-variant mt-2 leading-relaxed max-w-md mx-auto">
          Your bespoke order has been registered in the VELORA atelier ledger. Our master draper will prepare your pieces in unbleached muslin wrap.
        </p>
      </div>

      <!-- Order ID Badge -->
      <div class="inline-block px-5 py-3 rounded-lg bg-surface-container-low border border-outline-variant/40">
        <span class="font-label-caps text-[10px] text-outline uppercase block">Order Reference Number</span>
        <span class="font-serif text-xl sm:text-2xl text-primary font-bold tracking-wider">
          <?= htmlspecialchars($orderNumber ?: 'VEL-' . date('Ymd') . '-9841') ?>
        </span>
      </div>

      <!-- Order Items Detail -->
      <?php if (!empty($orderItems)): ?>
        <div class="border-t border-surface-container-high pt-6 text-left space-y-3">
          <span class="font-label-caps text-xs text-on-surface font-semibold tracking-wider block">Ordered Silhouettes</span>
          <div class="divide-y divide-surface-container-high">
            <?php foreach ($orderItems as $oi): ?>
              <div class="flex items-center justify-between py-2.5">
                <div class="flex items-center gap-3">
                  <?php if (!empty($oi['primary_image'])): ?>
                    <img class="w-10 h-12 rounded object-cover" src="<?= htmlspecialchars($oi['primary_image']) ?>" alt="Piece"/>
                  <?php endif; ?>
                  <div>
                    <p class="font-serif text-xs text-on-surface"><?= htmlspecialchars($oi['product_title']) ?></p>
                    <p class="font-sans text-[10px] text-on-surface-variant">Qty: <?= $oi['quantity'] ?> • Size: <?= htmlspecialchars($oi['size_code'] ?? 'S') ?></p>
                  </div>
                </div>
                <span class="font-price-display text-xs font-bold text-primary"><?= formatINR($oi['total_price']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          
          <div class="border-t border-surface-container-high pt-3 flex justify-between items-baseline">
            <span class="font-serif text-sm font-semibold text-on-surface">Total Paid / Due on Delivery</span>
            <span class="font-price-display text-xl text-primary font-bold">
              <?= formatINR($order['total_amount'] ?? 0) ?>
            </span>
          </div>
        </div>
      <?php endif; ?>

      <!-- White-glove perks strip -->
      <div class="p-4 rounded-lg bg-surface-container-low text-left space-y-1 text-xs">
        <div class="flex items-center gap-2 text-primary font-bold font-label-caps text-[10px]">
          <span class="material-symbols-outlined text-[16px]">check_circle</span>
          <span>Pan-India Doorstep Alteration Master Assigned</span>
        </div>
        <p class="font-sans text-[11px] text-on-surface-variant">
          Expected Delivery within 3 business days. You will receive an SMS and WhatsApp tracking update shortly.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 pt-2">
        <a href="shop.php" class="flex-1 py-3.5 bg-primary text-on-primary font-label-caps text-xs tracking-widest uppercase hover:bg-primary-container transition-colors rounded shadow-md text-center">
          Continue Exploring
        </a>
        <button onclick="window.print()" class="flex-1 py-3.5 bg-surface border border-outline-variant text-on-surface font-label-caps text-xs tracking-widest uppercase hover:bg-surface-container transition-colors rounded text-center">
          Print Receipt
        </button>
      </div>

    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
