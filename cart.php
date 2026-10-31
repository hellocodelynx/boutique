<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/functions.php';

$pdo = getDbConnection();
$sessionId = getSessionId();

// Fetch items in current bag
$stmt = $pdo->prepare("
    SELECT ci.*, p.title, p.subtitle, p.primary_image, p.slug 
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.session_id = ?
    ORDER BY ci.id DESC
");
$stmt->execute([$sessionId]);
$cartItems = $stmt->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}

$discount = 0;
$appliedCoupon = $_SESSION['velora_applied_coupon'] ?? null;
if ($appliedCoupon) {
    $stmtC = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmtC->execute([$appliedCoupon]);
    $c = $stmtC->fetch();
    if ($c && $subtotal >= $c['min_spend']) {
        $discount = ($c['discount_type'] === 'percentage') 
            ? ($subtotal * $c['discount_value']) / 100 
            : min($subtotal, (float)$c['discount_value']);
    } else {
        unset($_SESSION['velora_applied_coupon']);
        $appliedCoupon = null;
    }
}

$shipping = ($subtotal >= 2999 || $subtotal == 0) ? 0 : 250;
$total = max(0, $subtotal - $discount + $shipping);

$pageTitle = 'Your Shopping Bag | VELORA Atelier';
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 flex flex-col relative w-full pt-[7.5rem] pb-safe bg-surface">
  <div class="max-w-velora w-full mx-auto px-4 sm:px-8 flex flex-col gap-6">

    <div class="flex items-baseline justify-between border-b border-surface-container-high pb-4">
      <h1 class="font-serif text-3xl text-on-surface">Your Shopping Bag</h1>
      <span class="font-sans text-xs text-on-surface-variant font-medium">
        <?= count($cartItems) ?> Item<?= count($cartItems) !== 1 ? 's' : '' ?>
      </span>
    </div>

    <?php if (empty($cartItems)): ?>
      <div class="py-20 text-center bg-surface-container-low rounded-xl border border-surface-container-high/60 my-6">
        <span class="material-symbols-outlined text-5xl text-outline mb-2">shopping_bag</span>
        <h2 class="font-serif text-2xl text-on-surface">Your bag is currently empty</h2>
        <p class="font-sans text-xs text-on-surface-variant mt-1 max-w-sm mx-auto">
          Explore our autumn/winter collection of handcrafted silk gowns, ethnic kurtas, and artisanal co-ords.
        </p>
        <a href="shop.php" class="mt-6 inline-block px-8 py-3 bg-primary text-on-primary font-label-caps text-xs tracking-widest uppercase hover:bg-primary-container transition-colors shadow-md">
          Explore The Atelier
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Cart Items List (Left 2 cols) -->
        <div class="lg:col-span-2 space-y-4">
          <?php foreach ($cartItems as $item): ?>
            <div id="cart-row-<?= $item['id'] ?>" class="bg-surface-container-lowest p-4 rounded-xl border border-surface-container-high/60 shadow-sm flex gap-4 items-center">
              <a href="product.php?id=<?= $item['product_id'] ?>" class="w-20 sm:w-24 aspect-[3/4] rounded bg-surface-container overflow-hidden shrink-0">
                <img class="w-full h-full object-cover" src="<?= htmlspecialchars($item['primary_image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>"/>
              </a>

              <div class="flex-1 min-w-0 flex flex-col justify-between py-1">
                <div>
                  <div class="flex justify-between items-start gap-2">
                    <h3 class="font-serif text-base sm:text-lg text-on-surface truncate">
                      <a href="product.php?id=<?= $item['product_id'] ?>" class="hover:text-primary transition-colors">
                        <?= htmlspecialchars($item['title']) ?>
                      </a>
                    </h3>
                    <button onclick="removeCartItem(<?= $item['id'] ?>)" aria-label="Remove item" class="text-outline hover:text-error transition-colors p-1">
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>

                  <div class="flex items-center gap-3 font-label-caps text-[10px] text-on-surface-variant mt-1">
                    <span>Size: <strong class="text-on-surface"><?= htmlspecialchars($item['size_code'] ?? 'S') ?></strong></span>
                    <span>•</span>
                    <span>Color: <strong class="text-on-surface"><?= htmlspecialchars($item['color_name'] ?? 'Royal Wine') ?></strong></span>
                  </div>
                </div>

                <div class="flex items-center justify-between mt-4">
                  <!-- Step Quantity Counter -->
                  <div class="flex items-center bg-surface-container rounded h-8 px-2 border border-outline-variant/40">
                    <button onclick="updateItemQuantity(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)" class="w-6 h-6 flex items-center justify-center text-on-surface hover:text-primary font-bold">
                      <span class="material-symbols-outlined text-[14px]">remove</span>
                    </button>
                    <span class="w-6 text-center font-sans font-bold text-xs text-on-surface"><?= $item['quantity'] ?></span>
                    <button onclick="updateItemQuantity(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)" class="w-6 h-6 flex items-center justify-center text-on-surface hover:text-primary font-bold">
                      <span class="material-symbols-outlined text-[14px]">add</span>
                    </button>
                  </div>

                  <!-- Price Calculation -->
                  <div class="text-right">
                    <span class="font-price-display text-base font-bold text-primary">
                      <?= formatINR($item['price'] * $item['quantity']) ?>
                    </span>
                    <?php if ($item['quantity'] > 1): ?>
                      <span class="block font-sans text-[10px] text-outline">
                        <?= formatINR($item['price']) ?> each
                      </span>
                    <?php endif; ?>
                  </div>
                </div>

              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Order Summary & Promo Code (Right 1 col) -->
        <div class="bg-surface-container-low p-6 rounded-xl border border-surface-container-high/60 shadow-sm space-y-5">
          <h2 class="font-serif text-xl text-on-surface font-semibold border-b border-surface-container-high pb-3">
            Order Summary
          </h2>

          <!-- Voucher Application Form -->
          <div class="space-y-2">
            <span class="font-label-caps text-xs text-on-surface font-semibold">Promotional Voucher</span>
            <div class="flex gap-2">
              <input id="cart-coupon-input" class="flex-1 h-10 px-3 bg-surface border border-outline-variant/60 rounded text-xs uppercase tracking-wider focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Enter VELORA10" value="<?= htmlspecialchars($appliedCoupon ?? '') ?>"/>
              <button onclick="applyCartCoupon()" class="px-4 h-10 bg-tertiary text-on-tertiary font-label-caps text-xs tracking-wider uppercase hover:bg-primary transition-colors rounded">
                Apply
              </button>
            </div>
            <?php if ($appliedCoupon): ?>
              <p class="font-sans text-[11px] text-secondary font-semibold">
                ✓ Voucher <?= htmlspecialchars($appliedCoupon) ?> applied successfully!
              </p>
            <?php endif; ?>
          </div>

          <!-- Cost Calculations -->
          <div class="space-y-2.5 font-sans text-xs border-t border-surface-container-high pt-4">
            <div class="flex justify-between text-on-surface-variant">
              <span>Subtotal</span>
              <span class="font-price-display font-semibold text-on-surface"><?= formatINR($subtotal) ?></span>
            </div>

            <?php if ($discount > 0): ?>
              <div class="flex justify-between text-primary font-medium">
                <span>Voucher Discount</span>
                <span class="font-price-display font-semibold">-<?= formatINR($discount) ?></span>
              </div>
            <?php endif; ?>

            <div class="flex justify-between text-on-surface-variant">
              <span>Pan-India Shipping</span>
              <span>
                <?php if ($shipping === 0): ?>
                  <span class="text-secondary font-bold font-label-caps text-[10px]">COMPLIMENTARY</span>
                <?php else: ?>
                  <span class="font-price-display font-semibold text-on-surface"><?= formatINR($shipping) ?></span>
                <?php endif; ?>
              </span>
            </div>

            <div class="border-t border-surface-container-high pt-3 flex justify-between items-baseline">
              <span class="font-serif text-base text-on-surface font-semibold">Estimated Total</span>
              <span class="font-price-display text-2xl text-primary font-bold">
                <?= formatINR($total) ?>
              </span>
            </div>
          </div>

          <a href="checkout.php" class="w-full py-3.5 bg-primary hover:bg-primary-container text-on-primary font-label-caps text-xs tracking-widest uppercase font-bold text-center flex items-center justify-center gap-2 rounded shadow-lg transition-transform active:scale-[0.99]">
            <span>Proceed to Checkout</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </a>

          <p class="font-sans text-[10px] text-outline text-center">
            🔒 256-bit Bank Grade Encrypted Checkout • Compliant with RBI Standards
          </p>
        </div>

      </div>
    <?php endif; ?>

  </div>
</main>

<script>
  async function updateItemQuantity(itemId, qty) {
    const formData = new FormData();
    formData.append('item_id', itemId);
    formData.append('quantity', qty);

    const res = await fetch('api/cart.php?action=update', {
      method: 'POST',
      body: formData
    });
    const d = await res.json();
    if (d.success) {
      window.location.reload();
    }
  }

  async function removeCartItem(itemId) {
    const formData = new FormData();
    formData.append('item_id', itemId);

    const res = await fetch('api/cart.php?action=remove', {
      method: 'POST',
      body: formData
    });
    const d = await res.json();
    if (d.success) {
      window.location.reload();
    }
  }

  async function applyCartCoupon() {
    const code = document.getElementById('cart-coupon-input').value.trim();
    if (!code) return;

    const formData = new FormData();
    formData.append('code', code);

    const res = await fetch('api/cart.php?action=apply_coupon', {
      method: 'POST',
      body: formData
    });
    const d = await res.json();
    if (d.success) {
      triggerAddedToast(d.message);
      setTimeout(() => window.location.reload(), 800);
    } else {
      triggerAddedToast(d.error || 'Failed to apply voucher', false);
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
