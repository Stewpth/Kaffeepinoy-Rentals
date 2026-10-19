<?php
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

$identity = trim($input['identity'] ?? '');
$password = $input['password'] ?? '';

if ($identity === '' || !is_string($password) || $password === '') {
    respond(400, ['success' => false, 'message' => 'Ilagay ang username at password.']);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash
         FROM admin_users
         WHERE username = :username AND is_active = 1
         LIMIT 1'
    );

    $stmt->execute(['username' => $identity]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        respond(401, [
            'success' => false,
            'message' => 'Mali ang username o password.'
        ]);
    }

    session_regenerate_id(true);

    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];

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
