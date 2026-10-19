<?php
require_once __DIR__ . '/../lib/business-rules.php';

$cases = [
    ['2025-01-15', '2025-02-15', 1],
    ['2025-01-15', '2025-03-15', 2],
    ['2025-01-15', '2025-03-14', 2],
    ['2025-01-31', '2025-02-28', 1],
    ['2024-01-31', '2024-02-29', 1],
    ['2025-01-31', '2025-03-01', 2],
];
foreach ($cases as [$start, $end, $expected]) {
    $actual = kprContractMonths($start, $end);
    if ($actual !== $expected) throw new RuntimeException("$start to $end: expected $expected, got $actual");
}

try { kprContractMonths('2025-03-01', '2025-02-28'); throw new RuntimeException('Earlier end date was accepted.'); }
catch (InvalidArgumentException) {}

$cases = [
    [500000, 250000, '0', 12, 3500000],
    [500000, 250000, '100', 12, 500000],
    [0, 101, '50', 1, 51],
];
foreach ($cases as [$deposit, $fee, $discount, $months, $expected]) {
    $actual = kprObligationCents($deposit, $fee, $discount, $months);
    if ($actual !== $expected) throw new RuntimeException('Obligation calculation mismatch.');
}

if (kprPaymentStatus(0, 10000, 0) !== 'Unpaid') throw new RuntimeException('No payments should be Unpaid.');
if (kprPaymentStatus(9999, 10000, 1) !== 'Partially Paid') throw new RuntimeException('Underpayment should be Partially Paid.');
if (kprPaymentStatus(10000, 10000, 1) !== 'Fully Paid') throw new RuntimeException('Exact payment should be Fully Paid.');
if (kprPaymentStatus(10001, 10000, 1) !== 'Fully Paid') throw new RuntimeException('Overpayment should be Fully Paid.');

echo "Business rule tests passed.\n";
