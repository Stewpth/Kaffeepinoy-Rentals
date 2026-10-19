<?php
require_once __DIR__ . '/lib/api.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') kprJson(405, ['success' => false, 'message' => 'Method not allowed.']);
kprApiAuth(true);
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'], 'domain' => $params['domain'], 'secure' => $params['secure'], 'httponly' => $params['httponly'], 'samesite' => 'Lax']);
}
session_destroy();
kprJson(200, ['success' => true]);
