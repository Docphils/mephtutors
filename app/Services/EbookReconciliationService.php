<?php

namespace App\Services;

use App\Enums\EbookOrderStatus;
use App\Jobs\FulfillEbookOrder;
use App\Models\EbookOrder;
use App\Models\EbookTaskRun;
use Illuminate\Support\Facades\Log;

class EbookReconciliationService
{
    public function run(): array
    {
        $run = EbookTaskRun::query()->firstOrCreate(['task' => 'reconciliation']);
        $run->forceFill(['started_at' => now(), 'status' => 'running', 'last_error' => null])->save();
        $checked = 0;
        $recovered = 0;
        $verificationErrors = 0;
        $lastVerificationError = null;
        $pendingCutoff = now()->subMinutes(config('ebooks.pending_order_check_minutes', 30));

        try {
            EbookOrder::query()->where('status', EbookOrderStatus::Pending)
                ->where('created_at', '<=', $pendingCutoff)->orderBy('id')->chunkById(50, function ($orders) use (&$checked, &$recovered, &$verificationErrors, &$lastVerificationError): void {
                    foreach ($orders as $order) {
                        $checked++;
                        try {
                            $before = $order->status;
                            $result = app(EbookPaymentService::class)->verifyAndRecord($order->paystack_reference, 'reconciliation');
                            if ($before === EbookOrderStatus::Pending && $result['paid']) {
                                $recovered++;
                            }
                        } catch (\Throwable $exception) {
                            $verificationErrors++;
                            $lastVerificationError = mb_substr($exception->getMessage(), 0, 2000);
                            Log::warning('eBook payment reconciliation could not verify an order.', [
                                'order_reference' => $order->reference,
                                'exception' => $exception,
                            ]);
                        }
                    }
                });

            EbookOrder::query()->where('status', EbookOrderStatus::Paid)
                ->whereNull('fulfilled_at')->where('paid_at', '<=', now()->subMinutes(config('ebooks.unfulfilled_alert_minutes', 10)))
                ->orderBy('id')->chunkById(100, function ($orders): void {
                    foreach ($orders as $order) {
                        app(EbookAlertService::class)->sendOnce('unfulfilled-'.$order->id, 'Paid eBook order is not fulfilled', "Order {$order->reference} has been paid but has not been fulfilled after the configured threshold.");
                        FulfillEbookOrder::dispatch($order->id)
                            ->onConnection(config('ebooks.queue_connection'))
                            ->onQueue('ebooks')
                            ->afterCommit();
                    }
                });

            $run->forceFill([
                'finished_at' => now(),
                'status' => $verificationErrors > 0 ? 'partial' : 'succeeded',
                'checked_count' => $checked,
                'recovered_count' => $recovered,
                'last_error' => $lastVerificationError,
            ])->save();
            if ($verificationErrors > 0) {
                app(EbookAlertService::class)->sendOnce('reconciliation-verification-errors', 'eBook payment checks had errors', "Reconciliation could not verify {$verificationErrors} pending order(s). The task will retry during its next scheduled run.");
            }
            Log::info('eBook reconciliation completed.', ['checked' => $checked, 'recovered' => $recovered, 'verification_errors' => $verificationErrors]);
        } catch (\Throwable $exception) {
            $run->forceFill(['finished_at' => now(), 'status' => 'failed', 'checked_count' => $checked, 'recovered_count' => $recovered, 'last_error' => mb_substr($exception->getMessage(), 0, 2000)])->save();
            app(EbookAlertService::class)->sendOnce('reconciliation-failed', 'eBook reconciliation failed', 'The scheduled eBook reconciliation task failed. Check application logs and scheduler status.');
            throw $exception;
        }

        return ['checked' => $checked, 'recovered' => $recovered];
    }
}
