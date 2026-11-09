<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('enrollees')
            ->join('cohorts', 'cohorts.id', '=', 'enrollees.cohort_id')
            ->where('enrollees.meta->payment_status', 'paid')
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
                'enrollees.id',
                'enrollees.name',
                'enrollees.meta',
                'enrollees.confirmed_at',
                'enrollees.created_at',
                'cohorts.name as cohort_name',
                'cohorts.fee',
            ])
            ->chunk(500, function ($enrollees): void {
                foreach ($enrollees as $enrollee) {
                    $meta = is_array($enrollee->meta)
                        ? $enrollee->meta
                        : json_decode((string) $enrollee->meta, true);
                    $reference = is_array($meta) && is_string($meta['payment_reference'] ?? null)
                        ? $meta['payment_reference']
                        : null;

                    DB::table('income_transactions')->insertOrIgnore([
                        'source_type' => 'enrollee',
                        'source_id' => $enrollee->id,
                        'description' => 'Bootcamp enrollment · '.$enrollee->cohort_name,
                        'payer_name' => $enrollee->name,
                        'amount' => $enrollee->fee,
                        'currency' => 'NGN',
                        'method' => $reference ? 'paystack' : 'unrecorded',
                        'reference' => $reference,
                        'gateway_reference' => $reference,
                        'received_at' => $enrollee->confirmed_at,
                        'notes' => 'Date is the enrollment confirmation time. Amount uses the cohort fee currently stored; no payment-time amount snapshot is available.',
                        'created_at' => $enrollee->created_at,
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void {}
};
