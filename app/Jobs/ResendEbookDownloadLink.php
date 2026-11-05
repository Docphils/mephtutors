<?php

namespace App\Jobs;

use App\Enums\EbookOrderStatus;
use App\Mail\EbookPurchaseReceipt;
use App\Models\EbookDownload;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use App\Services\EbookAlertService;
use App\Services\EbookDownloadToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ResendEbookDownloadLink implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(public int $orderId, public int $generation) {}

    public function uniqueId(): string
    {
        return 'resend-'.$this->orderId.'-'.$this->generation;
    }

    public function handle(EbookDownloadToken $tokens): void
    {
        [$order, $token] = DB::transaction(function () use ($tokens): array {
            $order = EbookOrder::query()->with('ebook')->whereKey($this->orderId)->lockForUpdate()->firstOrFail();
            if ($order->status !== EbookOrderStatus::Paid || ! $order->fulfilled_at || ! $order->fulfilled_pdf_path) {
                throw new \RuntimeException('The eBook order is not ready for link resend.');
            }

            $download = EbookDownload::query()->where('order_id', $order->id)->lockForUpdate()->firstOrFail();
            if ($download->token_generation !== $this->generation) {
                return [$order, null];
            }

            return [$order, $tokens->generate($order->reference, $this->generation)];
        });

        if (! $token) {
            return;
        }

        Mail::to($order->buyer_email)->send(new EbookPurchaseReceipt($order, route('ebooks.download', ['token' => $token])));
        EbookEvent::query()->create([
            'type' => 'link_resent',
            'ebook_id' => $order->ebook_id,
            'order_id' => $order->id,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        EbookEvent::query()->firstOrCreate(
            ['type' => 'link_resend_failed', 'order_id' => $this->orderId],
            ['ebook_id' => EbookOrder::query()->whereKey($this->orderId)->value('ebook_id'), 'created_at' => now(), 'meta' => ['stage' => 'job_exhausted']],
        );
        app(EbookAlertService::class)->sendOnce(
            'link-resend-failed-'.$this->orderId,
            'eBook download link resend failed',
            "The download link resend for eBook order ID {$this->orderId} failed after retries. Check the mail transport and order record.",
        );
    }
}
