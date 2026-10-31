<?php
/**
 * VELORA Luxury Fashion Boutique
 * Common Utility & Session Functions
 */

if (!defined('VELORA_ACCESS')) {
    define('VELORA_ACCESS', true);
}

require_once __DIR__ . '/../config/db.php';

function startVeloraSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['velora_session_id'])) {
        // Use demo_session as starting seed so users immediately see 2 items in cart and 3 in wishlist like the Stitch prototype
        $_SESSION['velora_session_id'] = 'demo_session';
    }
    return $_SESSION['velora_session_id'];
}

function formatINR($amount) {
    return '₹' . number_format((float)$amount, 0, '.', ',');
}

function getSessionId() {
    return startVeloraSession();
}

function getCartCount() {
    $pdo = getDbConnection();
    $sessionId = getSessionId();
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_qty FROM cart_items WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $row = $stmt->fetch();
    return (int)($row['total_qty'] ?? 0);
}

function getCartSubtotal() {
    $pdo = getDbConnection();
    $sessionId = getSessionId();
    $stmt = $pdo->prepare("SELECT SUM(price * quantity) as subtotal FROM cart_items WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $row = $stmt->fetch();
    return (float)($row['subtotal'] ?? 0);
}

function getWishlistCount() {
    $pdo = getDbConnection();
    $sessionId = getSessionId();
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM wishlist WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

function getWishlistProductIds() {
    $pdo = getDbConnection();
    $sessionId = getSessionId();
    $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitize($input) {
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}
