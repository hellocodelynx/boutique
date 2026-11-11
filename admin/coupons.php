<?php
define('VELORA_ACCESS', true);
$currentTab = 'coupons';
$pageTitle = 'Promo Codes & Vouchers';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = getDbConnection();

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_coupon') {
        $couponId = (int)($_POST['coupon_id'] ?? 0);
        $current = (int)($_POST['current_status'] ?? 0);
        $newStatus = $current === 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE coupons SET is_active = ? WHERE id = ?");
        $stmt->execute([$newStatus, $couponId]);
        $message = "Voucher status toggled successfully.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'create_coupon') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['discount_type'] ?? 'percentage';
        $value = (float)($_POST['discount_value'] ?? 10);
        $minSpend = (float)($_POST['min_spend'] ?? 0);
        $desc = trim($_POST['description'] ?? '');

        if (!empty($code)) {
            $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_spend, description, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$code, $type, $value, $minSpend, $desc]);
            $message = "New voucher code '$code' created successfully.";
        }
    }
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-8">
  <div>
    <div class="flex items-center gap-2 text-on-surface-variant text-[10px] uppercase font-bold tracking-widest mb-1">
      <span>Atelier Core</span>
      <span class="text-outline">/</span>
      <span class="text-primary font-bold">Privilege Vouchers</span>
    </div>
    <h1 class="font-serif text-3xl md:text-4xl text-primary font-medium tracking-tight">Promotional Codes &amp; Privileges</h1>
    <p class="text-xs text-on-surface-variant mt-1">Configure salon discounts, welcome gifts (`VELORA500`), and percentage incentives (`VELORA10`).</p>
  </div>

  <button onclick="openNewCouponModal()" class="px-6 py-3 rounded-xl bg-primary hover:bg-primary-container text-on-primary text-xs uppercase tracking-wider font-semibold transition-colors flex items-center gap-2 shadow-md shrink-0">
    <span class="material-symbols-outlined text-[18px]">add</span>
    + Create Privilege Voucher
  </button>
</div>

<?php if ($message): ?>
<div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-2.5 text-xs font-medium">
  <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
  <span><?= htmlspecialchars($message) ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <?php foreach ($coupons as $c): ?>
  <div class="p-6 rounded-2xl bg-surface-container-lowest border border-surface-container-high/60 shadow-sm flex flex-col justify-between relative overflow-hidden">
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <span class="font-mono text-base font-bold text-primary px-3 py-1 bg-primary-fixed/40 rounded-lg">
          <?= htmlspecialchars($c['code']) ?>
        </span>
        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold <?= $c['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-outline' ?>">
          <?= $c['is_active'] ? 'Active' : 'Disabled' ?>
        </span>
      </div>

      <div class="pt-2">
        <div class="font-serif text-2xl text-on-surface font-semibold">
          <?= $c['discount_type'] === 'percentage' ? (int)$c['discount_value'] . '% OFF' : '₹' . number_format($c['discount_value']) . ' OFF' ?>
        </div>
        <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">
          <?= htmlspecialchars($c['description']) ?>
        </p>
      </div>

      <div class="text-[11px] text-outline font-mono pt-2 border-t border-surface-container-high/40">
        Min Spend: ₹<?= number_format((float)$c['min_spend']) ?>
      </div>
    </div>

    <form action="coupons.php" method="POST" class="pt-4 mt-4 border-t border-surface-container-high/40 flex justify-end">
      <input type="hidden" name="action" value="toggle_coupon"/>
      <input type="hidden" name="coupon_id" value="<?= $c['id'] ?>"/>
      <input type="hidden" name="current_status" value="<?= $c['is_active'] ?>"/>
      <button type="submit" class="text-xs font-semibold uppercase tracking-wider text-primary hover:underline cursor-pointer">
        <?= $c['is_active'] ? 'Deactivate' : 'Activate' ?>
      </button>
    </form>
  </div>
  <?php endforeach; ?>
</div>

<!-- New Voucher Modal -->
<div id="newCouponModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface w-full max-w-md rounded-2xl shadow-2xl border border-surface-container-high overflow-hidden flex flex-col">
    <div class="p-5 bg-surface-container-low border-b border-surface-container-high/60 flex items-center justify-between">
      <h3 class="font-serif text-lg text-primary font-medium">Create Privilege Voucher</h3>
      <button onclick="closeNewCouponModal()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-surface-container text-on-surface-variant">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form action="coupons.php" method="POST" class="p-6 space-y-4 text-xs">
      <input type="hidden" name="action" value="create_coupon"/>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Coupon Code</label>
        <input name="code" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 font-mono uppercase" placeholder="e.g. DIWALI20"/>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Discount Type</label>
          <select name="discount_type" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60">
            <option value="percentage">Percentage (%)</option>
            <option value="flat">Flat Amount (₹)</option>
          </select>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Discount Value</label>
          <input name="discount_value" required type="number" step="0.01" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="10 or 500"/>
        </div>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Minimum Spend (₹)</label>
        <input name="min_spend" type="number" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" value="2999"/>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Description</label>
        <input name="description" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="10% off on bridal orders above ₹2,999"/>
      </div>

      <div class="pt-4 flex justify-end gap-3 border-t border-surface-container-high/60">
        <button type="button" onclick="closeNewCouponModal()" class="px-4 py-2 bg-surface border border-outline-variant rounded-lg text-xs font-semibold uppercase tracking-wider">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg text-xs font-semibold uppercase tracking-wider shadow-md">
          Save Voucher
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openNewCouponModal() {
    const m = document.getElementById('newCouponModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
  }
  function closeNewCouponModal() {
    const m = document.getElementById('newCouponModal');
    m.classList.remove('flex');
    m.classList.add('hidden');
  }
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

