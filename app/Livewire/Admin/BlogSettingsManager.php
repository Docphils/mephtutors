<?php

namespace App\Livewire\Admin;

use App\Models\BlogSetting;
use App\Models\User;
use App\Services\BlogTavilyCreditBudget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Blog Settings | MephEd')]
class BlogSettingsManager extends Component
{
    public string $dailyTime = '04:00';

    public string $notificationEmails = '';

    public string $contentWordTarget = '700';

    public string $topicBatchSize = '10';

    public string $topicLowWatermark = '3';

    public string $tavilyMonthlyCreditLimit = '1500';

    public string $tavilyMaxResults = '10';

    public string $tavilySearchDepth = 'basic';

    public string $tavilyCurrentAffairsRange = 'month';

    public string $preferredResearchDomains = '';

    public string $defaultAuthorId = '';

    public function mount(): void
    {
        Gate::authorize('Admin');
        $this->dailyTime = (string) BlogSetting::getValue('daily_time', '04:00');
        $emails = BlogSetting::getValue('notification_emails', []);
        $this->notificationEmails = is_array($emails) ? implode(', ', $emails) : '';
        $this->contentWordTarget = (string) BlogSetting::getValue('content_word_target', config('blog.default_content_word_target'));
        $this->topicBatchSize = (string) BlogSetting::getValue('topic_batch_size', config('blog.default_topic_batch_size'));
        $this->topicLowWatermark = (string) BlogSetting::getValue('topic_low_watermark', config('blog.default_topic_low_watermark'));
        $this->tavilyMonthlyCreditLimit = (string) BlogSetting::getValue('tavily_monthly_credit_limit', config('blog.default_tavily_monthly_credit_limit'));
        $this->tavilyMaxResults = (string) BlogSetting::getValue('tavily_max_results', config('blog.default_tavily_max_results'));
        $this->tavilySearchDepth = (string) BlogSetting::getValue('tavily_search_depth', config('blog.default_tavily_search_depth'));
        $this->tavilyCurrentAffairsRange = (string) BlogSetting::getValue('tavily_current_affairs_time_range', config('blog.default_tavily_current_affairs_time_range'));
        $preferredDomains = BlogSetting::getValue('preferred_research_domains', []);
        $this->preferredResearchDomains = is_array($preferredDomains) ? implode("\n", $preferredDomains) : '';
        $this->defaultAuthorId = (string) BlogSetting::getValue('default_author_id', auth()->id() ?? '');
    }

    public function save(): void
    {
        Gate::authorize('Admin');
        $data = $this->validate([
            'dailyTime' => ['required', 'date_format:H:i'],
            'notificationEmails' => ['nullable', 'string', 'max:2000'],
            'contentWordTarget' => ['required', 'integer', 'between:300,2000'],
            'topicBatchSize' => ['required', 'integer', 'between:3,20'],
            'topicLowWatermark' => ['required', 'integer', 'between:0,10'],
            'tavilyMonthlyCreditLimit' => ['required', 'integer', 'between:1,1500'],
            'tavilyMaxResults' => ['required', 'integer', 'between:1,20'],
            'tavilySearchDepth' => ['required', Rule::in(['basic', 'advanced'])],
            'tavilyCurrentAffairsRange' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
            'preferredResearchDomains' => ['nullable', 'string', 'max:5000'],
            'defaultAuthorId' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'admin')->whereNotNull('email_verified_at')],
        ]);

        $emails = collect(preg_split('/[,;\r\n]+/', $data['notificationEmails']) ?: [])
            ->map(fn (string $email) => mb_strtolower(trim($email)))
            ->filter()
            ->unique()
            ->values();

        if ($emails->count() > 10) {
            throw ValidationException::withMessages([
                'notificationEmails' => 'Add no more than 10 notification addresses.',
            ]);
        }

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw ValidationException::withMessages([
                    'notificationEmails' => 'Enter valid email addresses separated by commas or new lines.',
                ]);
            }
        }

        $preferredDomains = collect(preg_split('/[\r\n,;]+/', $data['preferredResearchDomains']) ?: [])
            ->map(fn (string $domain) => mb_strtolower(trim($domain)))
            ->filter()
            ->unique()
            ->values();
        if ($preferredDomains->count() > 30) {
            throw ValidationException::withMessages([
                'preferredResearchDomains' => 'Add no more than 30 preferred source domains.',
            ]);
        }
        foreach ($preferredDomains as $domain) {
            if (! $this->isValidDomain($domain)) {
                throw ValidationException::withMessages([
                    'preferredResearchDomains' => 'Use domain names only (for example, education.gov.ng), without a scheme, path, wildcard, or IP address.',
                ]);
            }
        }

        DB::transaction(function () use ($data, $emails, $preferredDomains): void {
            BlogSetting::setValue('daily_time', $data['dailyTime']);
            BlogSetting::setValue('timezone', config('blog.timezone'));
            BlogSetting::setValue('notification_emails', $emails->all());
            BlogSetting::setValue('preferred_research_domains', $preferredDomains->all());
            BlogSetting::setValue('content_word_target', (int) $data['contentWordTarget']);
            BlogSetting::setValue('topic_batch_size', (int) $data['topicBatchSize']);
            BlogSetting::setValue('topic_low_watermark', (int) $data['topicLowWatermark']);
            BlogSetting::setValue('tavily_monthly_credit_limit', (int) $data['tavilyMonthlyCreditLimit']);
            BlogSetting::setValue('tavily_max_results', (int) $data['tavilyMaxResults']);
            BlogSetting::setValue('tavily_search_depth', $data['tavilySearchDepth']);
            BlogSetting::setValue('tavily_current_affairs_time_range', $data['tavilyCurrentAffairsRange']);
            BlogSetting::setValue('default_author_id', (int) $data['defaultAuthorId']);
        });
        session()->flash('success', 'Blog schedule, authorship, topic, content, and research settings saved.');
    }

    private function isValidDomain(string $domain): bool
    {
        return $domain !== ''
            && str_contains($domain, '.')
            && ! str_contains($domain, '*')
            && ! str_contains($domain, '/')
            && ! str_contains($domain, ':')
            && ! str_ends_with($domain, '.')
            && ! filter_var($domain, FILTER_VALIDATE_IP)
            && filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    public function render()
    {
        Gate::authorize('Admin');

        $authors = User::query()
            ->where('role', 'admin')
            ->whereNotNull('email_verified_at')
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'label' => $user->name.' ('.$user->email.')',
            ]);

        return view('livewire.admin.blog.settings', [
            'authors' => $authors,
            'tavilyCreditsUsed' => app(BlogTavilyCreditBudget::class)->currentMonthUsage(),
        ]);
    }
}
