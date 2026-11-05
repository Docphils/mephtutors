<?php

namespace App\Services;

use App\Enums\EbookOrderStatus;
use App\Enums\EbookStatus;
use App\Jobs\FulfillEbookOrder;
use App\Models\Ebook;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use App\Models\EbookPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class EbookPaymentService
{
    public function __construct(
        private readonly PaystackService $paystack,
        private readonly EbookOrderLifecycle $lifecycle,
        private readonly EbookAlertService $alerts,
    ) {}

    public function startCheckout(Ebook $ebook, array $buyer, array $attribution = []): array
    {
        if (! config('ebooks.sales_enabled')) {
            throw new RuntimeException('eBook checkout is not enabled.');
        }
        if (! EbookPolicy::requiredPoliciesArePublished()) {
            throw new RuntimeException('eBook checkout is unavailable until all required policy pages are published.');
        }

        $order = DB::transaction(function () use ($ebook, $buyer, $attribution): EbookOrder {
            $currentEbook = Ebook::query()->whereKey($ebook->id)->lockForUpdate()->firstOrFail();
            if ($currentEbook->status !== EbookStatus::Published || $currentEbook->currency !== 'NGN' || $currentEbook->price_kobo < 1) {
                throw new RuntimeException('This eBook is not available for purchase.');
            }

            $order = EbookOrder::query()->create([
                'reference' => (string) Str::uuid(),
                'ebook_id' => $currentEbook->id,
                'buyer_name' => $buyer['name'],
                'buyer_email' => mb_strtolower(trim($buyer['email'])),
                'buyer_phone' => $buyer['phone'] ?: null,
                'amount_kobo' => $currentEbook->price_kobo,
                'currency' => $currentEbook->currency,
                'status' => EbookOrderStatus::Pending,
                'paystack_reference' => Str::random(40),
                'referrer' => $attribution['referrer'] ?? null,
                'utm_source' => $attribution['utm_source'] ?? null,
                'utm_medium' => $attribution['utm_medium'] ?? null,
                'utm_campaign' => $attribution['utm_campaign'] ?? null,
            ]);

            EbookEvent::query()->create([
                'type' => 'checkout_started',
                'ebook_id' => $currentEbook->id,
                'order_id' => $order->id,
                'referrer' => $order->referrer,
                'utm_source' => $order->utm_source,
                'utm_medium' => $order->utm_medium,
                'utm_campaign' => $order->utm_campaign,
            ]);

            return $order;
        });

        try {
            return $this->paystack->initializeEbookPayment($order);
        } catch (\Throwable $exception) {
            $this->lifecycle->transition($order, EbookOrderStatus::Failed);
            EbookEvent::query()->create([
                'type' => 'payment_failed',
                'ebook_id' => $ebook->id,
                'order_id' => $order->id,
                'meta' => ['stage' => 'initialization'],
            ]);
            Log::error('eBook payment initialization failed.', [
                'order_reference' => $order->reference,
                'exception' => $exception,
            ]);
            throw new RuntimeException('Checkout is temporarily unavailable. Please try again or contact support.');
        }
    }

    public function verifyAndRecord(string $reference, string $source = 'callback'): array
    {
        $order = EbookOrder::query()->where('paystack_reference', $reference)->first();
        if (! $order) {
            throw new RuntimeException('Payment could not be verified.');
        }

        $response = $this->paystack->verifyPayment($reference);
        if (! is_array($response) || ! ($response['status'] ?? false) || ! is_array($response['data'] ?? null)) {
            throw new RuntimeException('Paystack transaction verification failed.');
        }

        return $this->recordPaystackResult($order, $response['data'], $source);
    }

    public function processWebhook(array $payload): bool
    {
        if (($payload['event'] ?? null) !== 'charge.success') {
            return false;
        }

        $transaction = $payload['data'] ?? null;
        if (! is_array($transaction) || ! is_string($transaction['reference'] ?? null)) {
            return false;
        }

        $order = EbookOrder::query()->where('paystack_reference', $transaction['reference'])->first();
        if (! $order) {
            Log::warning('Signed Paystack eBook webhook has no matching order.', [
                'paystack_reference' => $transaction['reference'],
            ]);

            return false;
        }

        EbookEvent::query()->create([
            'type' => 'webhook_received',
            'ebook_id' => $order->ebook_id,
            'order_id' => $order->id,
            'meta' => ['event' => 'charge.success'],
        ]);

        return $this->recordPaystackResult($order, $transaction, 'webhook')['paid'];
    }

    private function recordPaystackResult(EbookOrder $order, array $transaction, string $source): array
    {
        $referenceMatches = ($transaction['reference'] ?? null) === $order->paystack_reference;
        $metadataMatches = ($transaction['metadata']['order_reference'] ?? null) === $order->reference;
        $amountMatches = filter_var($transaction['amount'] ?? null, FILTER_VALIDATE_INT) !== false
            && (int) $transaction['amount'] === $order->amount_kobo;
        $currencyMatches = strtoupper((string) ($transaction['currency'] ?? '')) === $order->currency;

        if (! $referenceMatches || ! $metadataMatches || ! $amountMatches || ! $currencyMatches) {
            Log::error('Paystack eBook transaction did not match its order.', [
                'order_reference' => $order->reference,
                'source' => $source,
                'reference_matches' => $referenceMatches,
                'metadata_matches' => $metadataMatches,
                'amount_matches' => $amountMatches,
                'currency_matches' => $currencyMatches,
            ]);
            EbookEvent::query()->create([
                'type' => 'payment_mismatch',
                'ebook_id' => $order->ebook_id,
                'order_id' => $order->id,
                'meta' => [
                    'source' => $source,
                    'reference_matches' => $referenceMatches,
                    'metadata_matches' => $metadataMatches,
                    'amount_matches' => $amountMatches,
                    'currency_matches' => $currencyMatches,
                ],
            ]);
            if ($source === 'webhook') {
                EbookEvent::query()->create([
                    'type' => 'webhook_rejected',
                    'ebook_id' => $order->ebook_id,
                    'order_id' => $order->id,
                    'meta' => ['reason' => 'transaction_mismatch'],
                ]);
            }
            $this->alerts->sendOnce('payment-mismatch-'.$order->id, 'Payment verification mismatch', "Paystack returned a transaction that did not match order {$order->reference}. The order was not marked paid.");

            return ['paid' => false, 'status' => $order->fresh()->status->value];
        }

        $status = $transaction['status'] ?? null;
        if ($status === 'success') {
            return DB::transaction(function () use ($order, $source): array {
                $locked = EbookOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($locked->status === EbookOrderStatus::Pending) {
                    $this->lifecycle->transition($locked, EbookOrderStatus::Paid);
                    EbookEvent::query()->firstOrCreate(
                        ['type' => 'payment_succeeded', 'order_id' => $locked->id],
                        ['ebook_id' => $locked->ebook_id, 'created_at' => now()],
                    );
                    FulfillEbookOrder::dispatch($locked->id)
                        ->onConnection(config('ebooks.queue_connection'))
                        ->onQueue('ebooks')
                        ->afterCommit();
                    if ($source === 'reconciliation') {
                        $orderReference = $locked->reference;
                        DB::afterCommit(fn () => $this->alerts->sendOnce('missed-webhook-'.$locked->id, 'Missed Paystack webhook recovered', "Reconciliation verified payment for order {$orderReference}; the order has been queued for fulfillment."));
                    }
                }

                return ['paid' => $locked->fresh()->status === EbookOrderStatus::Paid, 'status' => $locked->fresh()->status->value];
            });
        }

        if (in_array($status, ['failed', 'abandoned'], true)) {
            $fresh = $order->fresh();
            if ($fresh->status === EbookOrderStatus::Pending) {
                $this->lifecycle->transition($fresh, EbookOrderStatus::Failed);
                EbookEvent::query()->firstOrCreate(
                    ['type' => 'payment_failed', 'order_id' => $order->id],
                    ['ebook_id' => $order->ebook_id, 'created_at' => now(), 'meta' => ['stage' => 'verification']],
                );
            }
        }

        return ['paid' => $order->fresh()->status === EbookOrderStatus::Paid, 'status' => $order->fresh()->status->value];
    }
}
