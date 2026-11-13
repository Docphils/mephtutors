<?php

namespace Tests\Feature;

use App\Enums\EbookOrderStatus;
use App\Enums\EbookStatus;
use App\Models\Ebook;
use App\Models\EbookDownload;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use App\Models\User;
use App\Services\EbookOrderLifecycle;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EbookDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_amount_and_currency_are_snapshotted_and_related_to_catalog(): void
    {
        $ebook = Ebook::factory()->create(['price_kobo' => 345000, 'currency' => 'NGN']);
        $order = EbookOrder::factory()->create([
            'ebook_id' => $ebook->id,
            'amount_kobo' => 345000,
            'currency' => 'NGN',
        ]);

        $ebook->update(['price_kobo' => 500000]);

        $this->assertSame(345000, $order->fresh()->amount_kobo);
        $this->assertSame('NGN', $order->fresh()->currency);
        $this->assertSame($ebook->id, $order->ebook->id);
    }

    public function test_download_hash_is_unique_and_event_does_not_store_ip_address(): void
    {
        $ebook = Ebook::factory()->create();
        $order = EbookOrder::factory()->create(['ebook_id' => $ebook->id]);
        EbookDownload::query()->create([
            'order_id' => $order->id,
            'token_hash' => hash('sha256', 'one-time-secret'),
            'expires_at' => now()->addHours(72),
            'max_downloads' => 5,
            'download_count' => 0,
        ]);

        $this->assertDatabaseMissing('ebook_downloads', ['token_hash' => 'one-time-secret']);

        EbookEvent::query()->create([
            'type' => 'landing_view',
            'ebook_id' => $ebook->id,
            'meta' => ['device' => 'mobile'],
        ]);

        $this->assertDatabaseHas('ebook_events', ['type' => 'landing_view', 'ebook_id' => $ebook->id]);
        $this->assertArrayNotHasKey('ip_address', EbookEvent::query()->firstOrFail()->getAttributes());
    }

    public function test_catalog_and_order_statuses_cast_to_enums_and_orders_prevent_catalog_deletion(): void
    {
        $ebook = Ebook::factory()->create(['status' => EbookStatus::Draft]);
        $order = EbookOrder::factory()->create(['ebook_id' => $ebook->id, 'status' => EbookOrderStatus::Pending]);

        $this->assertSame(EbookStatus::Draft, $ebook->fresh()->status);
        $this->assertSame(EbookOrderStatus::Pending, $order->fresh()->status);

        try {
            $ebook->delete();
            $this->fail('An eBook with existing orders must be retained.');
        } catch (QueryException) {
            $this->assertDatabaseHas('ebooks', ['id' => $ebook->id]);
        }
    }

    public function test_ebook_admin_route_rejects_guests_and_non_admin_users(): void
    {
        $this->get(route('admin.ebooks.index'))->assertRedirect(route('login'));

        $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
        $this->actingAs($client)->get(route('admin.ebooks.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin)->get(route('admin.ebooks.index'))->assertOk();
    }

    public function test_order_lifecycle_service_is_idempotent_and_blocks_invalid_transitions(): void
    {
        $order = EbookOrder::factory()->create(['status' => EbookOrderStatus::Pending]);
        $lifecycle = app(EbookOrderLifecycle::class);

        $paid = $lifecycle->transition($order, EbookOrderStatus::Paid);
        $paidAt = $paid->paid_at;
        $again = $lifecycle->transition($paid, EbookOrderStatus::Paid);

        $this->assertSame(EbookOrderStatus::Paid, $again->status);
        $this->assertTrue($again->paid_at->equalTo($paidAt));

        $this->expectException(\LogicException::class);
        $lifecycle->transition($again, EbookOrderStatus::Failed);
    }

    public function test_published_ebook_pages_are_public_and_drafts_are_not_exposed(): void
    {
        $draft = Ebook::factory()->create(['slug' => 'draft-book']);
        $published = Ebook::factory()->create([
            'slug' => 'public-book',
            'status' => EbookStatus::Published,
            'free_chapter_content' => "Chapter text\nSecond paragraph",
            'meta_title' => 'Public Book | MephEd',
            'meta_description' => 'Owner supplied book summary.',
        ]);

        $this->get(route('ebooks.show', ['slug' => $draft->slug]))->assertNotFound();
        $this->withHeader('Referer', 'https://search.example/result?email=private@example.com')
            ->get(route('ebooks.show', ['slug' => $published->slug]).'?utm_source=search')
            ->assertOk()
            ->assertSee($published->title)
            ->assertSee('Public Book | MephEd')
            ->assertSee('name="robots" content="index,follow"', false);
        $this->get(route('ebooks.free-chapter', ['slug' => $published->slug]))
            ->assertOk()
            ->assertSee('Chapter text')
            ->assertSee('Second paragraph');

        $event = EbookEvent::query()->where('type', 'landing_view')->firstOrFail();
        $this->assertSame('https://search.example/result', $event->referrer);
        $this->assertSame('search', $event->utm_source);
        $this->assertStringNotContainsString('private@example.com', $event->referrer);

        $this->assertGuest();
    }

    public function test_free_chapter_pdf_is_inline_and_has_an_attachment_download_option(): void
    {
        Storage::fake('local');
        $path = 'ebooks/free-chapters/sample.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 test document');
        $ebook = Ebook::factory()->create([
            'slug' => 'pdf-sample-book',
            'title' => 'PDF Sample Book',
            'status' => EbookStatus::Published,
            'free_chapter_path' => $path,
        ]);

        $this->get(route('ebooks.free-chapter', ['slug' => $ebook->slug]))
            ->assertOk()
            ->assertSee(route('ebooks.free-chapter.pdf', ['slug' => $ebook->slug], false), false)
            ->assertSee(route('ebooks.free-chapter.pdf', ['slug' => $ebook->slug, 'download' => 1], false), false)
            ->assertSee('If the PDF preview below appears blank', false);

        $this->get(route('ebooks.free-chapter.pdf', ['slug' => $ebook->slug]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename=pdf-sample-book-free-chapter.pdf');

        $this->get(route('ebooks.free-chapter.pdf', ['slug' => $ebook->slug, 'download' => 1]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename=pdf-sample-book-free-chapter.pdf');

        $this->assertDatabaseHas('ebook_events', ['type' => 'free_chapter_view', 'ebook_id' => $ebook->id]);
        $this->assertDatabaseHas('ebook_events', ['type' => 'free_chapter_download', 'ebook_id' => $ebook->id]);
    }

    public function test_sitemap_lists_public_ebook_pages_and_omits_drafts(): void
    {
        Ebook::factory()->create(['slug' => 'hidden-book']);
        Ebook::factory()->create([
            'slug' => 'listed-book',
            'status' => EbookStatus::Published,
            'free_chapter_content' => 'Sample chapter',
        ]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('ebooks.show', ['slug' => 'listed-book']), false)
            ->assertSee(route('ebooks.free-chapter', ['slug' => 'listed-book']), false)
            ->assertDontSee(route('ebooks.show', ['slug' => 'hidden-book']), false);
    }
}
