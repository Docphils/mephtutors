<?php

namespace App\Livewire\Admin;

use App\Models\BlogSetting;
use Illuminate\Support\Facades\Gate;
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

    public string $approvedSourceDomains = '';

    public string $sourceFeedUrls = '';

    public function mount(): void
    {
        Gate::authorize('Admin');
        $this->dailyTime = (string) BlogSetting::getValue('daily_time', '04:00');
        $emails = BlogSetting::getValue('notification_emails', []);
        $this->notificationEmails = is_array($emails) ? implode(', ', $emails) : '';
        $domains = BlogSetting::getValue('source_domains', config('blog.source_domains', []));
        $storedFeeds = BlogSetting::getValue('source_feed_urls');
        $feeds = is_array($storedFeeds) ? $storedFeeds : config('blog.source_feed_urls', []);
        $this->approvedSourceDomains = is_array($domains) ? implode("\n", $domains) : '';
        $this->sourceFeedUrls = is_array($feeds) ? implode("\n", $feeds) : '';
    }

    public function save(): void
    {
        Gate::authorize('Admin');
        $data = $this->validate([
            'dailyTime' => ['required', 'date_format:H:i'],
            'notificationEmails' => ['nullable', 'string', 'max:2000'],
            'approvedSourceDomains' => ['nullable', 'string', 'max:5000'],
            'sourceFeedUrls' => ['nullable', 'string', 'max:10000'],
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

        $domains = collect(preg_split('/[\r\n,;]+/', $data['approvedSourceDomains']) ?: [])
            ->map(fn (string $domain) => mb_strtolower(trim($domain)))
            ->filter()
            ->unique()
            ->values();

        if ($domains->isEmpty() || $domains->count() > 50) {
            throw ValidationException::withMessages([
                'approvedSourceDomains' => 'Keep between 1 and 50 exact source domains.',
            ]);
        }

        foreach ($domains as $domain) {
            if (! $this->isValidDomain($domain)) {
                throw ValidationException::withMessages([
                    'approvedSourceDomains' => 'Use host names only (for example, education.gov.ng), without schemes, paths, wildcards, or IP addresses.',
                ]);
            }
        }

        $feeds = collect(preg_split('/[\r\n]+/', $data['sourceFeedUrls']) ?: [])
            ->map(fn (string $url) => trim($url))
            ->filter()
            ->unique()
            ->values();

        if ($feeds->count() > 15) {
            throw ValidationException::withMessages([
                'sourceFeedUrls' => 'Add no more than 15 RSS or Atom feed URLs.',
            ]);
        }

        foreach ($feeds as $url) {
            $host = $this->validFeedHost($url);
            if ($host === null || ! $domains->contains($host)) {
                throw ValidationException::withMessages([
                    'sourceFeedUrls' => 'Each feed must use HTTPS and a host covered by the approved source domains above. Redirects and non-public destinations are blocked.',
                ]);
            }
        }

        BlogSetting::setValue('daily_time', $data['dailyTime']);
        BlogSetting::setValue('timezone', config('blog.timezone'));
        BlogSetting::setValue('notification_emails', $emails->all());
        BlogSetting::setValue('source_domains', $domains->all());
        BlogSetting::setValue('source_feed_urls', $feeds->all());
        session()->flash('success', 'Blog schedule, notification, and research source settings saved.');
    }

    private function isValidDomain(string $domain): bool
    {
        return ! str_contains($domain, '*')
            && ! str_contains($domain, '/')
            && ! str_contains($domain, ':')
            && ! str_ends_with($domain, '.')
            && ! filter_var($domain, FILTER_VALIDATE_IP)
            && str_contains($domain, '.')
            && filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    private function validFeedHost(string $url): ?string
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($url);
        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['fragment'])
            || (isset($parts['port']) && $parts['port'] !== 443)) {
            return null;
        }

        parse_str((string) ($parts['query'] ?? ''), $query);
        foreach (array_keys($query) as $name) {
            if (preg_match('/token|secret|signature|auth|password|credential|api[_-]?key/i', (string) $name)) {
                return null;
            }
        }

        $host = mb_strtolower((string) ($parts['host'] ?? ''));

        return $this->isValidDomain($host) ? $host : null;
    }

    public function render()
    {
        Gate::authorize('Admin');

        return view('livewire.admin.blog.settings');
    }
}
