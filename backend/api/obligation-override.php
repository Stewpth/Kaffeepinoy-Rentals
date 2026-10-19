<?php
require_once __DIR__ . '/../lib/api.php';
$adminId = kprApiAuth(true);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') kprJson(405, ['success' => false, 'message' => 'Method not allowed.']);
$id = filter_input(INPUT_GET, 'client_id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) kprJson(400, ['success' => false, 'message' => 'Invalid client ID.']);
try {
    $data = kprJsonInput();
    $amount = kprCentsString(kprMoneyCents($data['final_obligation_override'] ?? null));
    $reason = $data['reason'] ?? null;
    if (!is_string($reason) || trim($reason) === '' || strlen($reason) > 500) throw new InvalidArgumentException('A reason is required.');
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT contract_status FROM clients WHERE id = :id FOR UPDATE'); $stmt->execute(['id' => $id]);
    $status = $stmt->fetchColumn();
    if (!$status) { $pdo->rollBack(); kprJson(404, ['success' => false, 'message' => 'Client not found.']); }
    if ($status !== 'Pulled Out') { $pdo->rollBack(); kprJson(409, ['success' => false, 'message' => 'An obligation override is available only for a pulled-out contract.']); }
    $stmt = $pdo->prepare('UPDATE clients SET final_obligation_override = :amount, override_reason = :reason, override_admin_id = :admin, override_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute(['amount' => $amount, 'reason' => trim($reason), 'admin' => $adminId, 'id' => $id]);
    $audit = $pdo->prepare('INSERT INTO obligation_override_audit (client_id, override_amount, reason, admin_id, admin_username) VALUES (:client, :amount, :reason, :admin, :username)');
    $audit->execute(['client' => $id, 'amount' => $amount, 'reason' => trim($reason), 'admin' => $adminId, 'username' => (string) ($_SESSION['admin_username'] ?? 'KPR Admin Staff')]);
    $paymentStatus = kprRefreshPaymentStatus($pdo, $id);
    $pdo->commit();
    kprJson(200, ['success' => true, 'payment_status' => $paymentStatus]);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack(); kprJson(422, ['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack(); error_log($e->getMessage()); kprJson(500, ['success' => false, 'message' => 'Server error.']);
}
