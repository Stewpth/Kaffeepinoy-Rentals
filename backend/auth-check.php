<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'path' => '/',
    ]);
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Kaffepinoy-project/frontend/pages/login.html');
    exit;
}

require_once __DIR__ . '/config/database.php';
try {
    $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE id = :id AND is_active = 1');
    $stmt->execute(['id' => (int) $_SESSION['admin_id']]);
    $activeAdmin = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('May problema sa server. Subukan muli mamaya.');
}
if (!$activeAdmin) {
    $_SESSION = [];
    session_destroy();
    header('Location: /Kaffepinoy-project/frontend/pages/login.html');
    exit;
}
