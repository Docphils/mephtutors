<?php

namespace Tests\Feature;

use App\Enums\EbookStatus;
use App\Exceptions\BlogDraftRejectedException;
use App\Jobs\GenerateBlogDraft;
use App\Jobs\GenerateBlogTopicSuggestions;
use App\Jobs\PublishBlogPostToFacebook;
use App\Jobs\SendBlogDraftReviewEmail;
use App\Livewire\Admin\BlogEditor;
use App\Livewire\Admin\BlogSettingsManager;
use App\Livewire\Admin\BlogStudio;
use App\Mail\BlogDraftReviewMail;
use App\Models\BlogFacebookShare;
use App\Models\BlogGenerationRun;
use App\Models\BlogPost;
use App\Models\BlogSetting;
use App\Models\BlogTopic;
use App\Models\BlogTopicSuggestionBatch;
use App\Models\Ebook;
use App\Models\User;
use App\Services\BlogDraftPipeline;
use App\Services\BlogDraftWriter;
use App\Services\BlogFacebookPublisher;
use App\Services\BlogFacebookWorkflow;
use App\Services\BlogGenerationWorkflow;
use App\Services\BlogResearchService;
use App\Services\BlogTavilyCreditBudget;
use App\Services\BlogTopicSuggestionService;
use App\Services\BlogWebSearchService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class BlogWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_blog_is_enabled_after_foundation_completion_and_lists_only_published_articles(): void
    {
        config(['blog.public_enabled' => true]);
        $post = $this->createPost(null, [
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('line-clamp-2', false)
            ->assertSee('line-clamp-3', false);
        $this->get(route('blog.show', $post->slug))->assertOk()->assertSee($post->title);
        $this->get(route('blog.feed'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee($post->title);
        $this->get(route('sitemap'))->assertOk()
            ->assertSee(route('blog.index'), false)
            ->assertSee(route('blog.show', $post->slug), false);

        $draft = $this->createPost(null, [
            'title' => 'Hidden blog draft',
            'slug' => 'hidden-blog-draft',
        ]);
        $this->get(route('blog.show', $draft->slug))->assertNotFound();
        $this->get(route('blog.feed'))->assertDontSee($draft->title);
        $this->get(route('sitemap'))->assertDontSee(route('blog.show', $draft->slug), false);
    }

    public function test_only_verified_admins_can_open_the_blog_studio_and_private_previews(): void
    {
        $post = $this->createPost();
        config(['blog.generation_enabled' => true]);

        $this->get(route('admin.blog.index'))->assertRedirect(route('login'));
        $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
        $this->actingAs($client)->get(route('admin.blog.index'))->assertForbidden();

        $unverifiedAdmin = User::factory()->create(['role' => 'admin', 'email_verified_at' => null]);
        $this->actingAs($unverifiedAdmin)->get(route('admin.blog.index'))
            ->assertRedirect(route('verification.notice'));

        $admin = User::factory()->create([
            'name' => 'Internal Admin',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $studio = $this->actingAs($admin)->get(route('admin.blog.index'))->assertOk();
        $html = $studio->getContent();
        $componentRoot = strpos($html, 'wire:id="');
        $generateButton = strpos($html, 'wire:click="generateNow"');
        $this->assertNotFalse($componentRoot);
        $this->assertNotFalse($generateButton);
        $this->assertGreaterThan($componentRoot, $generateButton, 'The generation action must be rendered inside the Livewire component root.');

        $preview = $this->get(route('admin.blog.preview', $post))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Private article body');
        $this->assertStringContainsString('no-store', $preview->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $preview->headers->get('Cache-Control'));
    }

    public function test_admin_can_save_sanitized_drafts_publish_them_and_expose_only_published_articles(): void
    {
        config([
            'blog.public_enabled' => true,
            'blog.facebook_enabled' => false,
            'blog.author_byline' => 'MephEd Educational Services',
        ]);
        $admin = User::factory()->create([
            'name' => 'Internal Admin',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($admin)->test(BlogEditor::class)
            ->set('title', 'Learning that lasts')
            ->set('slug', 'learning-that-lasts')
            ->set('excerpt', 'A useful summary.')
            ->set('body', '<p>Safe <strong>content</strong><script>alert(1)</script><a href="javascript:alert(2)" onclick="alert(3)">unsafe link</a></p>')
            ->set('category', 'Learning')
            ->set('tags', 'Study, Practice')
            ->set('metaTitle', 'Learning that lasts | MephEd')
            ->set('metaDescription', 'Evidence-informed learning advice.')
            ->call('save')
            ->assertHasNoErrors();

        $post = BlogPost::query()->where('slug', 'learning-that-lasts')->firstOrFail();
        $this->assertSame(BlogPost::STATUS_DRAFT, $post->status);
        $this->assertStringContainsString('<strong>content</strong>', $post->body);
        $this->assertStringNotContainsString('<script', $post->body);
        $this->assertStringNotContainsString('javascript:', $post->body);
        $this->assertStringNotContainsString('onclick', $post->body);
        $this->assertDatabaseHas('blog_categories', ['slug' => 'learning']);
        $this->assertDatabaseCount('blog_post_tag', 2);

        $this->get(route('admin.blog.edit', $post->id))->assertOk()->assertSee('Learning that lasts');
        $this->get(route('blog.show', $post->slug))->assertNotFound();
        $this->get(route('blog.feed'))->assertOk()->assertDontSee($post->title);
        $this->get(route('sitemap'))->assertOk()->assertDontSee(route('blog.show', $post->slug), false);

        Livewire::actingAs($admin)->test(BlogStudio::class)
            ->call('publish', $post->id)
            ->assertHasNoErrors();
        $this->assertSame(BlogFacebookShare::STATUS_MANUAL, $post->fresh()->facebookShare?->status);
        $publishedAt = $post->fresh()->published_at;
        Livewire::actingAs($admin)->test(BlogStudio::class)
            ->call('publish', $post->id)
            ->assertHasNoErrors();
        $this->assertTrue($post->fresh()->published_at->equalTo($publishedAt));
        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('Learning that lasts')
            ->assertSee('Internal Admin | MephEd Educational Services')
            ->assertSee('"@type":"Person","name":"Internal Admin"', false)
            ->assertDontSee($admin->email)
            ->assertSee('name="robots" content="index,follow"', false)
            ->assertSee('BlogPosting', false)
            ->assertSee('<strong>content</strong>', false)
            ->assertDontSee('alert(1)')
            ->assertDontSee('onclick');
        $this->get(route('blog.index'))->assertOk()->assertSee($post->title);
        $this->get(route('blog.index', ['q' => 'Safe']))->assertOk()->assertSee($post->title);
        $this->get(route('blog.category', 'learning'))->assertOk()->assertSee($post->title);
        $this->get(route('blog.tag', 'study'))->assertOk()->assertSee($post->title);
        $this->get(route('blog.feed'))->assertOk()->assertSee($post->title);
        $this->get(route('sitemap'))->assertOk()->assertSee(route('blog.show', $post->slug), false);
        Livewire::actingAs($admin)->test(BlogStudio::class)
            ->call('unpublish', $post->id)
            ->assertHasNoErrors();
        $this->get(route('blog.show', $post->slug))->assertNotFound();
        $this->get(route('blog.feed'))->assertOk()->assertDontSee($post->title);
    }

    public function test_blog_editor_uses_trix_and_supports_featured_image_upload_and_removal(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.blog.create'))
            ->assertOk()
            ->assertSee('<trix-editor', false)
            ->assertSee('id="blog-hero-image"', false)
            ->assertSee('1200 × 630');

        Livewire::actingAs($admin)->test(BlogEditor::class)
            ->set('title', 'A useful learning guide')
            ->set('body', '<h2>Start here</h2><p><strong>Good advice</strong> <del>old advice</del></p><script>alert(1)</script>')
            ->set('heroImage', UploadedFile::fake()->image('featured.jpg', 1200, 630))
            ->call('save')
            ->assertHasNoErrors();

        $post = BlogPost::query()->where('slug', 'a-useful-learning-guide')->firstOrFail();
        $this->assertSame('<h2>Start here</h2><p><strong>Good advice</strong> <del>old advice</del></p>', $post->body);
        $this->assertNotNull($post->hero_image_path);
        $imagePath = $post->hero_image_path;
        Storage::disk('public')->assertExists($imagePath);

        config(['blog.public_enabled' => true]);
        $post->update(['status' => BlogPost::STATUS_PUBLISHED, 'published_at' => now()]);
        $imageUrl = Storage::disk('public')->url($imagePath);
        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.e($imageUrl).'">', false);

        Livewire::actingAs($admin)->test(BlogEditor::class, ['postId' => $post->id])
            ->set('excerpt', 'Updated summary, image retained.')
            ->call('save')
            ->assertHasNoErrors();
        Storage::disk('public')->assertExists($imagePath);

        Livewire::actingAs($admin)->test(BlogEditor::class, ['postId' => $post->id])
            ->set('removeHeroImage', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($post->fresh()->hero_image_path);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_changing_a_published_slug_redirects_the_old_public_url(): void
    {
        config(['blog.public_enabled' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $post = $this->createPost($admin, [
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        Livewire::actingAs($admin)->test(BlogEditor::class, ['postId' => $post->id])
            ->set('slug', 'new-private-education-guide')
            ->call('save')
            ->assertHasNoErrors();

        $this->get(route('blog.show', 'private-education-guide'))
            ->assertRedirect(route('blog.show', 'new-private-education-guide'), 301);
    }

    public function test_editor_rejects_more_than_ten_unique_tags_instead_of_silently_dropping_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $tags = implode(', ', ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven']);

        Livewire::actingAs($admin)->test(BlogEditor::class)
            ->set('title', 'Tag limit test')
            ->set('slug', 'tag-limit-test')
            ->set('body', '<p>Draft content.</p>')
            ->set('tags', $tags)
            ->call('save')
            ->assertHasErrors('tags');

        $this->assertDatabaseMissing('blog_posts', ['slug' => 'tag-limit-test']);
    }

    public function test_ai_field_assistance_previews_before_applying_and_keeps_original_text_unchanged(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->instance(BlogDraftWriter::class, Mockery::mock(BlogDraftWriter::class, function ($mock): void {
            $mock->shouldReceive('suggestField')
                ->once()
                ->with('title', 'Current headline', 'Write for parents', Mockery::on(fn (array $fields): bool => $fields['title'] === 'Headline'))
                ->andReturn('A clearer headline for parents');
        }));

        Livewire::actingAs($admin)->test(BlogEditor::class)
            ->set('title', 'Current headline')
            ->set('aiAssistInstruction', 'Write for parents')
            ->call('requestAiSuggestion', 'title')
            ->assertHasNoErrors()
            ->assertSet('title', 'Current headline')
            ->assertSet('aiSuggestion', 'A clearer headline for parents')
            ->assertSee('Your current field has not changed.')
            ->call('applyAiSuggestion')
            ->assertHasNoErrors()
            ->assertSet('title', 'A clearer headline for parents')
            ->assertSet('aiSuggestion', null);
    }

    public function test_ai_body_suggestions_are_sanitized_and_synchronized_with_the_rich_text_editor(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->instance(BlogDraftWriter::class, Mockery::mock(BlogDraftWriter::class, function ($mock): void {
            $mock->shouldReceive('suggestField')->once()->andReturn('<p>Safe body</p><script>alert(1)</script>');
        }));

        Livewire::actingAs($admin)->test(BlogEditor::class)
            ->set('body', '<p>Original body</p>')
            ->call('requestAiSuggestion', 'body')
            ->assertHasNoErrors()
            ->assertSet('body', '<p>Original body</p>')
            ->call('applyAiSuggestion')
            ->assertHasNoErrors()
            ->assertSet('body', '<p>Safe body</p>')
            ->assertDispatched('blog-body-suggestion-applied', body: '<p>Safe body</p>');
    }

    public function test_blog_editorial_and_research_settings_persist_for_verified_admins(): void
    {
        $this->get(route('admin.blog.settings'))->assertRedirect(route('login'));
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Livewire::actingAs($admin)->test(BlogSettingsManager::class)
            ->assertSet('dailyTime', '04:00')
            ->set('dailyTime', '05:30')
            ->set('notificationEmails', "Editor@Example.com\nreviewer@example.com, editor@example.com")
            ->set('contentWordTarget', '950')
            ->set('topicBatchSize', '12')
            ->set('topicLowWatermark', '4')
            ->set('tavilyMonthlyCreditLimit', '1400')
            ->set('tavilyMaxResults', '8')
            ->set('tavilySearchDepth', 'advanced')
            ->set('tavilyCurrentAffairsRange', 'week')
            ->set('preferredResearchDomains', "education.gov.ng\nnuc.edu.ng")
            ->set('defaultAuthorId', (string) $admin->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('05:30', BlogSetting::getValue('daily_time'));
        $this->assertSame('Africa/Lagos', BlogSetting::getValue('timezone'));
        $this->assertSame(['editor@example.com', 'reviewer@example.com'], BlogSetting::getValue('notification_emails'));
        $this->assertSame(950, BlogSetting::getValue('content_word_target'));
        $this->assertSame(12, BlogSetting::getValue('topic_batch_size'));
        $this->assertSame(4, BlogSetting::getValue('topic_low_watermark'));
        $this->assertSame(1400, BlogSetting::getValue('tavily_monthly_credit_limit'));
        $this->assertSame(8, BlogSetting::getValue('tavily_max_results'));
        $this->assertSame('advanced', BlogSetting::getValue('tavily_search_depth'));
        $this->assertSame('week', BlogSetting::getValue('tavily_current_affairs_time_range'));
        $this->assertSame(['education.gov.ng', 'nuc.edu.ng'], BlogSetting::getValue('preferred_research_domains'));
        $this->assertSame($admin->id, BlogSetting::getValue('default_author_id'));
        Livewire::actingAs($admin)->test(BlogSettingsManager::class)
            ->assertSet('dailyTime', '05:30')
            ->assertSet('notificationEmails', 'editor@example.com, reviewer@example.com')
            ->assertSet('contentWordTarget', '950')
            ->assertSet('topicBatchSize', '12')
            ->assertSet('tavilySearchDepth', 'advanced')
            ->assertSet('preferredResearchDomains', "education.gov.ng\nnuc.edu.ng");

        Livewire::actingAs($admin)->test(BlogSettingsManager::class)
            ->set('dailyTime', '25:30')
            ->set('notificationEmails', 'editor@example.com')
            ->call('save')
            ->assertHasErrors('dailyTime');
        Livewire::actingAs($admin)->test(BlogSettingsManager::class)
            ->set('dailyTime', '04:00')
            ->set('notificationEmails', 'not-an-email')
            ->call('save')
            ->assertHasErrors('notificationEmails');
        Livewire::actingAs($admin)->test(BlogSettingsManager::class)
            ->set('preferredResearchDomains', 'https://*.example.com/feed.xml')
            ->call('save')
            ->assertHasErrors('preferredResearchDomains');

        $this->assertSame('05:30', BlogSetting::getValue('daily_time'));
    }

    public function test_tavily_search_supplies_results_from_unlisted_hosts_without_fetching_those_hosts(): void
    {
        config(['services.tavily.api_key' => 'test-tavily-key']);
        BlogSetting::setValue('preferred_research_domains', ['education.gov.ng']);
        $topic = $this->createTopic([
            'title' => 'New education programme',
            'topic_type' => BlogTopic::TYPE_CURRENT_AFFAIRS,
            'angle' => 'What the change means for public schools.',
        ]);
        Http::fake([
            'api.tavily.com/search' => Http::response([
                'results' => [
                    [
                        'title' => 'New education programme',
                        'url' => 'https://independent.example.org/news/programme',
                        'content' => '<p>Details of the new programme.</p>',
                        'published_date' => '2026-11-06',
                    ],
                    [
                        'title' => 'Unsafe result',
                        'url' => 'http://127.0.0.1/internal',
                        'content' => 'This must not be used.',
                    ],
                    [
                        'title' => 'Tokenized URL',
                        'url' => 'https://independent.example.org/news?access_token=secret',
                        'content' => 'This must not be used.',
                    ],
                ],
            ]),
        ]);

        $result = app(BlogWebSearchService::class)->fetch($topic);

        $this->assertCount(1, $result['items']);
        $this->assertSame('independent.example.org', $result['items'][0]['source_host']);
        $this->assertSame('Details of the new programme.', $result['items'][0]['summary']);
        $this->assertSame([], $result['errors']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.tavily.com/search'
            && $request->hasHeader('Authorization')
            && str_contains($request['query'], 'New education programme')
            && str_contains($request['query'], 'education.gov.ng')
            && $request['search_depth'] === 'basic'
            && $request['max_results'] === 10
            && $request['topic'] === 'news'
            && $request['time_range'] === 'month'
            && $request['include_raw_content'] === false);
        Http::assertSentCount(1);
        $this->assertSame(1, app(BlogTavilyCreditBudget::class)->currentMonthUsage());
    }

    public function test_tavily_search_reports_missing_credentials_without_sending_a_request(): void
    {
        config(['services.tavily.api_key' => null]);
        Http::fake();

        $result = app(BlogWebSearchService::class)->fetch($this->createTopic());

        $this->assertSame([], $result['items']);
        $this->assertStringContainsString('TAVILY_API_KEY', $result['errors'][0]);
        Http::assertNothingSent();
    }

    public function test_tavily_uses_general_research_for_evergreen_topics_and_honors_the_credit_cap(): void
    {
        config(['services.tavily.api_key' => 'test-tavily-key']);
        $topic = $this->createTopic(['topic_type' => BlogTopic::TYPE_PRACTICAL]);
        Http::fake([
            'api.tavily.com/search' => Http::response([
                'results' => [[
                    'title' => 'A practical education guide',
                    'url' => 'https://education.example.org/guide',
                    'content' => 'Evidence and useful ideas.',
                ]],
            ]),
        ]);

        $result = app(BlogWebSearchService::class)->fetch($topic);

        $this->assertCount(1, $result['items']);
        Http::assertSent(fn (Request $request) => $request['topic'] === 'general'
            && ! array_key_exists('time_range', $request->data()));

        BlogSetting::setValue('tavily_search_depth', 'advanced');
        BlogSetting::setValue('tavily_monthly_credit_limit', 1);
        Http::fake();
        $blocked = app(BlogWebSearchService::class)->fetch($topic);

        $this->assertSame([], $blocked['items']);
        $this->assertStringContainsString('monthly Tavily credit limit', $blocked['errors'][0]);
        $this->assertSame(1, app(BlogTavilyCreditBudget::class)->currentMonthUsage());
        Http::assertNothingSent();
    }

    public function test_research_service_searches_for_the_selected_topic_without_rss_feeds(): void
    {
        $searchItem = [
            'title' => 'Education update',
            'summary' => 'Search summary.',
            'url' => 'https://news.example.org/story',
            'published_at' => null,
            'source_host' => 'news.example.org',
        ];
        $topic = $this->createTopic();
        $this->instance(BlogWebSearchService::class, Mockery::mock(BlogWebSearchService::class, function ($mock) use ($searchItem, $topic): void {
            $mock->shouldReceive('fetch')->once()->with(Mockery::on(fn (BlogTopic $value): bool => $value->is($topic)))
                ->andReturn(['items' => [$searchItem], 'errors' => []]);
        }));

        $result = app(BlogResearchService::class)->fetch($topic);

        $this->assertCount(1, $result['items']);
        $this->assertSame('Search summary.', $result['items'][0]['summary']);
    }

    public function test_daily_blog_dispatch_is_idempotent_and_respects_the_opt_in_flag(): void
    {
        Queue::fake();
        config(['blog.generation_enabled' => false]);
        $workflow = app(BlogGenerationWorkflow::class);
        $this->assertNull($workflow->dispatchScheduled());
        $this->assertDatabaseCount('blog_generation_runs', 0);

        config(['blog.generation_enabled' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        BlogSetting::setValue('default_author_id', $admin->id);
        $this->createTopic(['status' => BlogTopic::STATUS_APPROVED, 'approved_by' => $admin->id, 'approved_at' => now()]);
        BlogSetting::setValue('daily_time', '04:00');
        Carbon::setTestNow(Carbon::parse('2026-11-06 04:01:00', 'Africa/Lagos'));

        try {
            $firstRun = $workflow->dispatchScheduled();
            $secondRun = $workflow->dispatchScheduled();
        } finally {
            Carbon::setTestNow();
        }

        $this->assertNotNull($firstRun);
        $this->assertSame($firstRun->id, $secondRun?->id);
        $this->assertDatabaseCount('blog_generation_runs', 1);
        $this->assertNotNull($firstRun->blog_topic_id);
        $this->assertSame($admin->id, $firstRun->author_id);
        Queue::assertPushedOn('blog', GenerateBlogDraft::class);
        Queue::assertPushedTimes(GenerateBlogDraft::class, 1);
    }

    public function test_admin_can_queue_a_manual_blog_generation_run_with_visible_confirmation(): void
    {
        Queue::fake();
        config(['blog.generation_enabled' => true]);
        Log::spy();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->createTopic(['status' => BlogTopic::STATUS_APPROVED, 'approved_by' => $admin->id, 'approved_at' => now()]);

        Livewire::actingAs($admin)->test(BlogStudio::class)
            ->set('wordTarget', '1100')
            ->call('generateNow')
            ->assertHasNoErrors()
            ->assertSee('Draft generation was queued.')
            ->assertSee('Queued and waiting for the blog worker to start.');

        Log::shouldHaveReceived('info')->with(
            'A manual blog draft generation run was queued.',
            Mockery::on(fn (array $context): bool => $context['run_id'] > 0 && $context['queue'] === 'blog'),
        )->once();
        $run = BlogGenerationRun::query()->where('run_key', 'like', 'manual:%')->firstOrFail();
        $this->assertSame(BlogGenerationRun::STATUS_QUEUED, $run->status);
        $this->assertSame($admin->id, $run->author_id);
        $this->assertSame(1100, $run->word_target);
        Queue::assertPushedOn('blog', GenerateBlogDraft::class, fn (GenerateBlogDraft $job) => $job->runId === $run->id);
    }

    public function test_manual_generation_flag_failure_is_visible_and_logged(): void
    {
        config(['blog.generation_enabled' => false]);
        Log::shouldReceive('warning')->once();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Livewire::actingAs($admin)->test(BlogStudio::class)
            ->call('generateNow')
            ->assertHasNoErrors()
            ->assertSee('Blog generation is disabled.');

        $this->assertDatabaseCount('blog_generation_runs', 0);
    }

    public function test_manual_generation_waits_for_an_approved_topic_and_requests_suggestions(): void
    {
        Queue::fake();
        config(['blog.generation_enabled' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Livewire::actingAs($admin)->test(BlogStudio::class)
            ->call('generateNow')
            ->assertHasNoErrors()
            ->assertSee('waiting for an administrator to approve a topic');

        $run = BlogGenerationRun::query()->firstOrFail();
        $batch = BlogTopicSuggestionBatch::query()->firstOrFail();
        $this->assertSame(BlogGenerationRun::STATUS_AWAITING_TOPIC, $run->status);
        $this->assertNull($run->blog_topic_id);
        Queue::assertPushedOn('blog', GenerateBlogTopicSuggestions::class, fn ($job) => $job->batchId === $batch->id);
        Queue::assertNotPushed(GenerateBlogDraft::class);
    }

    public function test_approving_a_topic_releases_the_oldest_generation_run_waiting_for_approval(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $topic = $this->createTopic(['status' => BlogTopic::STATUS_PENDING, 'approved_at' => null]);
        $run = BlogGenerationRun::query()->create([
            'run_key' => 'manual:awaiting-topic',
            'status' => BlogGenerationRun::STATUS_AWAITING_TOPIC,
            'author_id' => $admin->id,
            'word_target' => 850,
        ]);

        Livewire::actingAs($admin)->test(BlogStudio::class)
            ->assertSee($topic->title)
            ->call('approveTopic', $topic->id)
            ->assertHasNoErrors()
            ->assertSee('Topic approved');

        $this->assertSame(BlogTopic::STATUS_APPROVED, $topic->fresh()->status);
        $this->assertSame(BlogGenerationRun::STATUS_QUEUED, $run->fresh()->status);
        $this->assertSame($topic->id, $run->fresh()->blog_topic_id);
        Queue::assertPushedOn('blog', GenerateBlogDraft::class, fn (GenerateBlogDraft $job) => $job->runId === $run->id);
    }

    public function test_an_approved_topic_can_be_reused_for_an_explicit_regeneration(): void
    {
        Queue::fake();
        config(['blog.generation_enabled' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin);
        $topic = $this->createTopic();
        $workflow = app(BlogGenerationWorkflow::class);

        $first = $workflow->dispatchManualForTopic($topic->id, 900);
        $second = $workflow->dispatchManualForTopic($topic->id, 1100);

        $this->assertSame($topic->id, $first->blog_topic_id);
        $this->assertSame($topic->id, $second->blog_topic_id);
        $this->assertSame(900, $first->word_target);
        $this->assertSame(1100, $second->word_target);
        Queue::assertPushedTimes(GenerateBlogDraft::class, 2);
    }

    public function test_ai_topic_suggestions_use_published_ebooks_and_enter_the_bank_as_pending(): void
    {
        config(['services.groq.api_key' => 'test-key', 'services.groq.blog_model' => 'test-model']);
        $ebook = Ebook::query()->create([
            'slug' => 'learning-guide',
            'title' => 'Learning Guide',
            'author_name' => 'MephEd',
            'publisher_name' => 'MephEd',
            'price_kobo' => 100000,
            'currency' => 'NGN',
            'description' => 'A guide to effective learning.',
            'status' => EbookStatus::Published,
        ]);
        $batch = BlogTopicSuggestionBatch::query()->create([
            'batch_key' => 'manual:test-topics',
            'status' => BlogTopicSuggestionBatch::STATUS_QUEUED,
            'requested_count' => 1,
        ]);
        Http::fake([
            'api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode([
                    'topics' => [[
                        'title' => 'Building better study habits',
                        'topic_type' => BlogTopic::TYPE_EBOOK_INSPIRED,
                        'angle' => 'Connect ideas from the learning guide with realistic study routines.',
                        'related_ebook_id' => $ebook->id,
                    ]],
                ], JSON_THROW_ON_ERROR)]]],
            ]),
        ]);

        app(BlogTopicSuggestionService::class)->generate($batch->id);

        $topic = BlogTopic::query()->sole();
        $this->assertSame(BlogTopic::STATUS_PENDING, $topic->status);
        $this->assertSame($ebook->id, $topic->related_ebook_id);
        $this->assertSame(BlogTopicSuggestionBatch::STATUS_COMPLETED, $batch->fresh()->status);
        Http::assertSent(fn (Request $request) => str_contains($request['messages'][1]['content'], 'Learning Guide')
            && str_contains($request['messages'][1]['content'], 'Treat supplied eBook descriptions'));
    }

    public function test_failed_blog_generation_can_be_retried_without_creating_a_second_run(): void
    {
        Queue::fake();
        config(['blog.generation_enabled' => true]);
        $topic = $this->createTopic(['status' => BlogTopic::STATUS_APPROVED, 'approved_at' => now()]);
        $run = BlogGenerationRun::query()->create([
            'run_key' => 'daily:2026-11-06',
            'scheduled_for' => '2026-11-06',
            'status' => BlogGenerationRun::STATUS_FAILED,
            'blog_topic_id' => $topic->id,
            'error_message' => 'Provider temporarily unavailable.',
        ]);

        $retried = app(BlogGenerationWorkflow::class)->retryFailed($run->id);

        $this->assertSame($run->id, $retried->id);
        $this->assertSame(BlogGenerationRun::STATUS_QUEUED, $retried->status);
        $this->assertNull($retried->error_message);
        $this->assertDatabaseCount('blog_generation_runs', 1);
        Queue::assertPushedOn('blog', GenerateBlogDraft::class);

        $this->expectException(\RuntimeException::class);
        app(BlogGenerationWorkflow::class)->retryFailed($run->id);
    }

    public function test_groq_blog_writer_uses_json_mode_and_returns_a_structured_draft(): void
    {
        config(['services.groq.api_key' => 'test-key', 'services.groq.blog_model' => 'test-model']);
        Http::fake([
            'api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"title":"A useful article"}']]],
            ]),
        ]);
        $sources = [[
            'title' => 'Education policy update',
            'summary' => 'An approved source summary.',
            'url' => 'https://education.gov.ng/update',
            'published_at' => null,
            'source_host' => 'education.gov.ng',
        ]];
        $topic = $this->createTopic(['title' => 'A focused learning topic']);

        $draft = app(BlogDraftWriter::class)->write($sources, [], [], null, $topic, 1200);

        $this->assertSame('A useful article', $draft['title']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
            && $request['response_format']['type'] === 'json_object'
            && str_contains($request['messages'][0]['content'], 'untrusted data')
            && str_contains($request['messages'][1]['content'], 'Target approximately 1200 words')
            && str_contains($request['messages'][1]['content'], 'A focused learning topic'));
    }

    public function test_gemini_blog_writer_keeps_api_credentials_out_of_the_url(): void
    {
        config([
            'blog.ai_provider' => 'gemini',
            'services.gemini.api_key' => 'test-gemini-secret',
            'services.gemini.blog_model' => 'gemini-2.5-flash',
        ]);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{"title":"A useful article"}']]]]],
            ]),
        ]);

        $draft = app(BlogDraftWriter::class)->write([[
            'title' => 'Education policy update',
            'summary' => 'An approved source summary.',
            'url' => 'https://education.gov.ng/update',
            'published_at' => null,
            'source_host' => 'education.gov.ng',
        ]]);

        $this->assertSame('A useful article', $draft['title']);
        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'generativelanguage.googleapis.com')
            && ! str_contains($request->url(), 'test-gemini-secret')
            && $request->hasHeader('x-goog-api-key', 'test-gemini-secret')
            && $request['generationConfig']['responseMimeType'] === 'application/json');
    }

    public function test_pipeline_creates_one_unpublished_cited_draft_with_branded_artwork(): void
    {
        Storage::fake('public');
        Queue::fake();
        BlogSetting::setValue('notification_emails', ['editor@example.com']);
        Ebook::query()->create([
            'slug' => 'published-classroom-guide',
            'title' => 'A Classroom Guide',
            'author_name' => 'MephEd',
            'publisher_name' => 'MephEd',
            'price_kobo' => 100000,
            'currency' => 'NGN',
            'description' => 'An approved classroom resource.',
            'status' => EbookStatus::Published,
        ]);
        Ebook::query()->create([
            'slug' => 'draft-classroom-guide',
            'title' => 'Unpublished Draft Book',
            'author_name' => 'MephEd',
            'publisher_name' => 'MephEd',
            'price_kobo' => 100000,
            'currency' => 'NGN',
            'status' => EbookStatus::Draft,
        ]);
        $run = BlogGenerationRun::query()->create([
            'run_key' => 'manual:test-pipeline',
            'status' => BlogGenerationRun::STATUS_QUEUED,
            'blog_topic_id' => $this->createTopic()->id,
            'provider' => 'groq',
        ]);
        $sources = [[
            'title' => 'A verified education update',
            'summary' => 'A reliable source summary.',
            'url' => 'https://education.gov.ng/update',
            'published_at' => null,
            'source_host' => 'education.gov.ng',
        ]];
        $this->instance(BlogResearchService::class, Mockery::mock(BlogResearchService::class, function ($mock) use ($sources): void {
            $mock->shouldReceive('fetch')->once()->andReturn(['items' => $sources, 'errors' => []]);
        }));
        $this->instance(BlogDraftWriter::class, Mockery::mock(BlogDraftWriter::class, function ($mock): void {
            $mock->shouldReceive('write')->once()->andReturn([
                'title' => 'A Better Path for Nigerian Classrooms',
                'excerpt' => 'A practical, evidence-led guide.',
                'body_html' => '<h2>What educators should know</h2><p>'.str_repeat('Practical guidance helps educators make informed decisions. ', 55).'</p>',
                'category' => 'Education',
                'tags' => ['Teaching', 'Nigeria'],
                'meta_title' => 'A Better Path for Nigerian Classrooms',
                'meta_description' => 'A balanced guide for Nigerian educators.',
                'source_indices' => [1],
                'internal_link_indices' => [1],
            ]);
        }));

        app(BlogDraftPipeline::class)->run($run->id);

        $post = BlogPost::query()->where('title', 'A Better Path for Nigerian Classrooms')->firstOrFail();
        $this->assertSame(BlogPost::STATUS_DRAFT, $post->status);
        $this->assertNull($post->published_at);
        $this->assertStringContainsString('https://education.gov.ng/update', $post->body);
        $this->assertStringContainsString(route('ebooks.show', ['slug' => 'published-classroom-guide']), $post->body);
        $this->assertStringNotContainsString('draft-classroom-guide', $post->body);
        $this->assertNotNull($post->hero_image_path);
        Storage::disk('public')->assertExists($post->hero_image_path);
        $this->assertSame(BlogGenerationRun::STATUS_COMPLETED, $run->fresh()->status);
        $this->assertSame('queued', $run->fresh()->notification_status);
        $this->assertSame('https://education.gov.ng/update', $run->fresh()->metadata['sources'][0]['url']);
        $this->assertSame('A Classroom Guide', $run->fresh()->metadata['internal_links'][0]['title']);
        Queue::assertPushedOn('blog', SendBlogDraftReviewEmail::class);
    }

    public function test_pipeline_revises_overlong_metadata_once_before_saving_the_draft(): void
    {
        Storage::fake('public');
        Queue::fake();
        $run = BlogGenerationRun::query()->create([
            'run_key' => 'manual:field-revision',
            'status' => BlogGenerationRun::STATUS_QUEUED,
            'blog_topic_id' => $this->createTopic()->id,
            'provider' => 'groq',
        ]);
        $sources = [[
            'title' => 'A verified education update',
            'summary' => 'A reliable source summary.',
            'url' => 'https://education.gov.ng/update',
            'published_at' => null,
            'source_host' => 'education.gov.ng',
        ]];
        $this->instance(BlogResearchService::class, Mockery::mock(BlogResearchService::class, function ($mock) use ($sources): void {
            $mock->shouldReceive('fetch')->once()->andReturn(['items' => $sources, 'errors' => []]);
        }));

        $validDraft = [
            'title' => 'A Practical Guide for Nigerian Educators',
            'excerpt' => 'Practical, evidence-led advice for educators.',
            'body_html' => '<p>'.str_repeat('Practical education advice supports informed decisions. ', 100).'</p>',
            'category' => 'Education',
            'tags' => ['Teaching'],
            'meta_title' => 'A Practical Guide for Nigerian Educators',
            'meta_description' => 'An evidence-led guide for educators.',
            'source_indices' => [1],
            'internal_link_indices' => [],
        ];
        $overlongDraft = [...$validDraft, 'meta_description' => str_repeat('x', 321)];
        $this->instance(BlogDraftWriter::class, Mockery::mock(BlogDraftWriter::class, function ($mock) use ($overlongDraft, $validDraft): void {
            $mock->shouldReceive('write')->twice()->andReturn($overlongDraft, $validDraft);
        }));

        app(BlogDraftPipeline::class)->run($run->id);

        $this->assertSame(BlogGenerationRun::STATUS_COMPLETED, $run->fresh()->status);
        $this->assertSame('An evidence-led guide for educators.', $run->fresh()->post->meta_description);
        $this->assertDatabaseCount('blog_posts', 1);
    }

    public function test_pipeline_revises_a_near_duplicate_instead_of_saving_it(): void
    {
        Storage::fake('public');
        Queue::fake();
        $existing = $this->createPost(null, [
            'title' => 'Private article',
            'slug' => 'private-article',
            'excerpt' => 'An existing education article.',
            'body' => '<p>An existing article.</p>',
        ]);
        $run = BlogGenerationRun::query()->create([
            'run_key' => 'manual:similarity-revision',
            'status' => BlogGenerationRun::STATUS_QUEUED,
            'blog_topic_id' => $this->createTopic()->id,
            'provider' => 'groq',
        ]);
        $sources = [[
            'title' => 'A verified education update',
            'summary' => 'A reliable source summary.',
            'url' => 'https://education.gov.ng/update',
            'published_at' => null,
            'source_host' => 'education.gov.ng',
        ]];
        $this->instance(BlogResearchService::class, Mockery::mock(BlogResearchService::class, function ($mock) use ($sources): void {
            $mock->shouldReceive('fetch')->once()->andReturn(['items' => $sources, 'errors' => []]);
        }));
        $duplicate = [
            'title' => $existing->title,
            'excerpt' => 'An existing education article.',
            'body_html' => '<p>'.str_repeat('Practical education advice supports informed decisions. ', 100).'</p>',
            'category' => 'Education',
            'tags' => [],
            'meta_title' => 'Private article',
            'meta_description' => 'An evidence-led guide.',
            'source_indices' => [1],
            'internal_link_indices' => [],
        ];
        $distinct = [...$duplicate,
            'title' => 'A Practical Guide for Nigerian Educators',
            'excerpt' => 'Practical advice for educators.',
            'meta_title' => 'A Practical Guide for Nigerian Educators',
        ];
        $this->instance(BlogDraftWriter::class, Mockery::mock(BlogDraftWriter::class, function ($mock) use ($duplicate, $distinct): void {
            $mock->shouldReceive('write')->twice()->andReturn($duplicate, $distinct);
        }));

        app(BlogDraftPipeline::class)->run($run->id);

        $this->assertSame(BlogGenerationRun::STATUS_COMPLETED, $run->fresh()->status);
        $this->assertSame('A Practical Guide for Nigerian Educators', $run->fresh()->post->title);
        $this->assertDatabaseCount('blog_posts', 2);
    }

    public function test_terminal_editorial_rejection_does_not_trigger_a_queue_retry(): void
    {
        $pipeline = Mockery::mock(BlogDraftPipeline::class);
        $pipeline->shouldReceive('run')->once()->with(99)->andThrow(new BlogDraftRejectedException('Duplicate content.'));

        (new GenerateBlogDraft(99))->handle($pipeline);

        $this->assertTrue(true);
    }

    public function test_invalid_model_citations_fail_the_run_without_saving_a_post(): void
    {
        $run = BlogGenerationRun::query()->create([
            'run_key' => 'manual:invalid-citation',
            'status' => BlogGenerationRun::STATUS_QUEUED,
            'blog_topic_id' => $this->createTopic()->id,
        ]);
        $sources = [[
            'title' => 'Verified update',
            'summary' => 'An approved source summary.',
            'url' => 'https://education.gov.ng/update',
            'published_at' => null,
            'source_host' => 'education.gov.ng',
        ]];
        $this->instance(BlogResearchService::class, Mockery::mock(BlogResearchService::class, function ($mock) use ($sources): void {
            $mock->shouldReceive('fetch')->once()->andReturn(['items' => $sources, 'errors' => []]);
        }));
        $this->instance(BlogDraftWriter::class, Mockery::mock(BlogDraftWriter::class, function ($mock): void {
            $mock->shouldReceive('write')->once()->andReturn([
                'title' => 'Article with a false citation',
                'excerpt' => 'An excerpt.',
                'body_html' => '<p>'.str_repeat('Useful educational detail. ', 150).'</p>',
                'category' => 'Education',
                'tags' => [],
                'meta_title' => 'Article with a false citation',
                'meta_description' => 'An evidence-led article.',
                'source_indices' => [99],
            ]);
        }));

        try {
            app(BlogDraftPipeline::class)->run($run->id);
            $this->fail('Invalid citations must not be accepted.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('source', $exception->getMessage());
        }

        $this->assertSame(BlogGenerationRun::STATUS_FAILED, $run->fresh()->status);
        $this->assertDatabaseCount('blog_posts', 0);
    }

    public function test_review_email_contains_an_authenticated_signed_preview_link(): void
    {
        Mail::fake();
        BlogSetting::setValue('notification_emails', ['editor@example.com']);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $post = $this->createPost($admin);
        $run = BlogGenerationRun::query()->create([
            'run_key' => 'manual:review-email',
            'status' => BlogGenerationRun::STATUS_COMPLETED,
            'blog_post_id' => $post->id,
            'notification_status' => 'queued',
        ]);

        (new SendBlogDraftReviewEmail($run->id))->handle();

        Mail::assertSent(BlogDraftReviewMail::class, fn (BlogDraftReviewMail $mail) => $mail->hasTo('editor@example.com')
            && str_contains($mail->reviewUrl, 'signature='));
        $mail = Mail::sent(BlogDraftReviewMail::class)->first();
        $this->assertNotNull($mail);
        $this->get($mail->reviewUrl)->assertRedirect(route('login'));
        $this->actingAs($admin)->get($mail->reviewUrl)->assertOk()->assertSee('Private article body');
        $this->assertSame('sent', $run->fresh()->notification_status);
    }

    public function test_facebook_publication_uses_one_dedicated_job_and_records_the_page_post_id(): void
    {
        Queue::fake();
        config([
            'blog.facebook_enabled' => true,
            'services.facebook.page_id' => '123456789',
            'services.facebook.page_access_token' => 'page-token-secret',
            'services.facebook.graph_api_version' => 'v27.0',
        ]);
        $post = $this->createPost(null, [
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $workflow = app(BlogFacebookWorkflow::class);
        $share = $workflow->queueAfterPublication($post);
        $workflow->queueAfterPublication($post);

        $this->assertSame(BlogFacebookShare::STATUS_QUEUED, $share->status);
        Queue::assertPushedOn('blog', PublishBlogPostToFacebook::class);
        Queue::assertPushedTimes(PublishBlogPostToFacebook::class, 1);
    }

    public function test_facebook_workflow_rejects_future_articles_and_unpublishing_cancels_queued_shares(): void
    {
        Queue::fake();
        config([
            'blog.facebook_enabled' => true,
            'services.facebook.page_id' => '123456789',
            'services.facebook.page_access_token' => 'page-token-secret',
            'services.facebook.graph_api_version' => 'v27.0',
        ]);
        $futurePost = $this->createPost(null, [
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->addHour(),
        ]);

        try {
            app(BlogFacebookWorkflow::class)->queueAfterPublication($futurePost);
            $this->fail('A future article cannot be shared.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('published article', $exception->getMessage());
        }

        $post = $this->createPost(null, [
            'title' => 'Published article to unpublish',
            'slug' => 'published-article-to-unpublish',
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $share = BlogFacebookShare::query()->create([
            'blog_post_id' => $post->id,
            'status' => BlogFacebookShare::STATUS_QUEUED,
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Livewire::actingAs($admin)->test(BlogStudio::class)
            ->call('unpublish', $post->id)
            ->assertHasNoErrors();

        $this->assertSame(BlogFacebookShare::STATUS_CANCELLED, $share->fresh()->status);
    }

    public function test_facebook_publisher_sends_a_link_post_with_server_side_page_token(): void
    {
        config([
            'blog.facebook_enabled' => true,
            'services.facebook.page_id' => '123456789',
            'services.facebook.page_access_token' => 'page-token-secret',
            'services.facebook.graph_api_version' => 'v27.0',
        ]);
        Http::fake([
            'graph.facebook.com/v27.0/123456789/feed' => Http::response(['id' => '123456789_987654321']),
        ]);
        $post = $this->createPost(null, [
            'title' => 'MephEd shares an education guide',
            'excerpt' => 'A practical resource.',
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $share = BlogFacebookShare::query()->create([
            'blog_post_id' => $post->id,
            'status' => BlogFacebookShare::STATUS_QUEUED,
        ]);

        app(BlogFacebookPublisher::class)->publish($share->id);

        $this->assertSame(BlogFacebookShare::STATUS_PUBLISHED, $share->fresh()->status);
        $this->assertSame('123456789_987654321', $share->fresh()->meta_post_id);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://graph.facebook.com/v27.0/123456789/feed'
            && $request->hasHeader('Authorization', 'Bearer page-token-secret')
            && $request['link'] === route('blog.show', $post->slug)
            && $request['published'] === true);
    }

    public function test_ambiguous_facebook_delivery_is_not_retried_to_avoid_duplicate_posts(): void
    {
        config([
            'blog.facebook_enabled' => true,
            'services.facebook.page_id' => '123456789',
            'services.facebook.page_access_token' => 'page-token-secret',
            'services.facebook.graph_api_version' => 'v27.0',
        ]);
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Temporary server error']], 500)]);
        $post = $this->createPost(null, [
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $share = BlogFacebookShare::query()->create([
            'blog_post_id' => $post->id,
            'status' => BlogFacebookShare::STATUS_QUEUED,
        ]);
        $publisher = app(BlogFacebookPublisher::class);

        $publisher->publish($share->id);
        $publisher->publish($share->id);

        $this->assertSame(BlogFacebookShare::STATUS_UNCERTAIN, $share->fresh()->status);
        Http::assertSentCount(1);
    }

    public function test_facebook_publisher_cancels_delivery_for_an_unpublished_article(): void
    {
        Http::fake();
        $post = $this->createPost();
        $share = BlogFacebookShare::query()->create([
            'blog_post_id' => $post->id,
            'status' => BlogFacebookShare::STATUS_QUEUED,
        ]);

        app(BlogFacebookPublisher::class)->publish($share->id);

        $this->assertSame(BlogFacebookShare::STATUS_CANCELLED, $share->fresh()->status);
        Http::assertNothingSent();
    }

    private function createPost(?User $author = null, array $attributes = []): BlogPost
    {
        $author ??= User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        return BlogPost::query()->create(array_merge([
            'title' => 'Private article',
            'slug' => 'private-education-guide',
            'excerpt' => 'A private draft.',
            'body' => '<p>Private article body</p>',
            'status' => BlogPost::STATUS_DRAFT,
            'author_id' => $author->id,
            'editor_id' => $author->id,
        ], $attributes));
    }

    private function createTopic(array $attributes = []): BlogTopic
    {
        return BlogTopic::query()->create(array_merge([
            'title' => 'Practical learning for Nigerian schools',
            'topic_type' => BlogTopic::TYPE_PRACTICAL,
            'angle' => 'Explain how educators can apply evidence in a classroom.',
            'status' => BlogTopic::STATUS_APPROVED,
            'approved_at' => now(),
        ], $attributes));
    }
}
