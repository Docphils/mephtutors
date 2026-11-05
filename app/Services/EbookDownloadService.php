<?php

namespace App\Services;

use App\Enums\EbookOrderStatus;
use App\Models\EbookDownload;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EbookDownloadService
{
    public function reserve(EbookOrder $order, string $method): ?string
    {
        return DB::transaction(function () use ($order, $method): ?string {
            $lockedOrder = EbookOrder::query()->whereKey($order->id)->lockForUpdate()->first();
            if (! $lockedOrder || $lockedOrder->status !== EbookOrderStatus::Paid || ! $lockedOrder->fulfilled_at) {
                return null;
            }

            $download = EbookDownload::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->first();
            if (! $download
                || $download->expires_at->isPast()
                || $download->download_count >= $download->max_downloads
                || ! $lockedOrder->fulfilled_pdf_path
                || ! str_starts_with($lockedOrder->fulfilled_pdf_path, 'ebooks/fulfilled/')) {
                return null;
            }

            $disk = Storage::disk('local');
            if (! $disk->exists($lockedOrder->fulfilled_pdf_path)) {
                return null;
            }

            $download->download_count++;
            $download->last_downloaded_at = now();
            $download->save();
            EbookEvent::query()->create([
                'type' => 'download',
                'ebook_id' => $lockedOrder->ebook_id,
                'order_id' => $lockedOrder->id,
                'meta' => ['method' => $method],
            ]);

            return $lockedOrder->fulfilled_pdf_path;
        });
    }
}
