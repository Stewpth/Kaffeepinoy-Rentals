<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function kprJson(int $status, array $body): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function kprApiAuth(bool $csrf = false): int
{
    if (session_status() !== PHP_SESSION_ACTIVE)
        session_start();
    if (empty($_SESSION['admin_id']))
        kprJson(401, ['success' => false, 'message' => 'Authentication required.']);
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE id = :id AND is_active = 1');
        $stmt->execute(['id' => (int) $_SESSION['admin_id']]);
        $adminId = $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        kprJson(500, ['success' => false, 'message' => 'Server error.']);
    }
    if (!$adminId) {
        $_SESSION = [];
        session_destroy();
        kprJson(401, ['success' => false, 'message' => 'Authentication required.']);
    }
    if ($csrf) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            kprJson(403, ['success' => false, 'message' => 'Invalid security token.']);
        }
    }
    return (int) $adminId;
}

function kprJsonInput(): array
{
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
        kprJson(415, ['success' => false, 'message' => 'JSON content type required.']);
    }
    $decoded = json_decode(file_get_contents('php://input'), true);
    if (!is_array($decoded))
        kprJson(400, ['success' => false, 'message' => 'Invalid request body.']);
    return $decoded;
}

function kprMoneyCents(mixed $value, bool $positive = false): int
{
    if (!is_string($value) && !is_int($value))
        throw new InvalidArgumentException('Invalid amount.');
    $value = (string) $value;
    if (!preg_match('/^(0|[1-9]\d{0,9})(?:\.(\d{1,2}))?$/', $value))
        throw new InvalidArgumentException('Invalid amount.');
    [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
    $cents = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    if ($positive ? $cents <= 0 : $cents < 0)
        throw new InvalidArgumentException('Invalid amount.');
    return $cents;
}

function kprCentsString(int $cents): string
{
    return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
}

function kprValidDate(mixed $date): bool
{
    if (!is_string($date))
        return false;
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

function kprRefreshPaymentStatus(PDO $pdo, int $clientId): string
{
    require_once __DIR__ . '/business-rules.php';
    $stmt = $pdo->prepare('SELECT deposit, monthly_fee, discount_percent, contract_start, contract_end, final_obligation_override FROM clients WHERE id = :id');
    $stmt->execute(['id' => $clientId]);
    $client = $stmt->fetch();
    if (!$client)
        throw new RuntimeException('Client not found.');
    $obligationCents = $client['final_obligation_override'] !== null
        ? kprMoneyCents((string) $client['final_obligation_override'])
        : kprObligationCents(
            kprMoneyCents((string) $client['deposit']),
            kprMoneyCents((string) $client['monthly_fee']),
            (string) $client['discount_percent'],
            kprContractMonths($client['contract_start'], $client['contract_end'])
        );
    $stmt = $pdo->prepare('SELECT (COALESCE(SUM(amount), 0) >= CAST(:obligation AS DECIMAL(12,2))) AS obligation_met, COUNT(*) AS payment_count FROM payments WHERE client_id = :id AND amount > 0');
    $stmt->execute(['obligation' => kprCentsString($obligationCents), 'id' => $clientId]);
    $payments = $stmt->fetch();
    $totalCents = (int) $payments['obligation_met'] ? $obligationCents : max(0, $obligationCents - 1);
    $status = kprPaymentStatus($totalCents, $obligationCents, (int) $payments['payment_count']);
    $stmt = $pdo->prepare('UPDATE clients SET payment_status = :status WHERE id = :id');
    $stmt->execute(['status' => $status, 'id' => $clientId]);
    return $status;
}

function kprClientInput(array $input): array
{
    $text = static function (string $key, int $max, bool $required = false) use ($input): ?string {
        $value = $input[$key] ?? null;
        if ($value === null || $value === '') {
            if ($required)
                throw new InvalidArgumentException("$key is required.");
            return null;
        }
        if (!is_string($value) || (function_exists('mb_strlen') ? mb_strlen(trim($value), 'UTF-8') : strlen(trim($value))) > $max)
            throw new InvalidArgumentException("Invalid $key.");
        return trim($value);
    };
    $start = $input['contract_start'] ?? null;
    $end = $input['contract_end'] ?? null;
    if (!kprValidDate($start) || !kprValidDate($end) || $end < $start)
        throw new InvalidArgumentException('Contract dates are invalid.');
    $deposit = kprMoneyCents($input['deposit'] ?? null);
    $fee = kprMoneyCents($input['monthly_fee'] ?? null);
    $discount = $input['discount_percent'] ?? '0';
    if (!is_string($discount) && !is_int($discount))
        throw new InvalidArgumentException('Invalid discount percentage.');
    $discount = (string) $discount;
    if (!preg_match('/^(?:100(?:\.0{1,2})?|\d{1,2}(?:\.\d{1,2})?)$/', $discount))
        throw new InvalidArgumentException('Discount must be between 0 and 100.');
    return [
        'name' => $text('name', 150, true),
        'address' => $text('address', 500),
        'job' => $text('job', 150),
        'contract_start' => $start,
        'contract_end' => $end,
        'deposit' => kprCentsString($deposit),
        'monthly_fee' => kprCentsString($fee),
        'discount_type' => $text('discount_type', 100),
        'discount_percent' => $discount,
        'program' => $text('program', 150),
        'item_received' => $text('item_received', 150),
        'unit_id' => $text('unit_id', 100),
        'serial_number' => $text('serial_number', 150),
        'contract_status' => in_array($input['contract_status'] ?? 'Ongoing', ['Ongoing', 'Pulled Out'], true) ? ($input['contract_status'] ?? 'Ongoing') : throw new InvalidArgumentException('Invalid contract status.'),
    ];
}
