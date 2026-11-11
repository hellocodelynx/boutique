<?php
/**
 * VELORA Luxury Fashion Boutique
 * Common Utility & Session Functions
 */

if (!defined('VELORA_ACCESS')) {
    define('VELORA_ACCESS', true);
}

require_once __DIR__ . '/../config/db.php';

function veloraImageUrl($imagePath) {
    $imagePath = str_replace('\\', '/', trim((string)$imagePath));
    if ($imagePath === '' || !preg_match('~^assets/img/~', $imagePath)) {
        return $imagePath;
    }

    $projectRoot = realpath(__DIR__ . '/..');
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $documentRootPath = $documentRoot !== false ? rtrim($documentRoot, '\\/') : '';
    if ($projectRoot !== false && $documentRootPath !== ''
        && stripos($projectRoot, $documentRootPath) === 0
        && ($projectRoot === $documentRootPath
            || substr($projectRoot, strlen($documentRootPath), 1) === DIRECTORY_SEPARATOR)) {
        $relativeRoot = substr($projectRoot, strlen($documentRootPath));
        $relativeRoot = trim(str_replace('\\', '/', $relativeRoot), '/');
        return '/' . ($relativeRoot !== '' ? $relativeRoot . '/' : '') . $imagePath;
    }

    return $imagePath;
}

function startVeloraSession() {
    if (session_status() === PHP_SESSION_NONE) {
        $savePath = session_save_path();
        $canWrite = false;
        if (!empty($savePath) && is_dir($savePath)) {
            $testFile = rtrim($savePath, '\\/') . '/.velora_sess_test_' . uniqid();
            $canWrite = @file_put_contents($testFile, '1') !== false;
            if ($canWrite) {
                @unlink($testFile);
            }
        }
        if (!$canWrite) {
            $localSessions = __DIR__ . '/../database/sessions';
            if (!is_dir($localSessions)) {
                @mkdir($localSessions, 0777, true);
            }
            session_save_path($localSessions);
        }
        @session_start();
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
