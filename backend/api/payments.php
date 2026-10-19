<?php
require_once __DIR__ . '/../lib/api.php';
$adminId = kprApiAuth($_SERVER['REQUEST_METHOD'] !== 'GET');
$method = $_SERVER['REQUEST_METHOD'];
$paymentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$clientId = filter_input(INPUT_GET, 'client_id', FILTER_VALIDATE_INT);
try {
    if ($method === 'GET') {
        if (!$clientId || $clientId < 1) kprJson(400, ['success' => false, 'message' => 'Invalid client ID.']);
        $stmt = $pdo->prepare('SELECT id, amount, payment_date, created_at FROM payments WHERE client_id = :id ORDER BY payment_date DESC, id DESC');
        $stmt->execute(['id' => $clientId]);
        kprJson(200, ['success' => true, 'payments' => $stmt->fetchAll()]);
    }
    if (!in_array($method, ['POST', 'PUT', 'DELETE'], true)) kprJson(405, ['success' => false, 'message' => 'Method not allowed.']);
    $data = kprJsonInput();
    $pdo->beginTransaction();
    if ($method === 'POST') {
        $clientId = filter_var($data['client_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$clientId || $clientId < 1) throw new InvalidArgumentException('Invalid client ID.');
        $lock = $pdo->prepare('SELECT id FROM clients WHERE id = :id FOR UPDATE'); $lock->execute(['id' => $clientId]);
        if (!$lock->fetchColumn()) { $pdo->rollBack(); kprJson(404, ['success' => false, 'message' => 'Client not found.']); }
        $amount = kprCentsString(kprMoneyCents($data['amount'] ?? null, true));
        $date = $data['payment_date'] ?? null;
        if (!kprValidDate($date)) throw new InvalidArgumentException('Invalid payment date.');
        $stmt = $pdo->prepare('INSERT INTO payments (client_id, amount, payment_date) VALUES (:client_id, :amount, :payment_date)');
        $stmt->execute(['client_id' => $clientId, 'amount' => $amount, 'payment_date' => $date]);
        $paymentId = (int) $pdo->lastInsertId();
        $audit = $pdo->prepare("INSERT INTO payment_audit (payment_id, client_id, action, new_amount, new_payment_date, admin_id, reason) VALUES (:payment_id, :client_id, 'create', :amount, :payment_date, :admin_id, NULL)");
        $audit->execute(['payment_id' => $paymentId, 'client_id' => $clientId, 'amount' => $amount, 'payment_date' => $date, 'admin_id' => $adminId]);
    } else {
        if (!$paymentId || $paymentId < 1) throw new InvalidArgumentException('Invalid payment ID.');
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = :id FOR UPDATE'); $stmt->execute(['id' => $paymentId]);
        $old = $stmt->fetch();
        if (!$old) { $pdo->rollBack(); kprJson(404, ['success' => false, 'message' => 'Payment not found.']); }
        $clientId = (int) $old['client_id'];
        $lock = $pdo->prepare('SELECT id FROM clients WHERE id = :id FOR UPDATE'); $lock->execute(['id' => $clientId]);
        if ($method === 'DELETE') {
            $reason = $data['reason'] ?? null;
            if (!is_string($reason) || trim($reason) === '' || strlen($reason) > 500) throw new InvalidArgumentException('A reason is required.');
            $audit = $pdo->prepare("INSERT INTO payment_audit (payment_id, client_id, action, old_amount, old_payment_date, admin_id, reason) VALUES (:payment_id, :client_id, 'delete', :amount, :payment_date, :admin_id, :reason)");
            $audit->execute(['payment_id' => $paymentId, 'client_id' => $clientId, 'amount' => $old['amount'], 'payment_date' => $old['payment_date'], 'admin_id' => $adminId, 'reason' => trim($reason)]);
            $del = $pdo->prepare('DELETE FROM payments WHERE id = :id'); $del->execute(['id' => $paymentId]);
        } else {
            $reason = $data['reason'] ?? null;
            if (!is_string($reason) || trim($reason) === '' || strlen($reason) > 500) throw new InvalidArgumentException('A reason is required.');
            $amount = kprCentsString(kprMoneyCents($data['amount'] ?? null, true));
            $date = $data['payment_date'] ?? null;
            if (!kprValidDate($date)) throw new InvalidArgumentException('Invalid payment date.');
            $upd = $pdo->prepare('UPDATE payments SET amount = :amount, payment_date = :payment_date WHERE id = :id');
            $upd->execute(['amount' => $amount, 'payment_date' => $date, 'id' => $paymentId]);
            $audit = $pdo->prepare("INSERT INTO payment_audit (payment_id, client_id, action, old_amount, old_payment_date, new_amount, new_payment_date, admin_id, reason) VALUES (:payment_id, :client_id, 'edit', :old_amount, :old_date, :new_amount, :new_date, :admin_id, :reason)");
            $audit->execute(['payment_id' => $paymentId, 'client_id' => $clientId, 'old_amount' => $old['amount'], 'old_date' => $old['payment_date'], 'new_amount' => $amount, 'new_date' => $date, 'admin_id' => $adminId, 'reason' => trim($reason)]);
        }
    }
    $status = kprRefreshPaymentStatus($pdo, (int) $clientId);
    $pdo->commit();
    kprJson(200, ['success' => true, 'payment_status' => $status]);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack(); kprJson(422, ['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack(); error_log($e->getMessage()); kprJson(500, ['success' => false, 'message' => 'Server error.']);
}
