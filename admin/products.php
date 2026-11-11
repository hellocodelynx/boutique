<?php
define('VELORA_ACCESS', true);
$currentTab = 'products';
$pageTitle = 'Product Catalog & Inventory';

require_once __DIR__ . '/includes/admin_header.php';

$pdo = getDbConnection();

// Handle product updates (Stock, Price, Status, Details)
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'quick_update') {
        $prodId       = (int)($_POST['product_id'] ?? 0);
        $title        = trim($_POST['title'] ?? '');
        $categoryId   = (int)($_POST['category_id'] ?? 0);
        $stock        = (int)($_POST['stock_quantity'] ?? 0);
        $price        = (float)($_POST['price'] ?? 0);
        $origPrice    = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : ($price * 1.25);
        $fabric       = trim($_POST['fabric'] ?? '');
        $silhouette   = trim($_POST['silhouette'] ?? '');
        $image        = trim($_POST['primary_image'] ?? '');
        $isFeatured   = isset($_POST['is_featured']) ? 1 : 0;
        $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
        $isNewArrival = isset($_POST['is_new_arrival']) ? 1 : 0;

        if ($prodId > 0 && $price > 0) {
            $updateFields = [
                'price = ?',
                'original_price = ?',
                'stock_quantity = ?',
                'is_featured = ?',
                'is_bestseller = ?',
                'is_new_arrival = ?'
            ];
            $updateParams = [$price, $origPrice, $stock, $isFeatured, $isBestseller, $isNewArrival];

            if (!empty($title)) {
                $updateFields[] = 'title = ?';
                $updateParams[] = $title;
            }
            if ($categoryId > 0) {
                $updateFields[] = 'category_id = ?';
                $updateParams[] = $categoryId;
            }
            if (!empty($fabric)) {
                $updateFields[] = 'fabric = ?';
                $updateParams[] = $fabric;
            }
            if (!empty($silhouette)) {
                $updateFields[] = 'silhouette = ?';
                $updateParams[] = $silhouette;
            }
            if (!empty($image)) {
                $updateFields[] = 'primary_image = ?';
                $updateParams[] = $image;
            }

            $updateParams[] = $prodId;
            $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($updateParams);
            $message = "Product #$prodId updated successfully.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'create_product') {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $price = (float)($_POST['price'] ?? 0);
        $fabric = trim($_POST['fabric'] ?? 'Mulmul Silk');
        $silhouette = trim($_POST['silhouette'] ?? 'Maxi');
        $occasion = trim($_POST['occasion'] ?? 'Atelier Salon');
        $stock = (int)($_POST['stock_quantity'] ?? 10);
        $image = trim($_POST['primary_image'] ?? 'assets/img/miraya-pleated-wine-maxi.jpg');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title)) . '-' . time();

        if (!empty($title) && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO products (slug, title, subtitle, category_id, silhouette, fabric, occasion, price, original_price, primary_image, stock_quantity, is_new_arrival) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$slug, $title, $subtitle, $categoryId, $silhouette, $fabric, $occasion, $price, $price * 1.25, $image, $stock]);
            $message = "New Haute Edition '$title' created successfully!";
        }
    }
}

// Fetch products
$search = trim($_GET['q'] ?? '');
$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.silhouette LIKE ? OR p.fabric LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<!-- Header -->
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-8">
  <div>
    <div class="flex items-center gap-2 text-on-surface-variant text-[10px] uppercase font-bold tracking-widest mb-1">
      <span>Atelier Core</span>
      <span class="text-outline">/</span>
      <span class="text-primary font-bold">Catalog &amp; Inventory</span>
    </div>
    <h1 class="font-serif text-3xl md:text-4xl text-primary font-medium tracking-tight">Product Catalog Management</h1>
    <p class="text-xs text-on-surface-variant mt-1">Manage silk gowns, ethnic silhouettes, prices, live inventory levels, and editorial badges.</p>
  </div>

  <button onclick="openNewProductModal()" class="px-6 py-3 rounded-xl bg-primary hover:bg-primary-container text-on-primary text-xs uppercase tracking-wider font-semibold transition-colors flex items-center gap-2 shadow-md shrink-0">
    <span class="material-symbols-outlined text-[18px]">add</span>
    Add New Haute Edition
  </button>
</div>

<?php if ($message): ?>
<div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-2.5 text-xs font-medium">
  <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
  <span><?= htmlspecialchars($message) ?></span>
</div>
<?php endif; ?>

