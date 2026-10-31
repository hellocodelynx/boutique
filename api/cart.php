<?php
/**
 * VELORA API - Cart Management Endpoint
 */

define('VELORA_ACCESS', true);
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDbConnection();
$sessionId = getSessionId();
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

// -------------------------------------------------------------
// GET CART ITEMS
// -------------------------------------------------------------
if ($action === 'get') {
    $stmt = $pdo->prepare("
        SELECT ci.*, p.title, p.subtitle, p.primary_image, p.slug
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.session_id = ?
        ORDER BY ci.id DESC
    ");
    $stmt->execute([$sessionId]);
    $items = $stmt->fetchAll();

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
    }

    $discount = 0;
    $couponCode = $_SESSION['velora_applied_coupon'] ?? null;
    if ($couponCode) {
        $stmtC = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
        $stmtC->execute([$couponCode]);
        $coupon = $stmtC->fetch();
        if ($coupon) {
            if ($coupon['discount_type'] === 'percentage') {
                $discount = ($subtotal * $coupon['discount_value']) / 100;
            } else {
                $discount = min($subtotal, (float)$coupon['discount_value']);
            }
        }
    }

    $shipping = ($subtotal >= 2999 || $subtotal == 0) ? 0 : 250;
    $total = max(0, $subtotal - $discount + $shipping);

    jsonResponse([
        'success' => true,
        'cart_count' => getCartCount(),
        'items' => $items,
        'subtotal' => $subtotal,
        'discount' => $discount,
        'shipping' => $shipping,
        'total' => $total,
        'coupon_code' => $couponCode
    ]);
}

// -------------------------------------------------------------
// ADD ITEM TO CART
// -------------------------------------------------------------
if ($action === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $colorName = sanitize($_POST['color_name'] ?? 'Default');
    $sizeCode = sanitize($_POST['size_code'] ?? 'M');
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if (!$productId) {
        jsonResponse(['success' => false, 'error' => 'Product ID is required'], 400);
    }

    // Verify product exists and get price
    $stmtP = $pdo->prepare("SELECT id, title, price, stock_quantity FROM products WHERE id = ?");
    $stmtP->execute([$productId]);
    $product = $stmtP->fetch();

    if (!$product) {
        jsonResponse(['success' => false, 'error' => 'Product not found'], 404);
    }

    // Check if matching item already exists in cart for this session
    $stmtExist = $pdo->prepare("
        SELECT id, quantity FROM cart_items 
        WHERE session_id = ? AND product_id = ? AND color_name = ? AND size_code = ?
    ");
    $stmtExist->execute([$sessionId, $productId, $colorName, $sizeCode]);
    $existing = $stmtExist->fetch();

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        $stmtUp = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmtUp->execute([$newQty, $existing['id']]);
    } else {
        $stmtIns = $pdo->prepare("
            INSERT INTO cart_items (session_id, product_id, color_name, size_code, quantity, price)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([$sessionId, $productId, $colorName, $sizeCode, $quantity, $product['price']]);
    }

    $cartCount = getCartCount();
    $cartSubtotal = getCartSubtotal();

    jsonResponse([
        'success' => true,
        'message' => "{$product['title']} added to your bag",
        'cart_count' => $cartCount,
        'cart_subtotal' => $cartSubtotal
    ]);
}

// -------------------------------------------------------------
// UPDATE QUANTITY
// -------------------------------------------------------------
if ($action === 'update') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($quantity <= 0) {
        $stmtDel = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
        $stmtDel->execute([$itemId, $sessionId]);
    } else {
        $stmtUp = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND session_id = ?");
        $stmtUp->execute([$quantity, $itemId, $sessionId]);
    }

    jsonResponse([
        'success' => true,
        'cart_count' => getCartCount(),
        'cart_subtotal' => getCartSubtotal()
    ]);
}

// -------------------------------------------------------------
// REMOVE ITEM
// -------------------------------------------------------------
if ($action === 'remove') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $stmtDel = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
    $stmtDel->execute([$itemId, $sessionId]);

    jsonResponse([
        'success' => true,
        'message' => 'Item removed from bag',
        'cart_count' => getCartCount(),
        'cart_subtotal' => getCartSubtotal()
    ]);
}

// -------------------------------------------------------------
// APPLY COUPON
// -------------------------------------------------------------
if ($action === 'apply_coupon') {
    $code = strtoupper(trim((string)($_POST['code'] ?? '')));
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        jsonResponse(['success' => false, 'error' => 'Invalid or expired promotional code'], 400);
    }

    $subtotal = getCartSubtotal();
    if ($subtotal < $coupon['min_spend']) {
        $shortfall = $coupon['min_spend'] - $subtotal;
        jsonResponse([
            'success' => false, 
            'error' => "Code requires minimum spend of " . formatINR($coupon['min_spend']) . " (Add " . formatINR($shortfall) . " more)"
        ], 400);
    }

    $_SESSION['velora_applied_coupon'] = $coupon['code'];

    $discount = ($coupon['discount_type'] === 'percentage') 
        ? ($subtotal * $coupon['discount_value']) / 100 
        : min($subtotal, (float)$coupon['discount_value']);

    jsonResponse([
        'success' => true,
        'message' => "Voucher {$coupon['code']} applied successfully!",
        'coupon_code' => $coupon['code'],
        'discount_amount' => $discount
    ]);
}

jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
