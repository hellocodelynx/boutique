<?php
/**
 * VELORA API - Wishlist Management Endpoint
 */

define('VELORA_ACCESS', true);
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDbConnection();
$sessionId = getSessionId();
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

if ($action === 'get') {
    $stmt = $pdo->prepare("
        SELECT p.*, w.created_at as saved_at
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.session_id = ?
        ORDER BY w.id DESC
    ");
    $stmt->execute([$sessionId]);
    $items = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'wishlist_count' => count($items),
        'products' => $items
    ]);
}

if ($action === 'toggle') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if (!$productId) {
        jsonResponse(['success' => false, 'error' => 'Product ID is required'], 400);
    }

    // Check if in wishlist
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE session_id = ? AND product_id = ?");
    $stmt->execute([$sessionId, $productId]);
    $exists = $stmt->fetch();

    $inWishlist = false;
    if ($exists) {
        $stmtDel = $pdo->prepare("DELETE FROM wishlist WHERE id = ?");
        $stmtDel->execute([$exists['id']]);
        $message = "Removed from your saved atelier pieces";
        $inWishlist = false;
    } else {
        $stmtIns = $pdo->prepare("INSERT INTO wishlist (session_id, product_id) VALUES (?, ?)");
        $stmtIns->execute([$sessionId, $productId]);
        $message = "Saved to your bespoke wishlist";
        $inWishlist = true;
    }

    $wishlistCount = getWishlistCount();

    jsonResponse([
        'success' => true,
        'message' => $message,
        'in_wishlist' => $inWishlist,
        'wishlist_count' => $wishlistCount
    ]);
}

jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
