<?php
require_once __DIR__ . '/../lib/api.php';
kprApiAuth();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') kprJson(405, ['success' => false, 'message' => 'Method not allowed.']);
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_clients, SUM(contract_status = 'Ongoing') AS ongoing_contracts, SUM(payment_status = 'Fully Paid') AS fully_paid_accounts, SUM(contract_status = 'Pulled Out') AS pulled_out_accounts FROM clients");
    $counts = $stmt->fetch();
    foreach ($counts as $key => $value) $counts[$key] = (int) $value;
    $recent = $pdo->query('SELECT * FROM clients ORDER BY id DESC LIMIT 5')->fetchAll();
    kprJson(200, ['success' => true, 'counts' => $counts, 'recent' => $recent]);
} catch (Throwable $e) {
    error_log($e->getMessage()); kprJson(500, ['success' => false, 'message' => 'Server error.']);
}
