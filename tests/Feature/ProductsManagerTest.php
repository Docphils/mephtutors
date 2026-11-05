<?php

namespace Tests\Feature;

use App\Enums\EbookStatus;
use App\Livewire\Admin\ProductsManager;
use App\Livewire\Pages\ProductsPage;
use App\Models\CompanyProduct;
use App\Models\Ebook;
use App\Models\User;
use App\Services\EbookCopywriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ProductsManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_link_a_product_to_the_ebook_catalog_on_the_same_site(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        Ebook::factory()->create([
            'title' => 'AI Skills Guide',
            'slug' => 'ai-skills-guide',
            'status' => EbookStatus::Published,
        ]);

        Livewire::actingAs($admin)->test(ProductsManager::class)
            ->set('name', 'MephEd eBooks')
            ->set('internalLinkTarget', '/ebooks')
            ->assertSet('websiteUrl', '/ebooks')
            ->assertSet('ctaLabel', 'Browse eBooks')
            ->set('summary', 'Browse practical digital books published by MephEd.')
            ->set('isPublished', true)
            ->call('saveProduct')
            ->assertHasNoErrors();

        $product = CompanyProduct::query()->where('slug', 'mephed-ebooks')->firstOrFail();
        $this->assertSame('/ebooks', $product->website_url);
        $this->assertTrue($product->hasInternalWebsiteUrl());

        $page = Livewire::test(ProductsPage::class);
        $this->assertMatchesRegularExpression(
            '/<a href="\/ebooks"([^>]*)>/',
            $page->html(),
        );
        preg_match('/<a href="\/ebooks"([^>]*)>/', $page->html(), $link);
        $this->assertArrayHasKey(1, $link);
        $this->assertStringNotContainsString('target="_blank"', $link[1]);

        $hub = Livewire::test(\App\Livewire\Ebooks\EbookHubPage::class);
        $hub->assertSee('AI Skills Guide')
            ->assertSee(route('ebooks.show', ['slug' => 'ai-skills-guide']));
    }

    public function test_internal_product_links_must_point_to_published_ebooks(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        Ebook::factory()->create([
            'slug' => 'not-published',
            'status' => EbookStatus::Draft,
        ]);

        Livewire::actingAs($admin)->test(ProductsManager::class)
            ->set('name', 'Unpublished Book')
            ->set('summary', 'A book that is not public yet.')
            ->set('websiteUrl', '/ebooks/not-published')
            ->call('saveProduct')
            ->assertHasErrors(['websiteUrl']);

        $this->assertDatabaseMissing('company_products', ['name' => 'Unpublished Book']);
    }

    public function test_ai_copy_suggestions_are_reviewed_before_applying_to_product_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $copywriter = Mockery::mock(EbookCopywriter::class);
        $copywriter->shouldReceive('generateProductCopy')
            ->once()
            ->with(
                'Study Planner',
                'Education',
                'Available',
                'Current summary',
                'Current details',
                'A planner for secondary students',
            )
            ->andReturn([
                'summary' => 'A practical planner for secondary students.',
                'description' => 'Organize study goals and learning routines.',
            ]);
        $this->app->instance(EbookCopywriter::class, $copywriter);

        Livewire::actingAs($admin)->test(ProductsManager::class)
            ->set('name', 'Study Planner')
            ->set('category', 'education')
            ->set('releaseStatus', 'available')
            ->set('summary', 'Current summary')
            ->set('description', 'Current details')
            ->set('aiNotes', 'A planner for secondary students')
            ->call('generateProductCopy')
            ->assertHasNoErrors()
            ->assertSet('summary', 'Current summary')
            ->assertSet('suggestedSummary', 'A practical planner for secondary students.')
            ->assertSet('suggestedDescription', 'Organize study goals and learning routines.')
            ->call('applyProductCopySuggestions')
            ->assertHasNoErrors()
            ->assertSet('summary', 'A practical planner for secondary students.')
            ->assertSet('description', 'Organize study goals and learning routines.')
            ->assertSet('suggestedSummary', null);
    }

    public function test_groq_product_copy_uses_supplied_product_facts_and_returns_draft_text(): void
    {
        config(['services.groq.api_key' => 'gsk_test_example']);
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'summary' => 'A practical planner for students.',
                            'description' => 'Organize study goals and learning routines.',
                        ]),
                    ],
                ]],
            ]),
        ]);

        $draft = app(EbookCopywriter::class)->generateProductCopy(
            'Study Planner',
            'Education',
            'Available',
            '',
            '',
            'For secondary students; helps organize study goals.',
        );

        $this->assertSame('A practical planner for students.', $draft['summary']);
        $this->assertSame('Organize study goals and learning routines.', $draft['description']);
        Http::assertSent(fn ($request) => str_contains(
            $request->data()['messages'][1]['content'],
            'For secondary students; helps organize study goals.',
        ));
    }
}
