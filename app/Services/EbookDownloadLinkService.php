<?php

namespace App\Services;

use App\Enums\EbookOrderStatus;
use App\Models\EbookDownload;
use App\Models\EbookOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EbookDownloadLinkService
{
    public function rotateForOrder(int $orderId): ?int
    {
        return DB::transaction(function () use ($orderId): ?int {
            $order = EbookOrder::query()->whereKey($orderId)->lockForUpdate()->first();
            if (! $order || $order->status !== EbookOrderStatus::Paid || ! $order->fulfilled_at || ! $order->fulfilled_pdf_path
                || ! Storage::disk('local')->exists($order->fulfilled_pdf_path)) {
                return null;
            }

            $download = EbookDownload::query()->where('order_id', $order->id)->lockForUpdate()->first();
            if (! $download) {
                return null;
            }

            $download->token_generation = ((int) $download->token_generation) + 1;
            $download->token_hash = hash('sha256', app(EbookDownloadToken::class)->generate($order->reference, $download->token_generation));
            $download->expires_at = now()->addHours(config('ebooks.fulfilment.download_ttl_hours'));
            $download->max_downloads = config('ebooks.fulfilment.max_downloads');
            $download->download_count = 0;
            $download->last_downloaded_at = null;
            $download->save();

            return $download->token_generation;
        });
    }
}
