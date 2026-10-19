<?php
require_once __DIR__ . '/../lib/api.php';
$adminId = kprApiAuth($_SERVER['REQUEST_METHOD'] !== 'GET');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1)
    kprJson(400, ['success' => false, 'message' => 'Invalid client ID.']);
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $client = $stmt->fetch();
        if (!$client)
            kprJson(404, ['success' => false, 'message' => 'Client not found.']);
        require_once __DIR__ . '/../lib/business-rules.php';
        try {
            $cents = kprObligationCents(kprMoneyCents((string) $client['deposit']), kprMoneyCents((string) $client['monthly_fee']), (string) $client['discount_percent'], kprContractMonths($client['contract_start'], $client['contract_end']));
            $calculated = kprCentsString($cents);
        } catch (Throwable $ignored) { $calculated = null; }
        $sum = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE client_id = :id AND amount > 0');
        $sum->execute(['id' => $id]);
        $client['calculated_obligation'] = $calculated;
        $client['final_obligation'] = $client['final_obligation_override'] ?? $calculated;
        $client['total_paid'] = (string) $sum->fetchColumn();
        kprJson(200, ['success' => true, 'client' => $client]);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST')
        kprJson(405, ['success' => false, 'message' => 'Method not allowed.']);
    $data = kprClientInput(kprJsonInput());
    $pdo->beginTransaction();
    $sets = array_map(static fn($key) => "$key = :$key", array_keys($data));
    $data['id'] = $id;
    $stmt = $pdo->prepare('UPDATE clients SET ' . implode(', ', $sets) . ' WHERE id = :id');
    $stmt->execute($data);
    if (!$stmt->rowCount()) {
        $check = $pdo->prepare('SELECT id FROM clients WHERE id = :id');
        $check->execute(['id' => $id]);
        if (!$check->fetchColumn()) {
            $pdo->rollBack();
            kprJson(404, ['success' => false, 'message' => 'Client not found.']);
        }
    }
    kprRefreshPaymentStatus($pdo, $id);
    $pdo->commit();
    kprJson(200, ['success' => true]);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    kprJson(422, ['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    error_log($e->getMessage());
    kprJson(500, ['success' => false, 'message' => 'Server error.']);
}
