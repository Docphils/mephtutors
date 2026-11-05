<?php

namespace App\Http\Controllers;

use App\Enums\EbookOrderStatus;
use App\Enums\EbookStatus;
use App\Jobs\ResendEbookDownloadLink;
use App\Models\Ebook;
use App\Models\EbookDownload;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use App\Services\EbookDownloadLinkService;
use App\Services\EbookDownloadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EbookFulfillmentController extends Controller
{
    public function freeChapter(Request $request, string $slug): BinaryFileResponse
    {
        $ebook = Ebook::query()->where('slug', $slug)->where('status', EbookStatus::Published)->firstOrFail();
        abort_unless($ebook->free_chapter_path && str_starts_with($ebook->free_chapter_path, 'ebooks/free-chapters/'), 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($ebook->free_chapter_path), 404);

        $path = $disk->path($ebook->free_chapter_path);
        $filename = Str::slug($ebook->title).'-free-chapter.pdf';
        $headers = [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=3600',
        ];

        if ($request->boolean('download')) {
            return response()->download($path, $filename, $headers);
        }

        return response()->file($path, [
            ...$headers,
            'X-Frame-Options' => 'SAMEORIGIN',
        ])->setContentDisposition('inline', $filename);
    }

    public function cheatSheet(string $slug): StreamedResponse
    {
        $ebook = Ebook::query()->where('slug', $slug)->where('status', EbookStatus::Published)->firstOrFail();
        abort_unless($ebook->cheat_sheet_path && str_starts_with($ebook->cheat_sheet_path, 'ebooks/cheat-sheets/'), 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($ebook->cheat_sheet_path), 404);
        EbookEvent::query()->create(['type' => 'free_chapter_download', 'ebook_id' => $ebook->id]);

        return $disk->download($ebook->cheat_sheet_path, Str::slug($ebook->title).'-cheat-sheet.pdf', [
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function thankYouDownload(string $reference, EbookDownloadService $downloads): StreamedResponse|RedirectResponse
    {
        $order = EbookOrder::query()->where('reference', $reference)->firstOrFail();
        $path = $downloads->reserve($order, 'thank_you');
        if (! $path) {
            return redirect()->to(URL::temporarySignedRoute(
                'ebooks.thank-you',
                now()->addMinutes(config('ebooks.thank_you_url_minutes')),
                ['reference' => $order->reference],
            ));
        }

        return $this->stream($path, $order);
    }

    public function download(string $token, EbookDownloadService $downloads): StreamedResponse|RedirectResponse
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token), 404);
        $download = EbookDownload::query()->with('order.ebook')->where('token_hash', hash('sha256', $token))->first();
        if (! $download) {
            abort(404);
        }

        $order = $download->order;
        if (! $order) {
            abort(404);
        }
        $path = $downloads->reserve($order, 'email_link');
        if (! $path) {
            return redirect()->route('ebooks.download.expired', ['token' => $token]);
        }

        return $this->stream($path, $order);
    }

    public function thankYouResend(Request $request, string $reference): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);
        $order = EbookOrder::query()->where('reference', $reference)->firstOrFail();
        $this->requestResend($order, $data['email']);

        return back()->with('resend_message', 'If the details match a paid order, a new link will be sent shortly.');
    }

    public function resend(Request $request, string $token): RedirectResponse
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token), 404);
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);
        $download = EbookDownload::query()->with('order')->where('token_hash', hash('sha256', $token))->first();
        if (! $download) {
            abort(404);
        }

        $order = $download->order;
        if (! $order) {
            abort(404);
        }
        $this->requestResend($order, $data['email']);

        return back()->with('resend_message', 'If the details match a paid order, a new link will be sent shortly.');
    }

    private function requestResend(EbookOrder $order, string $email): void
    {
        $key = 'ebook-resend-order:'.$order->id;
        if (RateLimiter::tooManyAttempts($key, config('ebooks.resend_attempts_per_order_per_hour', 3))) {
            return;
        }
        RateLimiter::hit($key, 3600);

        $provided = hash('sha256', mb_strtolower(trim($email)));
        $expected = hash('sha256', mb_strtolower($order->buyer_email));
        if (hash_equals($expected, $provided) && $order->status === EbookOrderStatus::Paid && $order->fulfilled_at) {
            $generation = app(EbookDownloadLinkService::class)->rotateForOrder($order->id);
            if ($generation !== null) {
                ResendEbookDownloadLink::dispatch($order->id, $generation)
                    ->onConnection(config('ebooks.queue_connection'))
                    ->onQueue('ebooks')
                    ->afterCommit();
            }
        }
    }

    private function stream(string $path, EbookOrder $order): StreamedResponse
    {
        $filename = Str::slug($order->ebook->title).'.pdf';
        Log::info('eBook download streamed.', ['order_reference' => $order->reference]);

        return Storage::disk('local')->download($path, $filename, [
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
