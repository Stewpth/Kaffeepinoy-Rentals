<?php
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'path' => '/',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['success' => false, 'message' => 'Invalid request method.']);
}

require_once __DIR__ . '/config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, ['success' => false, 'message' => 'Invalid request.']);
}

$identity = $input['identity'] ?? null;
$password = $input['password'] ?? '';

if (!is_string($identity) || !is_string($password) || trim($identity) === '' || $password === '' || strlen($identity) > 100 || strlen($password) > 4096) {
    respond(400, ['success' => false, 'message' => 'Ilagay ang username at password.']);
}

$bucketHash = hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
try {
    $pdo->exec('DELETE FROM admin_login_attempts WHERE window_started_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');
    $attemptStmt = $pdo->prepare('SELECT failed_attempts, window_started_at FROM admin_login_attempts WHERE bucket_hash = :bucket');
    $attemptStmt->execute(['bucket' => $bucketHash]);
    $attempt = $attemptStmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    respond(500, ['success' => false, 'message' => 'May problema sa server. Subukan muli mamaya.']);
}
if ($attempt && strtotime($attempt['window_started_at']) > time() - 900 && (int) $attempt['failed_attempts'] >= 5) {
    respond(429, ['success' => false, 'message' => 'Too many sign-in attempts. Please try again later.']);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash
         FROM admin_users
         WHERE username = :username AND is_active = 1
         LIMIT 1'
    );

    $stmt->execute(['username' => trim($identity)]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        $recordAttempt = $pdo->prepare("INSERT INTO admin_login_attempts (bucket_hash, failed_attempts, window_started_at) VALUES (:bucket, 1, NOW()) ON DUPLICATE KEY UPDATE failed_attempts = IF(window_started_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE), 1, failed_attempts + 1), window_started_at = IF(window_started_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE), NOW(), window_started_at)");
        $recordAttempt->execute(['bucket' => $bucketHash]);
        respond(401, [
            'success' => false,
            'message' => 'Mali ang username o password.'
        ]);
    }

    $clearAttempts = $pdo->prepare('DELETE FROM admin_login_attempts WHERE bucket_hash = :bucket');
    $clearAttempts->execute(['bucket' => $bucketHash]);

    session_regenerate_id(true);

    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    respond(200, [
        'success' => true,
        'message' => 'Login successful.'
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'May problema sa server. Subukan muli mamaya.'
    ]);
}
