<?php
/**
 * VELORA API - Products Endpoint
 */

define('VELORA_ACCESS', true);
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDbConnection();

// Check if fetching single product by ID
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        jsonResponse(['success' => false, 'error' => 'Product not found'], 404);
    }

    // Fetch images
    $stmtImg = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY display_order ASC");
    $stmtImg->execute([$id]);
    $product['images'] = $stmtImg->fetchAll();

    // Fetch colors
    $stmtCol = $pdo->prepare("SELECT * FROM product_colors WHERE product_id = ?");
    $stmtCol->execute([$id]);
    $product['colors'] = $stmtCol->fetchAll();

    // Fetch sizes
    $stmtSizes = $pdo->prepare("SELECT * FROM product_sizes WHERE product_id = ?");
    $stmtSizes->execute([$id]);
    $product['sizes'] = $stmtSizes->fetchAll();

    // Fetch reviews
    $stmtRev = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
    $stmtRev->execute([$id]);
    $product['reviews'] = $stmtRev->fetchAll();

    // Fetch bundled accessories
    $stmtAcc = $pdo->prepare("SELECT * FROM bundled_accessories WHERE parent_product_id = ?");
    $stmtAcc->execute([$id]);
    $product['accessories'] = $stmtAcc->fetchAll();

    jsonResponse(['success' => true, 'product' => $product]);
}

// Otherwise, list products with filters
$where = [];
$params = [];

if (!empty($_GET['category'])) {
    $where[] = "c.slug = ?";
    $params[] = $_GET['category'];
}

if (!empty($_GET['silhouette'])) {
    $where[] = "p.silhouette = ?";
    $params[] = $_GET['silhouette'];
}

if (!empty($_GET['fabric'])) {
    $where[] = "p.fabric = ?";
    $params[] = $_GET['fabric'];
}

if (!empty($_GET['occasion'])) {
    $where[] = "p.occasion = ?";
    $params[] = $_GET['occasion'];
}

if (!empty($_GET['max_price'])) {
    $where[] = "p.price <= ?";
    $params[] = (float)$_GET['max_price'];
}

if (!empty($_GET['min_price'])) {
    $where[] = "p.price >= ?";
    $params[] = (float)$_GET['min_price'];
}

if (!empty($_GET['search'])) {
    $where[] = "(p.title LIKE ? OR p.subtitle LIKE ? OR p.craftsmanship LIKE ?)";
    $term = '%' . $_GET['search'] . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sorting
$sort = $_GET['sort'] ?? 'featured';
$orderClause = "ORDER BY p.is_featured DESC, p.id ASC";
switch ($sort) {
    case 'newest':
        $orderClause = "ORDER BY p.is_new_arrival DESC, p.created_at DESC";
        break;
    case 'price-asc':
        $orderClause = "ORDER BY p.price ASC";
        break;
    case 'price-desc':
        $orderClause = "ORDER BY p.price DESC";
        break;
    case 'bestselling':
        $orderClause = "ORDER BY p.is_bestseller DESC, p.review_count DESC";
        break;
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 24;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $whereClause
    $orderClause
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Total count
$countSql = "
    SELECT COUNT(*) as total
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $whereClause
";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$total = (int)$stmtCount->fetchColumn();

jsonResponse([
    'success' => true,
    'total' => $total,
    'count' => count($products),
    'products' => $products
]);
