<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Recover older enrolments that were confirmed before income tracking existed.
     * Inspecting JSON in PHP keeps this backfill compatible with the production
     * database versions used by the application.
     */
    public function up(): void
    {
        DB::table('enrollees')
            ->join('cohorts', 'cohorts.id', '=', 'enrollees.cohort_id')
            ->whereNotNull('cohorts.fee')
            ->where('cohorts.fee', '>', 0)
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('income_transactions')
                    ->where('income_transactions.source_type', 'enrollee')
                    ->whereColumn('income_transactions.source_id', '=', 'enrollees.id');
            })
            ->orderBy('enrollees.id')
            ->select([
                'enrollees.id as enrollee_id',
                'enrollees.name',
                'enrollees.status',
                'enrollees.meta',
                'enrollees.confirmed_at',
                'enrollees.created_at',
                'cohorts.name as cohort_name',
                'cohorts.fee',
            ])
            ->chunkById(500, function ($enrollees): void {
                foreach ($enrollees as $enrollee) {
                    $meta = is_array($enrollee->meta)
                        ? $enrollee->meta
                        : json_decode((string) $enrollee->meta, true);
                    $meta = is_array($meta) ? $meta : [];
                    $paymentStatus = strtolower(trim((string) ($meta['payment_status'] ?? '')));
                    $paidMarkerPresent = $paymentStatus === 'paid';
                    $confirmedLifecycle = in_array($enrollee->status, ['confirmed', 'active', 'completed'], true);

                    if (! $paidMarkerPresent && ! $confirmedLifecycle) {
                        continue;
                    }

                    $reference = $paidMarkerPresent && is_string($meta['payment_reference'] ?? null)
                        ? trim($meta['payment_reference'])
                        : '';
                    $verifiedPaystackReceipt = $paidMarkerPresent && $reference !== '';
                    $fallbackNote = 'Imported from the enrollee lifecycle because legacy payment metadata was unavailable. Payment method and transaction-time amount could not be verified; amount uses the cohort fee currently stored.';

                    DB::table('income_transactions')->insertOrIgnore([
                        'source_type' => 'enrollee',
                        'source_id' => $enrollee->enrollee_id,
                        'description' => 'Bootcamp enrollment · '.$enrollee->cohort_name,
                        'payer_name' => $enrollee->name,
                        'amount' => $enrollee->fee,
                        'currency' => 'NGN',
                        'method' => $verifiedPaystackReceipt ? 'paystack' : 'unrecorded',
                        'reference' => $verifiedPaystackReceipt ? $reference : null,
                        'gateway_reference' => $verifiedPaystackReceipt ? $reference : null,
                        'received_at' => $enrollee->confirmed_at ?: $enrollee->created_at,
                        'notes' => $verifiedPaystackReceipt
                            ? 'Paystack payment is marked paid in the enrollee metadata. Amount uses the cohort fee currently stored; no payment-time amount snapshot is available.'
                            : $fallbackNote,
                        'created_at' => $enrollee->created_at,
                        'updated_at' => now(),
                    ]);
                }
            }, 'enrollees.id', 'enrollee_id');
    }

    public function down(): void
    {
        // Historical income records are financial records and are intentionally retained.
    }
};
