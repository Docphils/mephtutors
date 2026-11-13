<?php

namespace Tests\Feature;

use App\Enums\EbookOrderStatus;
use App\Jobs\FulfillEbookOrder;
use App\Jobs\ResendEbookDownloadLink;
use App\Livewire\Admin\EbookOperationsDashboard;
use App\Livewire\Admin\EbookOrdersManager;
use App\Livewire\Admin\EbookPoliciesManager;
use App\Livewire\Admin\EbooksManager;
use App\Models\Ebook;
use App\Models\EbookDownload;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use App\Models\EbookPolicy;
use App\Models\User;
use App\Services\EbookAlertService;
use App\Services\EbookCopywriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class EbookOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconciliation_recovers_a_paid_order_and_queues_fulfillment_once(): void
    {
        Queue::fake();
        config([
            'services.paystack.secret' => 'sk_test_example',
            'ebooks.alert_emails' => [],
        ]);
        $ebook = Ebook::factory()->create();
        $order = EbookOrder::factory()->create([
            'ebook_id' => $ebook->id,
            'status' => EbookOrderStatus::Pending,
            'paystack_reference' => Str::random(40),
            'created_at' => now()->subMinutes(40),
        ]);
        Http::fake(['api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => [
                'reference' => $order->paystack_reference,
                'amount' => $order->amount_kobo,
                'currency' => 'NGN',
                'status' => 'success',
                'metadata' => ['order_reference' => $order->reference],
            ],
        ], 200)]);

        $this->artisan('ebooks:reconcile-payments')->assertSuccessful();

        $this->assertSame(EbookOrderStatus::Paid, $order->fresh()->status);
        $this->assertDatabaseHas('ebook_events', ['type' => 'payment_succeeded', 'order_id' => $order->id]);
        $this->assertSame(1, EbookEvent::query()->where('type', 'payment_succeeded')->where('order_id', $order->id)->count());
        $this->assertDatabaseHas('ebook_task_runs', ['task' => 'reconciliation', 'status' => 'succeeded', 'checked_count' => 1, 'recovered_count' => 1]);
        Queue::assertPushed(FulfillEbookOrder::class, 1);
    }

    public function test_operations_dashboard_is_admin_only_and_health_and_policy_routes_render(): void
    {
        $health = $this->get(route('health'));
        $health->assertOk()->assertExactJson(['status' => 'ok']);
        $this->assertStringContainsString('no-store', $health->headers->get('Cache-Control'));
        $this->get(route('ebooks.policy', ['policy' => 'refund']))
            ->assertOk()
            ->assertSee('MephEd Educational Services Ltd')
            ->assertSee('charged more than once');

        $this->get(route('admin.ebooks.dashboard'))->assertRedirect(route('login'));
        $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
        $this->actingAs($client)->get(route('admin.ebooks.dashboard'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin)->get(route('admin.ebooks.dashboard'))->assertOk();
        Livewire::actingAs($admin)->test(EbookOperationsDashboard::class)->assertSee('eBook operations');
    }

    public function test_operations_dashboard_reports_free_resource_views_and_downloads_for_the_last_30_days(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $ebook = Ebook::factory()->create();

        foreach ([
            ['free_chapter_view', now()],
            ['free_chapter_view', now()],
            ['free_chapter_view', now()->subDays(31)],
            ['free_chapter_download', now()],
            ['cheat_sheet_download', now()],
            ['cheat_sheet_download', now()],
        ] as [$type, $createdAt]) {
            EbookEvent::query()->create([
                'type' => $type,
                'ebook_id' => $ebook->id,
                'created_at' => $createdAt,
            ]);
        }

        $html = Livewire::actingAs($admin)->test(EbookOperationsDashboard::class)
            ->assertSee('Free chapter views')
            ->assertSee('Chapter downloads')
            ->assertSee('Cheat sheet downloads')
            ->html();

        $this->assertMatchesRegularExpression('/<p class="text-xs text-slate-500">Free chapter views<\/p>\s*<p[^>]*>2<\/p>/', $html);
        $this->assertMatchesRegularExpression('/<p class="text-xs text-slate-500">Chapter downloads<\/p>\s*<p[^>]*>1<\/p>/', $html);
        $this->assertMatchesRegularExpression('/<p class="text-xs text-slate-500">Cheat sheet downloads<\/p>\s*<p[^>]*>2<\/p>/', $html);
    }

    public function test_repeated_alert_for_same_failure_is_emailed_once(): void
    {
        config(['ebooks.alert_emails' => ['ops@example.com'], 'ebooks.alert_dedupe_minutes' => 60]);
        Mail::shouldReceive('raw')->once();

        $alerts = app(EbookAlertService::class);
        $alerts->sendOnce('test-fulfilment-failure', 'Fulfilment failed', 'A paid order could not be delivered.');
        $alerts->sendOnce('test-fulfilment-failure', 'Fulfilment failed', 'A paid order could not be delivered.');
    }

    public function test_admin_can_queue_a_fresh_buyer_download_link(): void
    {
        Queue::fake();
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $ebook = Ebook::factory()->create();
        $order = EbookOrder::factory()->create([
            'ebook_id' => $ebook->id,
            'status' => EbookOrderStatus::Paid,
            'fulfilled_at' => now(),
            'fulfilled_pdf_path' => 'ebooks/fulfilled/order/file.pdf',
        ]);
        Storage::disk('local')->put($order->fulfilled_pdf_path, '%PDF-1.4');
        EbookDownload::query()->create([
            'order_id' => $order->id,
            'token_hash' => hash('sha256', 'old-link-token'),
            'token_generation' => 1,
            'expires_at' => now()->subMinute(),
            'max_downloads' => 5,
            'download_count' => 5,
        ]);

        Livewire::actingAs($admin)->test(EbookOrdersManager::class)
            ->call('resendDownloadLink', $order->id)
            ->assertHasNoErrors();

        $this->assertSame(2, $order->download()->firstOrFail()->token_generation);
        Queue::assertPushed(ResendEbookDownloadLink::class, fn ($job) => $job->orderId === $order->id && $job->generation === 2);
    }

    public function test_admin_cannot_publish_until_legal_policies_are_marked_ready(): void
    {
        EbookPolicy::query()->update(['is_published' => false, 'published_at' => null]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Livewire::actingAs($admin)->test(EbooksManager::class)
            ->set('title', 'Make Money with AI in Nigeria')
            ->set('slug', 'make-money-with-ai-nigeria')
            ->set('authorName', 'Owner Author')
            ->set('priceNaira', 5000)
            ->set('metaTitle', 'Make Money with AI in Nigeria')
            ->set('metaDescription', 'Make Money with AI in Nigeria guide')
            ->set('primaryKeyword', 'Make Money with AI in Nigeria')
            ->set('status', 'published')
            ->call('saveEbook')
            ->assertHasErrors(['status']);

        $this->assertDatabaseCount('ebooks', 0);
    }

    public function test_publish_form_displays_validation_errors_for_required_seo_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Livewire::actingAs($admin)->test(EbooksManager::class)
            ->set('title', 'Make Money with AI in Nigeria')
            ->set('authorName', 'Owner Author')
            ->set('priceNaira', 5000)
            ->set('status', 'published')
            ->call('saveEbook')
            ->assertHasErrors(['metaTitle' => 'required', 'metaDescription' => 'required', 'primaryKeyword' => 'required'])
            ->assertSee('Unable to save this eBook:')
            ->assertSee('The meta title field is required.')
            ->assertSee('The meta description field is required.')
            ->assertSee('The primary keyword field is required.');

        $this->assertDatabaseCount('ebooks', 0);
    }

    public function test_admin_can_edit_and_publish_dynamic_ebook_policies(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $refundPolicy = EbookPolicy::query()->where('slug', 'refund')->firstOrFail();

        $this->get(route('ebooks.policy', ['policy' => 'refund']))
            ->assertOk()
            ->assertSee('MephEd Educational Services Ltd');

        Livewire::actingAs($admin)->test(EbookPoliciesManager::class)
            ->set('policies.refund.content', "# Updated refund policy\n\nContact support for help.")
            ->call('savePolicies')
            ->assertHasNoErrors();

        $this->get(route('ebooks.policy', ['policy' => 'refund']))
            ->assertOk()
            ->assertSee('Updated refund policy')
            ->assertSee('Contact support for help.');

        Livewire::actingAs($admin)->test(EbookPoliciesManager::class)
            ->set('policies.refund.is_published', '0')
            ->call('savePolicies')
            ->assertHasNoErrors();

        $this->get(route('ebooks.policy', ['policy' => 'refund']))->assertNotFound();
        $this->assertFalse($refundPolicy->fresh()->is_published);

        Livewire::actingAs($admin)->test(EbookPoliciesManager::class)
            ->set('policies.refund.is_published', '1')
            ->call('savePolicies')
            ->assertHasErrors(['confirmPublication'])
            ->set('confirmPublication', true)
            ->call('savePolicies')
            ->assertHasNoErrors();

        $this->get(route('ebooks.policy', ['policy' => 'refund']))->assertOk();
        $this->assertTrue($refundPolicy->fresh()->is_published);
    }

    public function test_only_admins_can_open_ebook_policy_editor(): void
    {
        $this->get(route('admin.ebooks.policies'))->assertRedirect(route('login'));
        $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);

        $this->actingAs($client)->get(route('admin.ebooks.policies'))->assertForbidden();
    }

    public function test_copywriter_error_from_groq_is_shown_to_admin(): void
    {
        config(['services.groq.api_key' => 'gsk_test_example']);
        Http::fake([
            'api.groq.com/*' => Http::response([
                'error' => ['message' => 'The configured model is not enabled for this account.'],
            ], 400),
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Livewire::actingAs($admin)->test(EbooksManager::class)
            ->set('title', 'Making Money With AI in Nigeria')
            ->set('freeChapterContent', 'A chapter about using AI.')
            ->call('generateCopy')
            ->assertHasErrors(['copywriter'])
            ->assertSee('Groq rejected the copywriting request with model qwen/qwen3.8-27b (HTTP 400)')
            ->assertSee('The configured model is not enabled for this account.');
    }

    public function test_copywriter_limits_chapter_text_sent_to_groq(): void
    {
        config(['services.groq.api_key' => 'gsk_test_example']);
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'description' => 'A practical guide.',
                            'audience' => 'Readers interested in AI.',
                            'contents' => 'Practical lessons.',
                            'faq' => [],
                            'meta_title' => 'AI guide',
                            'meta_description' => 'A practical AI guide.',
                            'primary_keyword' => 'AI guide',
                        ]),
                    ],
                ]],
            ]),
        ]);

        app(EbookCopywriter::class)->generate(
            'AI guide',
            str_repeat('A', 3000).str_repeat('M', 3000).str_repeat('Z', 3000),
        );

        Http::assertSent(fn ($request) => mb_strlen($request->data()['messages'][1]['content'][0]['text']) <= 7000
            && str_contains($request->data()['messages'][1]['content'][0]['text'], '[...chapter excerpt omitted...]')
            && str_contains($request->data()['messages'][1]['content'][0]['text'], 'AAAA')
            && str_contains($request->data()['messages'][1]['content'][0]['text'], 'MMMM')
            && str_contains($request->data()['messages'][1]['content'][0]['text'], 'ZZZZ'));
    }

    public function test_copywriter_retries_rate_limited_requests_after_provider_delay(): void
    {
        config(['services.groq.api_key' => 'gsk_test_example']);
        $success = [
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'description' => 'A practical guide.',
                        'audience' => 'Readers interested in AI.',
                        'contents' => 'Practical lessons.',
                        'faq' => [],
                        'meta_title' => 'AI guide',
                        'meta_description' => 'A practical AI guide.',
                        'primary_keyword' => 'AI guide',
                    ]),
                ],
            ]],
        ];
        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'Please try again in 0s.']], 429)
                ->push($success, 200),
        ]);

        app(EbookCopywriter::class)->generate('AI guide', 'A chapter about using AI.');

        Http::assertSentCount(2);
    }

    public function test_copywriter_uses_fallback_model_after_primary_model_rate_limit(): void
    {
        config([
            'services.groq.api_key' => 'gsk_test_example',
            'services.groq.model' => 'custom/primary-model',
            'services.groq.fallback_models' => [
                'qwen/qwen3.8-27b',
                'openai/gpt-oss-20b',
                'openai/gpt-oss-120b',
            ],
        ]);
        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'Rate limit reached.']], 429, ['Retry-After' => '11'])
                ->push(['choices' => [['message' => ['content' => json_encode([
                    'description' => 'A practical guide.',
                    'audience' => 'Readers interested in AI.',
                    'contents' => 'Practical lessons.',
                    'faq' => [],
                    'meta_title' => 'AI guide',
                    'meta_description' => 'A practical AI guide.',
                    'primary_keyword' => 'AI guide',
                ])]]]], 200),
        ]);

        app(EbookCopywriter::class)->generate('AI guide', 'A chapter about using AI.');

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->data()['model'] === 'custom/primary-model');
        Http::assertSent(fn ($request) => $request->data()['model'] === 'qwen/qwen3.8-27b');
    }

    public function test_copywriter_normalizes_list_fields_in_model_response(): void
    {
        config(['services.groq.api_key' => 'gsk_test_example']);
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'description' => ['An introduction.', 'Practical advice.'],
                            'audience' => ['New learners', 'Small business owners'],
                            'contents' => ['Finding a service', 'Getting paid'],
                            'faq' => [['question' => ['Who is this for?'], 'answer' => ['Beginners.']]],
                            'meta_title' => 'AI guide',
                            'meta_description' => 'A practical AI guide.',
                            'primary_keyword' => 'AI guide',
                        ]),
                    ],
                ]],
            ]),
        ]);

        $draft = app(EbookCopywriter::class)->generate('AI guide', 'A chapter about using AI.');

        $this->assertSame("An introduction.\nPractical advice.", $draft['description']);
        $this->assertSame("New learners\nSmall business owners", $draft['audience']);
        $this->assertSame('Who is this for?', $draft['faq'][0]['question']);
    }
}
