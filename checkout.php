<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/functions.php';

$pdo = getDbConnection();
$sessionId = getSessionId();

// Fetch items
$stmt = $pdo->prepare("
    SELECT ci.*, p.title, p.primary_image 
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.session_id = ?
");
$stmt->execute([$sessionId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

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
    }
}

$shipping = ($subtotal >= 2999) ? 0 : 250;
$total = max(0, $subtotal - $discount + $shipping);

$pageTitle = 'Express Checkout | VELORA Atelier';
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 flex flex-col relative w-full pt-[7.5rem] pb-safe bg-surface">
  <div class="max-w-velora w-full mx-auto px-4 sm:px-8 flex flex-col gap-6">

    <div class="border-b border-surface-container-high pb-4">
      <h1 class="font-serif text-3xl text-on-surface">Atelier Express Checkout</h1>
      <p class="font-sans text-xs text-on-surface-variant mt-1">
        Personalized white-glove delivery with doorstep alteration assurance.
      </p>
    </div>

    <form id="checkout-form" onsubmit="handlePlaceOrder(event)" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
      
      <!-- Shipping & Payment Form (Left 2 cols) -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Contact & Delivery Address -->
        <div class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container-high/60 shadow-sm space-y-4">
          <h2 class="font-serif text-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">local_shipping</span>
            <span>Delivery Destination</span>
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-caps text-[10px] text-on-surface mb-1">Full Name *</label>
              <input required name="customer_name" class="w-full h-11 px-3 bg-surface border border-outline-variant/60 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Aarav Sharma" value="Meera Ranganathan"/>
            </div>
            <div>
              <label class="block font-label-caps text-[10px] text-on-surface mb-1">Email Address *</label>
              <input required type="email" name="customer_email" class="w-full h-11 px-3 bg-surface border border-outline-variant/60 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary" placeholder="meera@domain.com" value="meera.ranganathan@example.com"/>
            </div>
            <div class="sm:col-span-2">
              <label class="block font-label-caps text-[10px] text-on-surface mb-1">Contact Phone (For Alteration Master) *</label>
              <input required type="tel" name="customer_phone" class="w-full h-11 px-3 bg-surface border border-outline-variant/60 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary" placeholder="+91 98765 43210" value="+91 98200 12345"/>
            </div>
            <div class="sm:col-span-2">
              <label class="block font-label-caps text-[10px] text-on-surface mb-1">Bespoke Delivery Address *</label>
              <textarea required rows="2" name="shipping_address" class="w-full p-3 bg-surface border border-outline-variant/60 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary leading-relaxed" placeholder="Apartment / Villa, Street, Landmark">B-402, Imperial Heights, Altamount Road</textarea>
            </div>
            <div>
              <label class="block font-label-caps text-[10px] text-on-surface mb-1">City *</label>
              <input required name="city" class="w-full h-11 px-3 bg-surface border border-outline-variant/60 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Mumbai" value="Mumbai"/>
            </div>
            <div>
              <label class="block font-label-caps text-[10px] text-on-surface mb-1">PIN Code *</label>
              <input required name="pincode" class="w-full h-11 px-3 bg-surface border border-outline-variant/60 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary" placeholder="400026" value="400026"/>
            </div>
          </div>
        </div>

        <!-- Payment Method Selection -->
        <div class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container-high/60 shadow-sm space-y-4">
          <h2 class="font-serif text-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">account_balance_wallet</span>
            <span>Payment Selection</span>
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label class="cursor-pointer">
              <input type="radio" name="payment_method" value="COD" checked class="hidden peer"/>
              <div class="p-4 rounded-lg border border-outline-variant/60 bg-surface peer-checked:border-primary peer-checked:bg-surface-container-low peer-checked:shadow-sm transition-all flex flex-col gap-2">
                <div class="flex items-center justify-between">
                  <span class="material-symbols-outlined text-primary text-[22px]">payments</span>
                  <span class="w-3.5 h-3.5 rounded-full border border-primary peer-checked:bg-primary"></span>
                </div>
                <span class="font-sans font-bold text-xs text-on-surface">Cash On Delivery</span>
                <span class="font-sans text-[11px] text-on-surface-variant">Inspect & pay at your doorstep</span>
              </div>
            </label>

            <label class="cursor-pointer">
              <input type="radio" name="payment_method" value="UPI" class="hidden peer"/>
              <div class="p-4 rounded-lg border border-outline-variant/60 bg-surface peer-checked:border-primary peer-checked:bg-surface-container-low peer-checked:shadow-sm transition-all flex flex-col gap-2">
                <div class="flex items-center justify-between">
                  <span class="material-symbols-outlined text-secondary text-[22px]">qr_code_2</span>
                  <span class="w-3.5 h-3.5 rounded-full border border-primary peer-checked:bg-primary"></span>
                </div>
                <span class="font-sans font-bold text-xs text-on-surface">Instant UPI</span>
                <span class="font-sans text-[11px] text-on-surface-variant">GPay, PhonePe, Paytm, BHIM</span>
              </div>
            </label>

            <label class="cursor-pointer">
              <input type="radio" name="payment_method" value="Card" class="hidden peer"/>
              <div class="p-4 rounded-lg border border-outline-variant/60 bg-surface peer-checked:border-primary peer-checked:bg-surface-container-low peer-checked:shadow-sm transition-all flex flex-col gap-2">
                <div class="flex items-center justify-between">
                  <span class="material-symbols-outlined text-tertiary text-[22px]">credit_card</span>
                  <span class="w-3.5 h-3.5 rounded-full border border-primary peer-checked:bg-primary"></span>
                </div>
                <span class="font-sans font-bold text-xs text-on-surface">Cards & Amex</span>
                <span class="font-sans text-[11px] text-on-surface-variant">All Domestic & Global Cards</span>
              </div>
            </label>
          </div>
        </div>

      </div>

      <!-- Order Summary (Right 1 col) -->
      <div class="bg-surface-container-low p-6 rounded-xl border border-surface-container-high/60 shadow-sm space-y-4">
        <h2 class="font-serif text-lg text-on-surface font-semibold border-b border-surface-container-high pb-3">
          Order Summary (<?= count($cartItems) ?> Items)
        </h2>

        <!-- Mini items listing -->
        <div class="divide-y divide-surface-container-high max-h-60 overflow-y-auto space-y-2 pr-1">
          <?php foreach ($cartItems as $it): ?>
            <div class="flex items-center gap-3 py-2">
              <img class="w-12 h-14 rounded object-cover bg-surface-container shrink-0" src="<?= htmlspecialchars(veloraImageUrl($it['primary_image'])) ?>" alt="<?= htmlspecialchars($it['title']) ?>"/>
              <div class="flex-1 min-w-0">
                <p class="font-serif text-xs text-on-surface truncate"><?= htmlspecialchars($it['title']) ?></p>
                <p class="font-sans text-[10px] text-on-surface-variant">Qty: <?= $it['quantity'] ?> • Size: <?= htmlspecialchars($it['size_code'] ?? 'S') ?></p>
              </div>
              <span class="font-price-display text-xs font-bold text-primary shrink-0">
                <?= formatINR($it['price'] * $it['quantity']) ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="border-t border-surface-container-high pt-3 space-y-2 font-sans text-xs">
          <div class="flex justify-between text-on-surface-variant">
            <span>Subtotal</span>
            <span class="font-price-display font-semibold text-on-surface"><?= formatINR($subtotal) ?></span>
          </div>
          <?php if ($discount > 0): ?>
            <div class="flex justify-between text-primary font-medium">
              <span>Voucher Applied</span>
              <span class="font-price-display font-semibold">-<?= formatINR($discount) ?></span>
            </div>
          <?php endif; ?>
          <div class="flex justify-between text-on-surface-variant">
            <span>Shipping</span>
            <span class="text-secondary font-bold font-label-caps text-[10px]">
              <?= $shipping === 0 ? 'COMPLIMENTARY' : formatINR($shipping) ?>
            </span>
          </div>
          <div class="border-t border-surface-container-high pt-3 flex justify-between items-baseline">
            <span class="font-serif text-base text-on-surface font-bold">Grand Total</span>
            <span class="font-price-display text-2xl text-primary font-bold">
              <?= formatINR($total) ?>
            </span>
          </div>
        </div>

        <button type="submit" id="submit-order-btn" class="w-full py-4 bg-primary hover:bg-primary-container text-on-primary font-label-caps text-xs tracking-widest uppercase font-bold text-center flex items-center justify-center gap-2 rounded shadow-xl transition-all">
          <span class="material-symbols-outlined text-[18px]">verified</span>
          <span>Confirm Atelier Order • <?= formatINR($total) ?></span>
        </button>

        <p class="font-sans text-[10px] text-outline text-center leading-normal">
          By placing your order, you agree to VELORA's terms of service and 7-day atelier exchange policy.
        </p>
      </div>

    </form>

  </div>
</main>

<script>
  async function handlePlaceOrder(e) {
    e.preventDefault();
    const btn = document.getElementById('submit-order-btn');
    btn.disabled = true;
    btn.innerText = 'Crafting Order Dossier...';

    const form = document.getElementById('checkout-form');
    const formData = new FormData(form);

    try {
      const res = await fetch('api/checkout.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        triggerAddedToast('Order Confirmed!');
        setTimeout(() => {
          window.location.href = `order-success.php?order=${encodeURIComponent(data.order_number)}`;
        }, 600);
      } else {
        triggerAddedToast(data.error || 'Failed to place order', false);
        btn.disabled = false;
        btn.innerText = 'Confirm Atelier Order';
      }
    } catch (err) {
      console.error(err);
      triggerAddedToast('Order processing error. Please try again.', false);
      btn.disabled = false;
      btn.innerText = 'Confirm Atelier Order';
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
