<?php

namespace App\Jobs;

use App\Enums\EbookOrderStatus;
use App\Mail\EbookNewOrderNotice;
use App\Mail\EbookPurchaseReceipt;
use App\Models\EbookDownload;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use App\Models\User;
use App\Services\EbookAlertService;
use App\Services\EbookDownloadToken;
use App\Services\EbookPdfStamper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class FulfillEbookOrder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public int $uniqueFor = 3600;

    public function __construct(public int $orderId) {}

    public function uniqueId(): string
    {
        return (string) $this->orderId;
    }

    public function handle(EbookPdfStamper $stamper, EbookDownloadToken $tokens): void
    {
        $order = EbookOrder::query()->with('ebook', 'download')->find($this->orderId);
        if (! $order || ! $order->ebook || $order->status !== EbookOrderStatus::Paid || $order->fulfilled_at) {
            return;
        }

        $disk = Storage::disk('local');
        $sourcePath = $disk->path($order->ebook->pdf_path);
        $fulfilledPath = $order->fulfilled_pdf_path;

        if (! $fulfilledPath || ! $disk->exists($fulfilledPath)) {
            $tempPath = storage_path('app/tmp/ebook-'.Str::uuid().'.pdf');
            try {
                $stamper->stamp($sourcePath, $tempPath, $order->buyer_email);
                $fulfilledPath = 'ebooks/fulfilled/'.$order->reference.'/'.Str::uuid().'.pdf';
                $stream = fopen($tempPath, 'rb');
                try {
                    if (! $stream || ! $disk->put($fulfilledPath, $stream)) {
                        throw new \RuntimeException('Could not save the stamped eBook to private storage.');
                    }
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            } finally {
                if (is_file($tempPath)) {
                    @unlink($tempPath);
                }
            }
        }

        $now = now();
        $generation = DB::transaction(function () use ($fulfilledPath, $now, $tokens): int {
            $lockedOrder = EbookOrder::query()->whereKey($this->orderId)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== EbookOrderStatus::Paid || $lockedOrder->fulfilled_at) {
                throw new \RuntimeException('The order is no longer eligible for fulfilment.');
            }

            $lockedOrder->fulfilled_pdf_path = $fulfilledPath;
            $lockedOrder->save();
            $download = EbookDownload::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->first();
            if (! $download) {
                $download = new EbookDownload(['order_id' => $lockedOrder->id, 'token_generation' => 1]);
            }
            $generation = (int) ($download->token_generation ?: 1);
            $download->fill([
                'token_hash' => hash('sha256', $tokens->generate($lockedOrder->reference, $generation)),
                'token_generation' => $generation,
                'expires_at' => $now->copy()->addHours(config('ebooks.fulfilment.download_ttl_hours')),
                'max_downloads' => config('ebooks.fulfilment.max_downloads'),
                'download_count' => 0,
                'last_downloaded_at' => null,
            ]);
            $download->created_at ??= $now;
            $download->save();

            return $generation;
        });

        $order->refresh()->load('ebook');
        $rawToken = $tokens->generate($order->reference, $generation);
        $downloadUrl = route('ebooks.download', ['token' => $rawToken]);
        Mail::to($order->buyer_email)->send(new EbookPurchaseReceipt($order, $downloadUrl));

        $notifyAdmins = DB::transaction(function () use ($order): bool {
            $lockedOrder = EbookOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== EbookOrderStatus::Paid || $lockedOrder->fulfilled_at) {
                return false;
            }

            $lockedOrder->fulfilled_at = now();
            $lockedOrder->save();
            $event = EbookEvent::query()->firstOrCreate(
                ['type' => 'fulfilment_succeeded', 'order_id' => $lockedOrder->id],
                ['ebook_id' => $lockedOrder->ebook_id, 'created_at' => now()],
            );

            return $event->wasRecentlyCreated;
        });

        if ($notifyAdmins) {
            try {
                $adminEmails = User::query()->where('role', 'admin')->whereNotNull('email')->pluck('email')->all();
                if ($adminEmails !== []) {
                    Mail::to($adminEmails)->send(new EbookNewOrderNotice($order->fresh()->load('ebook')));
                } else {
                    Log::warning('No eBook admin email recipient exists.', ['order_reference' => $order->reference]);
                    app(EbookAlertService::class)->sendOnce('admin-order-recipient-missing', 'No eBook administrator recipient', 'A paid eBook order was fulfilled, but no administrator account has an email address.');
                }
            } catch (Throwable $exception) {
                Log::error('eBook admin order notice could not be sent.', [
                    'order_reference' => $order->reference,
                    'exception' => $exception,
                ]);
                app(EbookAlertService::class)->sendOnce('admin-order-notice-'.$order->id, 'eBook admin notification failed', "The owner notification for paid order {$order->reference} could not be sent. Check the mail transport and administrator accounts.");
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        $order = EbookOrder::query()->with('ebook')->find($this->orderId);
        if (! $order || $order->status !== EbookOrderStatus::Paid || $order->fulfilled_at) {
            return;
        }

        EbookEvent::query()->firstOrCreate(
            ['type' => 'fulfilment_failed', 'order_id' => $order->id],
            ['ebook_id' => $order->ebook_id, 'created_at' => now(), 'meta' => ['stage' => 'job_exhausted']],
        );
        Log::error('eBook fulfilment job exhausted its retries.', [
            'order_reference' => $order->reference,
            'exception' => $exception,
        ]);

        app(EbookAlertService::class)->sendOnce(
            'fulfilment-failed-'.$order->id,
            'eBook fulfilment failed',
            "Order {$order->reference} is paid but could not be fulfilled after queue retries.",
        );
    }
}
