<?php
/**
 * VELORA Luxury Atelier - Admin Authentication & Security
 */

if (!defined('VELORA_ACCESS')) {
    define('VELORA_ACCESS', true);
}

require_once __DIR__ . '/../../includes/functions.php';

function isAdminLoggedIn() {
    startVeloraSession();
    return !empty($_SESSION['velora_admin_id']);
}

function getLoggedInAdmin() {
    startVeloraSession();
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $adminId = $_SESSION['velora_admin_id'];
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, created_at FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        // Fallback session info if DB lookup fails
        return [
            'id' => $_SESSION['velora_admin_id'],
            'username' => $_SESSION['velora_admin_user'] ?? 'admin',
            'email' => $_SESSION['velora_admin_email'] ?? 'admin@velora.com',
            'full_name' => $_SESSION['velora_admin_name'] ?? 'Eleanor Vance',
            'role' => $_SESSION['velora_admin_role'] ?? 'Head Couturier'
        ];
    }
    
    return $admin;
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function loginAdmin($identifier, $password) {
    startVeloraSession();
    if (!is_string($identifier) || !is_string($password) || $identifier === '' || $password === '') {
        return false;
    }

    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['velora_admin_id'] = $admin['id'];
    $_SESSION['velora_admin_user'] = $admin['username'];
    $_SESSION['velora_admin_email'] = $admin['email'];
    $_SESSION['velora_admin_name'] = $admin['full_name'];
    $_SESSION['velora_admin_role'] = $admin['role'];
    return true;
}

function logoutAdmin() {
    startVeloraSession();
    unset($_SESSION['velora_admin_id']);
    unset($_SESSION['velora_admin_user']);
    unset($_SESSION['velora_admin_email']);
    unset($_SESSION['velora_admin_name']);
    unset($_SESSION['velora_admin_role']);
}
