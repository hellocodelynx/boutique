<?php
/**
 * VELORA API - Checkout & Order Processing
 */

define('VELORA_ACCESS', true);
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDbConnection();
$sessionId = getSessionId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Fetch current cart items
$stmt = $pdo->prepare("
    SELECT ci.*, p.title 
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.session_id = ?
");
$stmt->execute([$sessionId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    jsonResponse(['success' => false, 'error' => 'Your shopping bag is empty.'], 400);
}

// Validate Inputs
$name = sanitize($_POST['customer_name'] ?? '');
$email = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitize($_POST['customer_phone'] ?? '');
$address = sanitize($_POST['shipping_address'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$pincode = sanitize($_POST['pincode'] ?? '');
$paymentMethod = sanitize($_POST['payment_method'] ?? 'COD');

if (!$name || !$email || !$phone || !$address || !$city || !$pincode) {
    jsonResponse(['success' => false, 'error' => 'Please fill in all delivery details.'], 400);
}

// Calculate subtotal
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}

// Apply coupon if valid
$discount = 0;
$couponCode = $_SESSION['velora_applied_coupon'] ?? null;
if ($couponCode) {
    $stmtC = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmtC->execute([$couponCode]);
    $coupon = $stmtC->fetch();
    if ($coupon && $subtotal >= $coupon['min_spend']) {
        if ($coupon['discount_type'] === 'percentage') {
            $discount = ($subtotal * $coupon['discount_value']) / 100;
        } else {
            $discount = min($subtotal, (float)$coupon['discount_value']);
        }
    }
}

$shipping = ($subtotal >= 2999) ? 0.00 : 250.00;
$total = max(0, $subtotal - $discount + $shipping);

$orderNumber = 'VEL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

// Transaction: Insert order, order_items, clear cart
try {
    $pdo->beginTransaction();

    $stmtOrder = $pdo->prepare("
        INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, shipping_address, city, pincode, payment_method, subtotal, discount_amount, shipping_fee, total_amount, coupon_code, order_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
    ");
    $stmtOrder->execute([
        $orderNumber, $name, $email, $phone, $address, $city, $pincode, $paymentMethod,
        $subtotal, $discount, $shipping, $total, $couponCode
    ]);
    $orderId = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, product_title, color_name, size_code, quantity, unit_price, total_price)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($cartItems as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $stmtItem->execute([
            $orderId, $item['product_id'], $item['title'], $item['color_name'],
            $item['size_code'], $item['quantity'], $item['price'], $itemTotal
        ]);
    }

    // Clear session cart and coupon
    $stmtClear = $pdo->prepare("DELETE FROM cart_items WHERE session_id = ?");
    $stmtClear->execute([$sessionId]);
    unset($_SESSION['velora_applied_coupon']);

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'order_number' => $orderNumber,
        'total' => $total,
        'message' => 'Thank you for your couture order.'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'error' => 'Failed to process order: ' . $e->getMessage()], 500);
}
