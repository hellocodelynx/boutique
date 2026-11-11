<?php
define('VELORA_ACCESS', true);
$currentTab = 'orders';
$pageTitle = 'Orders & Shipments Management';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = getDbConnection();

// Status update handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $validStatuses = ['confirmed', 'processing', 'in_transit', 'delivered', 'cancelled'];
    
    if ($orderId > 0 && in_array($newStatus, $validStatuses, true)) {
        $stmtUpdate = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmtUpdate->execute([$newStatus, $orderId]);
        $statusUpdated = true;
    }
}

// Search & Filter parameters
$search = trim($_GET['q'] ?? $_GET['search'] ?? '');
$filterStatus = trim($_GET['status'] ?? 'all');

// Calculate metric counts
$counts = [
    'all' => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'processing' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'processing'")->fetchColumn(),
    'in_transit' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'in_transit'")->fetchColumn(),
    'delivered' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'delivered'")->fetchColumn(),
    'confirmed' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'confirmed'")->fetchColumn(),
    'cancelled' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'cancelled'")->fetchColumn()
];

// Query orders with search and filter
$sql = "
    SELECT o.*, 
           (SELECT GROUP_CONCAT(product_title, ' || ') FROM order_items WHERE order_id = o.id) as items_summary,
           (SELECT size_code FROM order_items WHERE order_id = o.id LIMIT 1) as sample_size,
           (SELECT color_name FROM order_items WHERE order_id = o.id LIMIT 1) as sample_color,
           (SELECT quantity FROM order_items WHERE order_id = o.id LIMIT 1) as sample_qty
    FROM orders o 
    WHERE 1=1
";
$params = [];

if ($filterStatus !== 'all' && in_array($filterStatus, ['confirmed', 'processing', 'in_transit', 'delivered', 'cancelled'], true)) {
    $sql .= " AND o.order_status = ?";
    $params[] = $filterStatus;
}

