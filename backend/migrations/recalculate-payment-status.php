<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../lib/api.php';
$ids = $pdo->query('SELECT id FROM clients ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
$updated = 0;
$skipped = 0;
foreach ($ids as $id) {
    try {
        $pdo->beginTransaction();
        kprRefreshPaymentStatus($pdo, (int) $id);
        $pdo->commit();
        $updated++;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $skipped++;
        error_log('Payment status reconciliation skipped one client because obligation data is incomplete or invalid.');
    }
}
echo "Reconciled: $updated; skipped: $skipped.\n";
