<?php

namespace App\Livewire\Admin;

use App\Enums\EbookOrderStatus;
use App\Models\EbookDownload;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use App\Models\EbookTaskRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('eBook Operations - MephEd Admin')]
class EbookOperationsDashboard extends Component
{
    public function render()
    {
        Gate::authorize('Admin');
        $now = now();
        $sales = collect([1, 7, 30])->mapWithKeys(function (int $days) use ($now): array {
            $start = $now->copy()->startOfDay()->subDays($days - 1);
            $orders = EbookOrder::query()->where('status', EbookOrderStatus::Paid)->where('paid_at', '>=', $start)->get(['amount_kobo']);

            return [$days => ['count' => $orders->count(), 'revenue' => $orders->sum('amount_kobo'), 'average' => $orders->count() ? (int) round($orders->sum('amount_kobo') / $orders->count()) : 0]];
        });
        $counts = EbookOrder::query()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $pending = EbookOrder::query()->where('status', EbookOrderStatus::Pending)->where('created_at', '<=', $now->copy()->subMinutes(config('ebooks.pending_order_check_minutes', 30)))->count();
        $unfulfilled = EbookOrder::query()->where('status', EbookOrderStatus::Paid)->whereNull('fulfilled_at')->count();
        $webhookRejected = EbookEvent::query()->where('type', 'webhook_rejected')->where('created_at', '>=', $now->copy()->subDays(30))->count();
        $paymentMismatches = EbookEvent::query()->where('type', 'payment_mismatch')->where('created_at', '>=', $now->copy()->subDays(30))->count();
        $lastWebhook = EbookEvent::query()->whereIn('type', ['webhook_received', 'webhook_rejected'])->latest('created_at')->value('created_at');
        $lastRun = EbookTaskRun::query()->where('task', 'reconciliation')->first();
        $funnel = EbookEvent::query()->where('created_at', '>=', $now->copy()->subDays(30))
            ->whereIn('type', ['landing_view', 'checkout_started', 'payment_succeeded'])
            ->selectRaw('ebook_id, type, count(*) as aggregate')->groupBy('ebook_id', 'type')->with('ebook:id,title')->get();
        $downloads = EbookEvent::query()->where('type', 'download')->where('created_at', '>=', $now->copy()->subDays(30))->count();
        $resends = EbookEvent::query()->where('type', 'link_resent')->where('created_at', '>=', $now->copy()->subDays(30))->count();
        $deliveryFailures = EbookEvent::query()->whereIn('type', ['fulfilment_failed', 'link_resend_failed'])->where('created_at', '>=', $now->copy()->subDays(30))->count();
        $expiredLinks = EbookDownload::query()->where(function ($query) use ($now): void {
            $query->where('expires_at', '<=', $now)->orWhereColumn('download_count', '>=', 'max_downloads');
        })->count();
        $queueBacklog = config('ebooks.queue_connection') === 'database' && Schema::hasTable('jobs') ? DB::table('jobs')->count() : null;
        $oldestUnfulfilled = EbookOrder::query()->where('status', EbookOrderStatus::Paid)->whereNull('fulfilled_at')->oldest('paid_at')->value('paid_at');
        $perBook = EbookOrder::query()->where('created_at', '>=', $now->copy()->subDays(30))
            ->selectRaw('ebook_id, count(*) as orders, sum(case when status = ? then 1 else 0 end) as paid_orders, sum(case when status = ? then amount_kobo else 0 end) as revenue', [EbookOrderStatus::Paid->value, EbookOrderStatus::Paid->value])
            ->with('ebook:id,title')->groupBy('ebook_id')->get();
        $traffic = EbookOrder::query()->where('created_at', '>=', $now->copy()->subDays(30))->get(['status', 'referrer', 'utm_source', 'utm_medium'])
            ->groupBy(function (EbookOrder $order): string {
                $source = mb_strtolower((string) $order->utm_source);
                $medium = mb_strtolower((string) $order->utm_medium);
                $host = mb_strtolower((string) parse_url((string) $order->referrer, PHP_URL_HOST));
                if (str_contains($source, 'selar') || str_contains($host, 'selar')) {
                    return 'Selar';
                }
                if (in_array($medium, ['cpc', 'paid', 'ads', 'advertising'], true)) {
                    return 'Ads';
                }
                if (in_array($source, ['facebook', 'instagram', 'tiktok', 'x', 'twitter', 'linkedin'], true) || preg_match('/facebook|instagram|tiktok|twitter|t\.co|linkedin/', $host)) {
                    return 'Social';
                }
                if (preg_match('/google|bing|yahoo|duckduckgo/', $host)) {
                    return 'Organic search';
                }
                if ($source === '' && $host === '') {
                    return 'Direct';
                }

                return 'Other';
            })->map(fn ($orders) => ['orders' => $orders->count(), 'paid' => $orders->where('status', EbookOrderStatus::Paid)->count()]);

        return view('livewire.admin.ebook-operations-dashboard', compact('sales', 'counts', 'pending', 'unfulfilled', 'webhookRejected', 'paymentMismatches', 'lastWebhook', 'lastRun', 'funnel', 'downloads', 'resends', 'oldestUnfulfilled', 'perBook', 'deliveryFailures', 'expiredLinks', 'queueBacklog', 'traffic'));
    }
}
