<?php
require_once __DIR__ . '/../lib/api.php';
$adminId = kprApiAuth($_SERVER['REQUEST_METHOD'] !== 'GET');
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = $_GET['q'] ?? '';
        if (!is_string($query)) throw new InvalidArgumentException('Invalid search query.');
        $query = trim($query);
        $contract = $_GET['contract_status'] ?? '';
        $payment = $_GET['payment_status'] ?? '';
        $sql = 'SELECT * FROM clients WHERE (:q0 = \'%%\' OR name LIKE :q1 OR address LIKE :q2 OR unit_id LIKE :q3 OR serial_number LIKE :q4)';
        $needle = '%' . $query . '%';
        $params = ['q0' => $needle, 'q1' => $needle, 'q2' => $needle, 'q3' => $needle, 'q4' => $needle];
        if ($contract !== '') {
            if (!in_array($contract, ['Ongoing', 'Pulled Out'], true))
                throw new InvalidArgumentException('Invalid contract status.');
            $sql .= ' AND contract_status = :contract';
            $params['contract'] = $contract;
        }
        if ($payment !== '') {
            if (!in_array($payment, ['Unpaid', 'Partially Paid', 'Fully Paid'], true))
                throw new InvalidArgumentException('Invalid payment status.');
            $sql .= ' AND payment_status = :payment';
            $params['payment'] = $payment;
        }
        $sql .= ' ORDER BY id DESC LIMIT 500';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        kprJson(200, ['success' => true, 'clients' => $stmt->fetchAll()]);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        kprJson(405, ['success' => false, 'message' => 'Method not allowed.']);
    $data = kprClientInput(kprJsonInput());
    $pdo->beginTransaction();
    $columns = array_keys($data);
    $sql = 'INSERT INTO clients (' . implode(', ', $columns) . ', payment_status) VALUES (:' . implode(', :', $columns) . ', \'Unpaid\')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    $id = (int) $pdo->lastInsertId();
    kprRefreshPaymentStatus($pdo, $id);
    $pdo->commit();
    kprJson(201, ['success' => true, 'id' => $id]);
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