<!-- Products Table -->
<div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-container-high/60 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="bg-surface-container-low/80 text-on-surface-variant text-[10px] uppercase tracking-wider border-b border-surface-container-high/60">
          <th class="py-4 px-4 font-bold">Piece &amp; Silhouette</th>
          <th class="py-4 px-4 font-bold">Collection</th>
          <th class="py-4 px-4 font-bold">Price (INR)</th>
          <th class="py-4 px-4 font-bold">Stock</th>
          <th class="py-4 px-4 font-bold">Attributes</th>
          <th class="py-4 px-4 font-bold text-right">Quick Edit</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-surface-container-high/40">
        <?php foreach ($products as $prod): ?>
        <tr class="hover:bg-surface-container-low/40 transition-colors">
          
          <td class="py-4 px-4">
            <div class="flex items-center gap-3">
              <img src="<?= htmlspecialchars(veloraImageUrl($prod['primary_image'])) ?>" alt="<?= htmlspecialchars($prod['title']) ?>" class="w-12 h-14 object-cover rounded-lg bg-surface-container border border-surface-container-high/60 shrink-0"/>
              <div>
                <a href="../product.php?id=<?= $prod['id'] ?>" target="_blank" class="text-xs font-semibold text-on-surface hover:text-primary transition-colors block line-clamp-1">
                  <?= htmlspecialchars($prod['title']) ?>
                </a>
                <span class="text-[10px] text-outline font-mono block mt-0.5"><?= htmlspecialchars($prod['silhouette']) ?> • <?= htmlspecialchars($prod['fabric']) ?></span>
                <?php if (!empty($prod['badge'])): ?>
                <span class="inline-block px-2 py-0.5 rounded text-[9px] uppercase font-bold bg-primary-fixed text-primary mt-1">
                  <?= htmlspecialchars($prod['badge']) ?>
                </span>
                <?php endif; ?>
              </div>
            </div>
          </td>

          <td class="py-4 px-4">
            <span class="text-xs font-semibold text-on-surface"><?= htmlspecialchars($prod['category_name']) ?></span>
            <span class="text-[10px] text-outline block"><?= htmlspecialchars($prod['occasion']) ?></span>
          </td>

          <td class="py-4 px-4">
            <span class="font-serif text-sm font-bold text-primary">₹<?= number_format((float)$prod['price']) ?></span>
            <?php if (!empty($prod['original_price']) && $prod['original_price'] > $prod['price']): ?>
            <span class="text-[10px] text-outline line-through block">₹<?= number_format((float)$prod['original_price']) ?></span>
            <?php endif; ?>
          </td>

          <td class="py-4 px-4">
            <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold <?= $prod['stock_quantity'] <= 5 ? 'bg-error-container text-error' : ($prod['stock_quantity'] <= 12 ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container text-on-surface') ?>">
              <?= $prod['stock_quantity'] ?> units
            </span>
          </td>

          <td class="py-4 px-4">
            <div class="flex items-center gap-1.5 flex-wrap">
              <?php if ($prod['is_featured']): ?>
              <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold bg-secondary-fixed text-secondary">Featured</span>
              <?php endif; ?>
              <?php if ($prod['is_bestseller']): ?>
              <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold bg-primary-container text-on-primary">Bestseller</span>
              <?php endif; ?>
              <?php if ($prod['is_new_arrival']): ?>
              <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold bg-surface-container text-on-surface">New</span>
              <?php endif; ?>
            </div>
          </td>

          <td class="py-4 px-4 text-right">
            <button onclick="editProductModal(<?= (int)$prod['id'] ?>)" class="px-3 py-1.5 bg-surface hover:bg-surface-container text-primary rounded-lg border border-outline-variant/60 text-xs font-semibold uppercase tracking-wider transition-colors inline-flex items-center gap-1 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">edit</span>
              Edit
            </button>
          </td>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Edit Product Modal -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface w-full max-w-xl rounded-2xl shadow-2xl border border-surface-container-high overflow-hidden flex flex-col max-h-[92vh]">
    <div class="p-5 bg-surface-container-low border-b border-surface-container-high/60 flex items-center justify-between">
      <h3 class="font-serif text-lg text-primary font-medium" id="editModalTitle">Edit Product</h3>
      <button onclick="closeEditModal()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-surface-container text-on-surface-variant">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form action="products.php" method="POST" class="p-6 overflow-y-auto space-y-4 text-xs">
      <input type="hidden" name="action" value="quick_update"/>
      <input type="hidden" name="product_id" id="editProdId" value=""/>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Edition Title</label>
        <input name="title" id="editTitle" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 outline-none focus:border-primary"/>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Collection</label>
          <select name="category_id" id="editCategory" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 outline-none focus:border-primary">
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Stock Quantity</label>
          <input name="stock_quantity" id="editStock" required type="number" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 outline-none focus:border-primary"/>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Price (INR ₹)</label>
          <input name="price" id="editPrice" required type="number" step="0.01" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 outline-none focus:border-primary"/>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Original Price (INR ₹)</label>
          <input name="original_price" id="editOrigPrice" type="number" step="0.01" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 outline-none focus:border-primary"/>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Silhouette</label>
          <input name="silhouette" id="editSilhouette" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 outline-none focus:border-primary"/>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Fabric</label>
          <input name="fabric" id="editFabric" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 outline-none focus:border-primary"/>
        </div>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Primary Image Path</label>
        <input name="primary_image" id="editImage" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60 outline-none focus:border-primary"/>
      </div>

      <div class="flex items-center gap-6 pt-2">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_featured" id="editFeatured" class="w-4 h-4 rounded text-primary accent-primary"/>
          <span class="text-xs text-on-surface font-semibold">Featured Drop</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_bestseller" id="editBestseller" class="w-4 h-4 rounded text-primary accent-primary"/>
          <span class="text-xs text-on-surface font-semibold">Bestseller</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_new_arrival" id="editNewArrival" class="w-4 h-4 rounded text-primary accent-primary"/>
          <span class="text-xs text-on-surface font-semibold">New Arrival</span>
        </label>
      </div>

      <div class="pt-4 flex justify-end gap-3 border-t border-surface-container-high/60">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 bg-surface border border-outline-variant rounded-lg text-xs font-semibold uppercase tracking-wider">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg text-xs font-semibold uppercase tracking-wider shadow-md">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<!-- New Product Modal -->
<div id="newProductModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface w-full max-w-xl rounded-2xl shadow-2xl border border-surface-container-high overflow-hidden flex flex-col max-h-[90vh]">
    <div class="p-5 bg-surface-container-low border-b border-surface-container-high/60 flex items-center justify-between">
      <h3 class="font-serif text-lg text-primary font-medium">Add New Haute Edition</h3>
      <button onclick="closeNewProductModal()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-surface-container text-on-surface-variant">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form action="products.php" method="POST" class="p-6 overflow-y-auto space-y-4 text-xs">
      <input type="hidden" name="action" value="create_product"/>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Edition Title</label>
        <input name="title" required class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="e.g. The Nayantara Flared Brocade Gown"/>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Subtitle / Craft Note</label>
        <input name="subtitle" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="e.g. Hand-spun Banarasi Silk with Micro Pleating"/>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Collection</label>
          <select name="category_id" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60">
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Price (INR ₹)</label>
          <input name="price" required type="number" step="0.01" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="7490"/>
        </div>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Silhouette</label>
          <input name="silhouette" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" value="Maxi"/>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Fabric</label>
          <input name="fabric" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" value="Mulmul Silk"/>
        </div>
        <div>
          <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Stock</label>
          <input name="stock_quantity" type="number" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" value="12"/>
        </div>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-on-surface-variant block mb-1">Image Path</label>
        <input name="primary_image" class="w-full bg-surface-container-low text-on-surface p-2.5 rounded-lg border border-outline-variant/60" placeholder="assets/img/image-name.jpg"/>
      </div>

      <div class="pt-4 flex justify-end gap-3 border-t border-surface-container-high/60">
        <button type="button" onclick="closeNewProductModal()" class="px-4 py-2.5 bg-surface border border-outline-variant rounded-lg text-xs font-semibold uppercase tracking-wider">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg text-xs font-semibold uppercase tracking-wider shadow-md">
          Publish Edition
        </button>
      </div>
    </form>
  </div>
</div>

<!-- JavaScript for Modals & Data Handling -->
<script>
  // Product Catalog map populated from PHP with safe JSON escaping
  const productsCatalog = <?= json_encode(array_column($products, null, 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

  function editProductModal(prodOrId) {
    let prod = null;
    if (typeof prodOrId === 'number' || typeof prodOrId === 'string') {
      prod = productsCatalog[prodOrId];
    } else if (typeof prodOrId === 'object' && prodOrId !== null) {
      prod = prodOrId;
    }
    if (!prod) return;

    document.getElementById('editModalTitle').textContent = 'Edit: ' + (prod.title || 'Edition');
    document.getElementById('editProdId').value = prod.id || '';
    if (document.getElementById('editTitle')) document.getElementById('editTitle').value = prod.title || '';
    if (document.getElementById('editCategory')) document.getElementById('editCategory').value = prod.category_id || '';
    if (document.getElementById('editPrice')) document.getElementById('editPrice').value = prod.price || '';
    if (document.getElementById('editOrigPrice')) document.getElementById('editOrigPrice').value = prod.original_price || '';
    if (document.getElementById('editStock')) document.getElementById('editStock').value = prod.stock_quantity || '';
    if (document.getElementById('editSilhouette')) document.getElementById('editSilhouette').value = prod.silhouette || '';
    if (document.getElementById('editFabric')) document.getElementById('editFabric').value = prod.fabric || '';
    if (document.getElementById('editImage')) document.getElementById('editImage').value = prod.primary_image || '';
    if (document.getElementById('editFeatured')) document.getElementById('editFeatured').checked = (prod.is_featured == 1);
    if (document.getElementById('editBestseller')) document.getElementById('editBestseller').checked = (prod.is_bestseller == 1);
    if (document.getElementById('editNewArrival')) document.getElementById('editNewArrival').checked = (prod.is_new_arrival == 1);
    
    const m = document.getElementById('editModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
  }

  function closeEditModal() {
    const m = document.getElementById('editModal');
    m.classList.remove('flex');
    m.classList.add('hidden');
  }

  function openNewProductModal() {
    const m = document.getElementById('newProductModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
  }

  function closeNewProductModal() {
    const m = document.getElementById('newProductModal');
    m.classList.remove('flex');
    m.classList.add('hidden');
  }
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