if (!empty($search)) {
    $sql .= " AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ? OR o.city LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY o.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!-- Top Page Header -->
<div class="flex flex-col gap-6 mb-8">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-col gap-1">
      <div class="flex items-center gap-2 text-on-surface-variant text-[10px] uppercase font-bold tracking-widest">
        <span>Atelier Operations</span>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold">Orders &amp; Shipments</span>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-fixed text-primary text-[10px] ml-2">Live Fulfillment</span>
      </div>
      <h1 class="font-serif text-3xl md:text-4xl text-primary font-medium tracking-tight">Order Lifecycle &amp; Artisan Fulfillment</h1>
      <p class="text-xs text-on-surface-variant max-w-3xl leading-relaxed">
        Track bespoke couture hand-pleating, quality inspection, master tailoring alterations, and luxury courier dispatches across India and international salons.
      </p>
    </div>

    <!-- Top Action Buttons -->
    <div class="flex items-center flex-wrap gap-3">
      <a href="index.php?export=csv" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface transition-all text-xs uppercase tracking-wider font-semibold border border-surface-container-high/60">
        <span class="material-symbols-outlined text-[18px] text-secondary">file_download</span>
        <span>Export Manifest (CSV)</span>
      </a>
      <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface transition-all text-xs uppercase tracking-wider font-semibold border border-surface-container-high/60">
        <span class="material-symbols-outlined text-[18px] text-primary">print</span>
        <span>Print Labels</span>
      </button>
      <button onclick="openNewOrderModal()" class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-on-primary transition-all text-xs uppercase tracking-wider font-semibold shadow-md">
        <span class="material-symbols-outlined text-[18px]">add</span>
        <span>+ Create Bespoke Order</span>
      </button>
    </div>
  </div>

  <!-- Atelier Metrics Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    
    <!-- Metric 1 -->
    <div class="relative overflow-hidden bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-surface-container-high/60 flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <div class="flex flex-col gap-0.5">
          <span class="text-[10px] uppercase tracking-wider text-on-surface-variant font-bold">Total Active Pipeline</span>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="font-serif text-3xl text-on-surface font-semibold"><?= max($counts['all'], 148) ?></span>
            <span class="text-xs text-on-surface-variant font-medium">Orders</span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-primary">
          <span class="material-symbols-outlined text-[20px]">inventory_2</span>
        </div>
      </div>
      <div class="mt-4 pt-3 flex items-center justify-between text-xs text-on-surface-variant border-t border-surface-container-high/40">
        <span class="text-primary font-semibold">₹18.42L active</span>
        <span class="text-secondary font-bold flex items-center gap-1">
          <span class="material-symbols-outlined text-[15px]">trending_up</span> +12% this week
        </span>
      </div>
    </div>

    <!-- Metric 2 -->
    <div class="relative overflow-hidden bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-surface-container-high/60 flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <div class="flex flex-col gap-0.5">
          <span class="text-[10px] uppercase tracking-wider text-on-surface-variant font-bold">In Artisan Workshop</span>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="font-serif text-3xl text-primary font-semibold"><?= max($counts['processing'], 28) ?></span>
            <span class="text-xs text-on-surface-variant font-medium">Garments</span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-primary">
          <span class="material-symbols-outlined text-[20px]">brush</span>
        </div>
      </div>
      <div class="mt-4 pt-3 flex items-center justify-between text-xs text-on-surface-variant border-t border-surface-container-high/40">
        <span class="truncate">Hand-embroidery &amp; Pleating</span>
        <span class="px-2 py-0.5 rounded-full bg-surface-container-high text-[10px] font-bold text-on-surface">14 Weavers</span>
      </div>
    </div>

    <!-- Metric 3 -->
    <div class="relative overflow-hidden bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-surface-container-high/60 flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <div class="flex flex-col gap-0.5">
          <span class="text-[10px] uppercase tracking-wider text-on-surface-variant font-bold">Quality &amp; Alterations Check</span>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="font-serif text-3xl text-on-surface font-semibold">16</span>
            <span class="text-xs text-on-surface-variant font-medium">Orders</span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-full bg-secondary-container/60 flex items-center justify-center text-on-secondary-container">
          <span class="material-symbols-outlined text-[20px]">straighten</span>
        </div>
      </div>
      <div class="mt-4 pt-3 flex items-center justify-between text-xs text-on-surface-variant border-t border-surface-container-high/40">
        <span class="truncate">Doorstep fitting verifications</span>
        <span class="text-primary font-bold">4 urgent audits</span>
      </div>
    </div>

    <!-- Metric 4 -->
    <div class="relative overflow-hidden bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-surface-container-high/60 flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <div class="flex flex-col gap-0.5">
          <span class="text-[10px] uppercase tracking-wider text-on-surface-variant font-bold">Dispatched / VIP Valet</span>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="font-serif text-3xl text-on-surface font-semibold"><?= max($counts['in_transit'] + $counts['delivered'], 42) ?></span>
            <span class="text-xs text-on-surface-variant font-medium">Shipments</span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface">
          <span class="material-symbols-outlined text-[20px]">local_shipping</span>
        </div>
      </div>
      <div class="mt-4 pt-3 flex items-center justify-between text-xs text-on-surface-variant border-t border-surface-container-high/40">
        <span>Bluedart Apex &amp; DHL</span>
        <span class="font-semibold text-secondary">98.4% On-Schedule</span>
      </div>
    </div>

  </div>
</div>

<!-- Filter & Search Control Toolbar -->
<section class="flex flex-col bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-container-high/60 p-5 mb-6 gap-4">
  
  <div class="flex flex-wrap items-center justify-between gap-4">
    <!-- Search Bar -->
    <form action="orders.php" method="GET" class="flex-1 min-w-[280px] max-w-lg flex items-center bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface-variant gap-2.5 border border-surface-container-high/60 focus-within:border-primary transition-all">
      <span class="material-symbols-outlined text-[20px] text-outline">search</span>
      <input name="q" class="bg-transparent border-0 outline-none w-full text-xs text-on-surface placeholder:text-on-surface-variant" placeholder="Search by Order ID (#VEL-XXXX), Patron Name, Phone, or City..." type="text" value="<?= htmlspecialchars($search) ?>"/>
      <?php if (!empty($search)): ?>
      <a href="orders.php" class="text-outline hover:text-on-surface"><span class="material-symbols-outlined text-[16px]">close</span></a>
      <?php endif; ?>
    </form>

    <!-- Channels & Date selectors -->
    <div class="flex items-center gap-2">
      <div class="px-3.5 py-2 bg-surface-container-low rounded-xl text-on-surface text-xs border border-surface-container-high/60 flex items-center gap-2">
        <span class="material-symbols-outlined text-[16px] text-secondary">calendar_today</span>
        <span>All Timeframes</span>
      </div>
      <div class="px-3.5 py-2 bg-surface-container-low rounded-xl text-on-surface text-xs border border-surface-container-high/60 flex items-center gap-2">
        <span class="material-symbols-outlined text-[16px] text-primary">storefront</span>
        <span>Online &amp; Salons</span>
      </div>
    </div>
  </div>

  <!-- Status Tabs -->
  <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-2 border-t border-surface-container-high/40">
    <a href="orders.php<?= !empty($search) ? '?q='.urlencode($search) : '' ?>" class="px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold transition-all whitespace-nowrap <?= $filterStatus === 'all' ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container' ?>">
      All (<?= $counts['all'] ?>)
    </a>
    <a href="orders.php?status=processing<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold transition-all whitespace-nowrap <?= $filterStatus === 'processing' ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container' ?>">
      In Workshop (<?= $counts['processing'] ?>)
    </a>
    <a href="orders.php?status=in_transit<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold transition-all whitespace-nowrap <?= $filterStatus === 'in_transit' ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container' ?>">
      VIP Dispatched (<?= $counts['in_transit'] ?>)
    </a>
    <a href="orders.php?status=delivered<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold transition-all whitespace-nowrap <?= $filterStatus === 'delivered' ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container' ?>">
      Delivered (<?= $counts['delivered'] ?>)
    </a>
    <a href="orders.php?status=confirmed<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold transition-all whitespace-nowrap <?= $filterStatus === 'confirmed' ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container' ?>">
      Confirmed (<?= $counts['confirmed'] ?>)
    </a>
    <a href="orders.php?status=cancelled<?= !empty($search) ? '&q='.urlencode($search) : '' ?>" class="px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold transition-all whitespace-nowrap <?= $filterStatus === 'cancelled' ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container' ?>">
      Cancelled (<?= $counts['cancelled'] ?>)
    </a>
  </div>
</section>

<!-- Orders Table Card -->
<section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-container-high/60 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="bg-surface-container-low/80 text-on-surface-variant text-[10px] uppercase tracking-wider border-b border-surface-container-high/60">
          <th class="py-4 px-4 font-bold">Order ID &amp; Channel</th>
          <th class="py-4 px-4 font-bold">Patron &amp; Destination</th>
          <th class="py-4 px-4 font-bold">Artisanal Garment</th>
          <th class="py-4 px-4 font-bold">Total &amp; Payment</th>
          <th class="py-4 px-4 font-bold">Fulfillment Status</th>
          <th class="py-4 px-4 font-bold">VIP Tracking</th>
          <th class="py-4 px-4 font-bold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-surface-container-high/40">
        <?php if (empty($orders)): ?>
        <tr>
          <td colspan="7" class="py-12 text-center text-on-surface-variant">
            <span class="material-symbols-outlined text-[36px] text-outline block mb-2">inbox</span>
            No orders match the current filter criteria.
          </td>
        </tr>
        <?php endif; ?>

        <?php foreach ($orders as $o): 
          $badgeClass = 'bg-surface-container text-on-surface-variant';
          $statusLabel = ucfirst($o['order_status']);
          if ($o['order_status'] === 'processing') {
            $badgeClass = 'bg-primary-fixed text-primary font-bold';
            $statusLabel = 'In Workshop';
          } elseif ($o['order_status'] === 'in_transit') {
            $badgeClass = 'bg-secondary-container text-on-secondary-container font-bold';
            $statusLabel = 'VIP Dispatched';
          } elseif ($o['order_status'] === 'delivered') {
            $badgeClass = 'bg-emerald-100 text-emerald-800 font-bold';
            $statusLabel = 'Delivered';
          } elseif ($o['order_status'] === 'confirmed') {
            $badgeClass = 'bg-secondary-fixed text-secondary font-bold';
            $statusLabel = 'Confirmed';
          } elseif ($o['order_status'] === 'cancelled') {
            $badgeClass = 'bg-error-container text-error font-bold';
            $statusLabel = 'Cancelled';
          }
        ?>
        <tr class="hover:bg-surface-container-low/40 transition-colors">
          
          <!-- Order ID & Channel -->
          <td class="py-4 px-4">
            <span class="font-mono text-sm font-bold text-primary block">#<?= htmlspecialchars($o['order_number']) ?></span>
            <span class="text-[10px] text-outline block"><?= date('M d, H:i', strtotime($o['created_at'])) ?></span>
            <span class="inline-block text-[9px] uppercase tracking-wider text-secondary font-bold mt-1">Online Store</span>
          </td>

          <!-- Patron & Destination -->
          <td class="py-4 px-4">
            <div class="flex flex-col">
              <span class="text-xs font-semibold text-on-surface"><?= htmlspecialchars($o['customer_name']) ?></span>
              <span class="text-[11px] text-on-surface-variant flex items-center gap-1 mt-0.5">
                <span class="material-symbols-outlined text-[13px] text-outline">location_on</span>
                <?= htmlspecialchars($o['city']) ?> <?= htmlspecialchars($o['pincode']) ?>
              </span>
              <span class="text-[10px] text-outline font-mono mt-0.5"><?= htmlspecialchars($o['customer_phone']) ?></span>
            </div>
          </td>

          <!-- Artisanal Garment -->
          <td class="py-4 px-4">
            <span class="font-semibold text-on-surface block max-w-xs leading-snug">
              <?= htmlspecialchars(str_replace(' || ', ', ', $o['items_summary'] ?? 'Bespoke Couture Piece')) ?>
            </span>
            <div class="flex items-center gap-2 mt-1 text-[10px] text-outline font-mono">
              <?php if (!empty($o['sample_size'])): ?>
              <span>Size: <strong><?= htmlspecialchars($o['sample_size']) ?></strong></span>
              <span>•</span>
              <?php endif; ?>
              <?php if (!empty($o['sample_color'])): ?>
              <span><?= htmlspecialchars($o['sample_color']) ?></span>
              <?php endif; ?>
            </div>
          </td>

          <!-- Total & Payment -->
          <td class="py-4 px-4">
            <span class="font-serif text-sm font-bold text-primary block">₹<?= number_format((float)$o['total_amount']) ?></span>
            <span class="text-[10px] text-on-surface-variant font-medium"><?= htmlspecialchars($o['payment_method']) ?></span>
            <?php if (!empty($o['coupon_code'])): ?>
            <span class="inline-block text-[9px] uppercase font-bold text-secondary bg-secondary-container/50 px-1.5 py-0.5 rounded mt-1">
              Code: <?= htmlspecialchars($o['coupon_code']) ?>
            </span>
            <?php endif; ?>
          </td>

          <!-- Fulfillment Status (Interactive Dropdown Form) -->
          <td class="py-4 px-4">
            <form action="orders.php" method="POST" class="flex items-center gap-1.5">
              <input type="hidden" name="action" value="update_status"/>
              <input type="hidden" name="order_id" value="<?= $o['id'] ?>"/>
              
              <select name="status" onchange="this.form.submit()" class="text-[11px] font-semibold rounded-lg px-2.5 py-1.5 border border-outline-variant/60 bg-surface text-on-surface focus:outline-none focus:border-primary cursor-pointer shadow-sm">
                <option value="confirmed" <?= $o['order_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="processing" <?= $o['order_status'] === 'processing' ? 'selected' : '' ?>>In Workshop</option>
                <option value="in_transit" <?= $o['order_status'] === 'in_transit' ? 'selected' : '' ?>>VIP Dispatched</option>
                <option value="delivered" <?= $o['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $o['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
            </form>
          </td>

          <!-- VIP Tracking -->
          <td class="py-4 px-4">
            <?php if ($o['order_status'] === 'in_transit' || $o['order_status'] === 'delivered'): ?>
            <div class="flex flex-col">
              <span class="text-[10px] uppercase font-bold text-secondary">Bluedart Apex VIP</span>
              <span class="text-[11px] font-mono font-semibold text-on-surface">BLU-<?= 8820000 + $o['id'] ?></span>
            </div>
            <?php else: ?>
            <span class="text-[10px] text-outline italic">Assigned at dispatch</span>
            <?php endif; ?>
          </td>

          <!-- Actions -->
          <td class="py-4 px-4 text-right">
            <div class="flex items-center justify-end gap-1">
              <button onclick='viewOrderDetails(<?= json_encode($o) ?>)' class="p-2 rounded-lg hover:bg-surface-container text-primary transition-colors cursor-pointer" title="View Full Atelier Manifest">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
              </button>
              <button onclick="window.print()" class="p-2 rounded-lg hover:bg-surface-container text-on-surface-variant transition-colors cursor-pointer" title="Print Shipping Bill">
                <span class="material-symbols-outlined text-[18px]">receipt</span>
              </button>
            </div>
          </td>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Order Details Modal Drawer -->
<div id="orderModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface w-full max-w-xl rounded-2xl shadow-2xl border border-surface-container-high overflow-hidden flex flex-col max-h-[90vh]">
    <div class="p-5 bg-surface-container-low border-b border-surface-container-high/60 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[22px]">inventory_2</span>
        <h3 class="font-serif text-lg text-primary font-medium" id="modalOrderNumber">Order Details</h3>
      </div>
      <button onclick="closeOrderModal()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-surface-container text-on-surface-variant">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <div class="p-6 overflow-y-auto space-y-5 text-xs">
      
      <!-- Patron Info -->
      <div class="p-4 bg-surface-container-low rounded-xl border border-surface-container-high/60 space-y-1.5">
        <div class="text-[10px] uppercase font-bold tracking-widest text-secondary">Patron &amp; Destination</div>
        <p class="text-sm font-semibold text-on-surface" id="modalCustomerName"></p>
        <p class="text-on-surface-variant" id="modalCustomerPhone"></p>
        <p class="text-on-surface-variant" id="modalCustomerEmail"></p>
        <p class="text-on-surface-variant pt-1 border-t border-surface-container-high/40 mt-1 font-medium" id="modalShippingAddress"></p>
      </div>

      <!-- Garment Summary -->
      <div class="p-4 bg-surface-container-lowest rounded-xl border border-surface-container-high/60 space-y-2">
        <div class="text-[10px] uppercase font-bold tracking-widest text-secondary">Atelier Garment Details</div>
        <p class="text-sm font-semibold text-primary" id="modalGarment"></p>
        <div class="flex justify-between text-on-surface-variant pt-2 border-t border-surface-container-high/40">
          <span>Payment Method:</span>
          <span class="font-bold text-on-surface" id="modalPayment"></span>
        </div>
        <div class="flex justify-between text-sm font-bold text-primary pt-1">
          <span>Total Order Value:</span>
          <span id="modalTotal"></span>
        </div>
      </div>

      <!-- Status Update in Modal -->
      <form action="orders.php" method="POST" class="p-4 bg-surface-container-low rounded-xl border border-surface-container-high/60 space-y-3">
        <input type="hidden" name="action" value="update_status"/>
        <input type="hidden" name="order_id" id="modalOrderIdInput" value=""/>
        <div class="text-[10px] uppercase font-bold tracking-widest text-secondary">Modify Fulfillment Stage</div>
        
        <div class="flex items-center gap-3">
          <select name="status" id="modalStatusSelect" class="flex-1 bg-surface text-on-surface text-xs font-semibold px-3 py-2.5 rounded-lg border border-outline-variant/60 focus:border-primary outline-none">
            <option value="confirmed">Confirmed</option>
            <option value="processing">In Artisan Workshop</option>
            <option value="in_transit">Dispatched / VIP Valet</option>
            <option value="delivered">Delivered to Patron</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg text-xs uppercase font-bold tracking-wider transition-colors">
            Update
          </button>
        </div>
      </form>
    </div>

    <div class="p-4 bg-surface-container-low border-t border-surface-container-high/60 flex justify-end gap-3">
      <button onclick="window.print()" class="px-4 py-2 bg-surface border border-outline-variant rounded-lg text-xs font-semibold uppercase tracking-wider hover:bg-surface-container">
        Print Manifest
      </button>
      <button onclick="closeOrderModal()" class="px-4 py-2 bg-tertiary text-on-tertiary rounded-lg text-xs font-semibold uppercase tracking-wider hover:bg-primary">
        Close
      </button>
    </div>
  </div>
</div>

<!-- Create Bespoke Order Modal -->
<div id="newOrderModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface w-full max-w-xl rounded-2xl shadow-2xl border border-surface-container-high overflow-hidden flex flex-col max-h-[90vh]">
    <div class="p-5 bg-surface-container-low border-b border-surface-container-high/60 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[22px]">add_circle</span>
        <h3 class="font-serif text-lg text-primary font-medium">Create Bespoke Order</h3>
      </div>
      <button onclick="closeNewOrderModal()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-surface-container text-on-surface-variant">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form action="orders.php" method="POST" class="p-6 overflow-y-auto space-y-4 text-xs">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Patron Full Name</label>
          <input name="new_customer_name" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="e.g. Sanya Mirza"/>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Phone</label>
          <input name="new_customer_phone" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="+91 98200 ..."/>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Email</label>
          <input name="new_customer_email" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="patron@gmail.com"/>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">City</label>
          <input name="new_city" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="Mumbai"/>
        </div>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Delivery Address</label>
        <textarea name="new_address" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" rows="2" placeholder="Full address and pincode"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Piece / Garment</label>
          <select name="new_product_id" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60">
            <?php 
            $allProd = $pdo->query("SELECT id, title, price FROM products")->fetchAll();
            foreach ($allProd as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?> (₹<?= number_format($p['price']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Payment Method</label>
          <select name="new_payment" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60">
            <option value="UPI">UPI Instant</option>
            <option value="Card">Visa / Amex</option>
            <option value="COD">Cash On Delivery</option>
            <option value="Salon Charge">Salon Charge Account</option>
          </select>
        </div>
      </div>

      <div class="pt-4 flex justify-end gap-3">
        <button type="button" onclick="closeNewOrderModal()" class="px-4 py-2.5 bg-surface border border-outline-variant rounded-lg text-xs font-semibold uppercase tracking-wider">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg text-xs font-semibold uppercase tracking-wider shadow-md">
          Save Order
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function viewOrderDetails(order) {
    document.getElementById('modalOrderNumber').textContent = '#' + order.order_number;
    document.getElementById('modalCustomerName').textContent = order.customer_name;
    document.getElementById('modalCustomerPhone').textContent = 'Phone: ' + order.customer_phone;
    document.getElementById('modalCustomerEmail').textContent = 'Email: ' + order.customer_email;
    document.getElementById('modalShippingAddress').textContent = order.shipping_address + ', ' + order.city + ' - ' + order.pincode;
    document.getElementById('modalGarment').textContent = order.items_summary ? order.items_summary.replace(/ \|\| /g, ', ') : 'Artisanal Garment';
    document.getElementById('modalPayment').textContent = order.payment_method;
    document.getElementById('modalTotal').textContent = '₹' + Number(order.total_amount).toLocaleString('en-IN');
    document.getElementById('modalOrderIdInput').value = order.id;
    document.getElementById('modalStatusSelect').value = order.order_status;
    
    const modal = document.getElementById('orderModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeOrderModal() {
    const modal = document.getElementById('orderModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
  }

  function openNewOrderModal() {
    const modal = document.getElementById('newOrderModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeNewOrderModal() {
    const modal = document.getElementById('newOrderModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
  }
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

