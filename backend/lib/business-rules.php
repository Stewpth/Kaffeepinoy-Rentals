<?php
declare(strict_types=1);

function kprContractMonths(string $start, string $end): int
{
    $startDate = DateTimeImmutable::createFromFormat('!Y-m-d', $start);
    $endDate = DateTimeImmutable::createFromFormat('!Y-m-d', $end);
    if (!$startDate || !$endDate || $startDate->format('Y-m-d') !== $start || $endDate->format('Y-m-d') !== $end || $endDate < $startDate) {
        throw new InvalidArgumentException('Invalid contract date range.');
    }

    $months = ((int) $endDate->format('Y') - (int) $startDate->format('Y')) * 12
        + (int) $endDate->format('n') - (int) $startDate->format('n');
    // The month difference is the number of anniversary periods begun; any
    // partial period counts as a full month. A same-day contract still spans one.
    return max(1, $months);
}

function kprObligationCents(int $depositCents, int $monthlyFeeCents, string $discountPercent, int $months): int
{
    if ($depositCents < 0 || $monthlyFeeCents < 0 || $months < 1 || !preg_match('/^(?:100(?:\.0{1,2})?|\d{1,2}(?:\.\d{1,2})?)$/', $discountPercent)) {
        throw new InvalidArgumentException('Invalid obligation values.');
    }
    [$whole, $fraction] = array_pad(explode('.', $discountPercent, 2), 2, '0');
    $basisPoints = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    $numerator = $monthlyFeeCents * (10000 - $basisPoints);
    $discountedMonthlyCents = intdiv($numerator + 5000, 10000);
    if ($discountedMonthlyCents > intdiv(999999999999 - $depositCents, $months)) {
        throw new InvalidArgumentException('Final obligation exceeds the supported currency range.');
    }
    return $depositCents + ($discountedMonthlyCents * $months);
}

function kprPaymentStatus(int $validPaymentCents, int $finalObligationCents, int $paymentCount): string
{
    if ($paymentCount === 0)
        return 'Unpaid';
    return $validPaymentCents >= $finalObligationCents ? 'Fully Paid' : 'Partially Paid';
}
