<?php

namespace App\Services;

use App\Models\IncomeTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class IncomeLedger
{
    public function recordPaystack(array $transaction, string $sourceType, int $sourceId, string $description): IncomeTransaction
    {
        $reference = $transaction['reference'] ?? null;
        $amount = filter_var($transaction['amount'] ?? null, FILTER_VALIDATE_INT);
        $currency = strtoupper((string) ($transaction['currency'] ?? ''));

        if (($transaction['status'] ?? null) !== 'success'
            || ! is_string($reference)
            || $reference === ''
            || $amount === false
            || $amount <= 0
            || ! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('Paystack returned an incomplete or unsuccessful income transaction.');
        }

        $customer = $transaction['customer'] ?? [];
        $payerName = is_array($customer)
            ? trim(implode(' ', array_filter([
                $customer['first_name'] ?? null,
                $customer['last_name'] ?? null,
            ], 'is_string')))
            : '';

        $receivedAt = $this->transactionDate($transaction['paid_at'] ?? $transaction['created_at'] ?? null);

        return DB::transaction(fn (): IncomeTransaction => IncomeTransaction::query()->updateOrCreate(
            ['gateway_reference' => $reference],
            [
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'description' => $description,
                'payer_name' => $payerName !== '' ? Str::limit($payerName, 160, '') : null,
                'amount' => $amount / 100,
                'currency' => $currency,
                'method' => 'paystack',
                'reference' => $reference,
                'received_at' => $receivedAt,
                'recorded_by' => null,
            ],
        ));
    }

    private function transactionDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
