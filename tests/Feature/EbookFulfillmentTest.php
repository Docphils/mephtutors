<?php

namespace Tests\Feature;

use App\Enums\EbookOrderStatus;
use App\Enums\EbookStatus;
use App\Jobs\FulfillEbookOrder;
use App\Jobs\ResendEbookDownloadLink;
use App\Mail\EbookPurchaseReceipt;
use App\Models\Ebook;
use App\Models\EbookDownload;
use App\Models\EbookOrder;
use App\Services\EbookDownloadToken;
use App\Services\EbookPdfStamper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EbookFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_order_is_stamped_stored_privately_and_delivered_with_only_a_hashed_token(): void
    {
        Storage::fake('local');
        Mail::fake();
        config(['ebooks.fulfilment.download_ttl_hours' => 72, 'ebooks.fulfilment.max_downloads' => 5]);
        $order = $this->paidOrder();
        Storage::disk('local')->put($order->ebook->pdf_path, '%PDF-1.4 source');
        $this->mock(EbookPdfStamper::class)->shouldReceive('stamp')->once()->andReturnUsing(function ($source, $destination, $email): void {
            $this->assertFileExists($source);
            $this->assertSame('buyer@example.com', $email);
            @mkdir(dirname($destination), 0700, true);
            file_put_contents($destination, "%PDF-1.4 stamped {$email}");
        });

        (new FulfillEbookOrder($order->id))->handle(app(EbookPdfStamper::class), app(EbookDownloadToken::class));

        $fresh = $order->fresh();
        $this->assertNotNull($fresh->fulfilled_at);
        $this->assertStringStartsWith('ebooks/fulfilled/'.$order->reference.'/', $fresh->fulfilled_pdf_path);
        Storage::disk('local')->assertExists($fresh->fulfilled_pdf_path);
        $download = EbookDownload::query()->where('order_id', $order->id)->firstOrFail();
        $this->assertSame(64, strlen($download->token_hash));
        $this->assertSame(5, $download->max_downloads);
        $this->assertDatabaseHas('ebook_events', ['type' => 'fulfilment_succeeded', 'order_id' => $order->id]);
        $rawToken = null;
        Mail::assertSent(EbookPurchaseReceipt::class, function ($mail) use ($order, &$rawToken): bool {
            $rawToken = basename(parse_url($mail->downloadUrl, PHP_URL_PATH));

            return $mail->order->id === $order->id && str_contains($mail->downloadUrl, '/ebooks/download/');
        });
        $this->assertIsString($rawToken);
        $this->assertSame(hash('sha256', $rawToken), $download->token_hash);
    }

    public function test_download_requires_paid_fulfilled_order_and_enforces_expiry_and_count_limit(): void
    {
        Storage::fake('local');
        $order = $this->paidOrder(['fulfilled_at' => now(), 'fulfilled_pdf_path' => 'ebooks/fulfilled/ref/file.pdf']);
        Storage::disk('local')->put($order->fulfilled_pdf_path, '%PDF-1.4 stamped buyer@example.com');
        $token = str_repeat('a', 64);
        EbookDownload::query()->create([
            'order_id' => $order->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
            'max_downloads' => 1,
            'download_count' => 0,
        ]);

        $first = $this->get(route('ebooks.download', ['token' => $token]));
        $first->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertHeader('Cache-Control');
        $this->assertStringContainsString('buyer@example.com', $first->streamedContent());
        $this->assertSame(1, $order->download()->firstOrFail()->download_count);

        $this->get(route('ebooks.download', ['token' => $token]))->assertRedirect(route('ebooks.download.expired', ['token' => $token]));
        $this->assertDatabaseHas('ebook_events', ['type' => 'download', 'order_id' => $order->id]);
    }

    public function test_cheat_sheet_download_is_public_only_for_published_books_and_is_instrumented(): void
    {
        Storage::fake('local');
        $ebook = Ebook::factory()->create([
            'status' => EbookStatus::Published,
            'cheat_sheet_path' => 'ebooks/cheat-sheets/guide.pdf',
        ]);
        Storage::disk('local')->put($ebook->cheat_sheet_path, '%PDF-1.4 sample guide');

        $this->get(route('ebooks.cheat-sheet', ['slug' => $ebook->slug]))
            ->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertDatabaseHas('ebook_events', ['type' => 'cheat_sheet_download', 'ebook_id' => $ebook->id]);

        $ebook->update(['status' => EbookStatus::Draft]);
        $this->get(route('ebooks.cheat-sheet', ['slug' => $ebook->slug]))->assertNotFound();
    }

    public function test_resend_is_bound_to_order_email_and_thank_you_page_requires_a_short_lived_signature(): void
    {
        Queue::fake();
        Storage::fake('local');
        $order = $this->paidOrder(['fulfilled_at' => now(), 'fulfilled_pdf_path' => 'ebooks/fulfilled/ref/file.pdf']);
        Storage::disk('local')->put($order->fulfilled_pdf_path, '%PDF-1.4 stamped buyer@example.com');
        $token = str_repeat('b', 64);
        EbookDownload::query()->create([
            'order_id' => $order->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
            'max_downloads' => 5,
            'download_count' => 0,
        ]);

        $this->post(route('ebooks.download.resend', ['token' => $token]), ['email' => 'other@example.com'])
            ->assertSessionHas('resend_message');
        Queue::assertNothingPushed();

        $this->post(route('ebooks.download.resend', ['token' => $token]), ['email' => 'BUYER@example.com'])
            ->assertSessionHas('resend_message');
        Queue::assertPushed(ResendEbookDownloadLink::class, 1);
        $download = EbookDownload::query()->where('order_id', $order->id)->firstOrFail();
        $this->assertSame(2, $download->token_generation);
        $this->assertSame(hash('sha256', app(EbookDownloadToken::class)->generate($order->reference, 2)), $download->token_hash);

        $this->get(route('ebooks.thank-you', ['reference' => $order->reference]))->assertForbidden();
        $signed = URL::temporarySignedRoute('ebooks.thank-you', now()->addMinutes(5), ['reference' => $order->reference]);
        $this->get($signed)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    private function paidOrder(array $attributes = []): EbookOrder
    {
        $ebook = Ebook::factory()->create(['pdf_path' => 'ebooks/source/test.pdf']);

        return EbookOrder::factory()->create(array_merge([
            'ebook_id' => $ebook->id,
            'status' => EbookOrderStatus::Paid,
            'buyer_email' => 'buyer@example.com',
            'paid_at' => now(),
        ], $attributes));
    }
}
