<?php

namespace App\Livewire\Ebooks;

use App\Enums\EbookStatus;
use App\Models\Ebook;
use App\Models\EbookEvent;
use App\Models\EbookPolicy;
use App\Services\EbookPaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class EbookLandingPage extends Component
{
    public Ebook $ebook;

    public string $buyerName = '';

    public string $buyerEmail = '';

    public string $buyerPhone = '';

    public string $website = '';

    public string $checkoutAuthorizationUrl = '';

    public bool $checkoutProcessing = false;

    public function mount(string $slug): void
    {
        $this->ebook = Ebook::query()->where('slug', $slug)->where('status', EbookStatus::Published)->firstOrFail();
        EbookEvent::query()->create([
            'type' => 'landing_view',
            'ebook_id' => $this->ebook->id,
            'referrer' => $this->safeReferrer(),
            'utm_source' => $this->utmValue('utm_source'),
            'utm_medium' => $this->utmValue('utm_medium'),
            'utm_campaign' => $this->utmValue('utm_campaign'),
        ]);

        if (! session()->has('ebook_attribution')) {
            session()->put('ebook_attribution', [
                'referrer' => $this->safeReferrer(),
                'utm_source' => $this->utmValue('utm_source'),
                'utm_medium' => $this->utmValue('utm_medium'),
                'utm_campaign' => $this->utmValue('utm_campaign'),
            ]);
        }
    }

    public function beginCheckout(EbookPaymentService $payments): void
    {
        $this->buyerName = Str::squish($this->buyerName);
        $this->buyerEmail = mb_strtolower(trim($this->buyerEmail));
        $this->buyerPhone = trim($this->buyerPhone);

        $this->validate([
            'buyerName' => ['required', 'string', 'min:2', 'max:160'],
            'buyerEmail' => ['required', 'string', 'email:rfc', 'max:254'],
            'buyerPhone' => ['nullable', 'string', 'regex:/^\\+?[0-9][0-9\\s().-]{6,24}$/'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        $email = $this->buyerEmail;
        $limitKey = 'ebook-checkout:'.$this->ebook->id.':'.hash('sha256', $email);
        if (RateLimiter::tooManyAttempts($limitKey, config('ebooks.checkout_attempts_per_email_per_minute', 5))) {
            $this->addError('buyerEmail', 'Too many checkout attempts. Please wait a minute and try again.');

            return;
        }
        RateLimiter::hit($limitKey, 60);

        $this->checkoutProcessing = true;
        try {
            $transaction = $payments->startCheckout($this->ebook, [
                'name' => $this->buyerName,
                'email' => $email,
                'phone' => trim($this->buyerPhone),
            ], (array) session('ebook_attribution', []));

            $this->checkoutAuthorizationUrl = $transaction['authorization_url'];
            $this->dispatch(
                'ebook-checkout-initialized',
                accessCode: $transaction['access_code'],
                authorizationUrl: $transaction['authorization_url'],
            );
        } catch (\Throwable $exception) {
            Log::warning('Guest eBook checkout could not be initialized.', [
                'ebook_id' => $this->ebook->id,
                'email_hash' => hash('sha256', $email),
                'exception' => $exception,
            ]);
            $this->addError('checkout', 'Checkout could not be started. Please try again; you will only be charged after authorizing payment.');
        } finally {
            $this->checkoutProcessing = false;
        }
    }

    private function utmValue(string $key): ?string
    {
        $value = request()->query($key);

        return is_string($value) && trim($value) !== '' ? mb_substr(trim($value), 0, 255) : null;
    }

    private function safeReferrer(): ?string
    {
        $parts = parse_url((string) request()->headers->get('referer', ''));
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host']) || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $referrer = strtolower($parts['scheme']).'://'.$parts['host'];
        if (isset($parts['port'])) {
            $referrer .= ':'.$parts['port'];
        }
        $referrer .= $parts['path'] ?? '';

        return mb_substr($referrer, 0, 2048);
    }

    public function render()
    {
        $title = $this->ebook->meta_title ?: $this->ebook->title.' | MephEd eBooks';
        $description = $this->ebook->meta_description ?: $this->ebook->subtitle ?: $this->ebook->title.' by '.$this->ebook->author_name.'. Published by '.$this->ebook->publisher_name.'.';

        $pageUrl = route('ebooks.show', ['slug' => $this->ebook->slug]);
        $graph = [
            [
                '@type' => 'Book', '@id' => $pageUrl.'#book', 'name' => $this->ebook->title,
                'description' => $this->ebook->description ?: $description,
                'author' => ['@type' => 'Person', 'name' => $this->ebook->author_name],
                'publisher' => ['@type' => 'Organization', 'name' => $this->ebook->publisher_name],
                'bookFormat' => 'https://schema.org/EBook', 'inLanguage' => 'en-NG', 'url' => $pageUrl,
                'image' => $this->ebook->cover_path ? asset('storage/'.$this->ebook->cover_path) : asset('images/MephEd.png'),
                'offers' => ['@type' => 'Offer', 'priceCurrency' => $this->ebook->currency, 'price' => number_format($this->ebook->price_kobo / 100, 2, '.', ''), 'availability' => 'https://schema.org/InStock', 'url' => $pageUrl],
            ],
            ['@type' => 'Organization', '@id' => url('/').'#organization', 'name' => 'MephEd', 'url' => url('/')],
            ['@type' => 'BreadcrumbList', 'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'eBooks', 'item' => route('ebooks.index')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $this->ebook->title, 'item' => $pageUrl],
            ]],
        ];
        if ($this->ebook->faq) {
            $graph[] = ['@type' => 'FAQPage', 'mainEntity' => array_map(fn (array $item) => [
                '@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
            ], $this->ebook->faq)];
        }
        $structuredData = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return view('livewire.ebooks.landing-page', [
            'ebookSalesEnabled' => config('ebooks.sales_enabled') && EbookPolicy::requiredPoliciesArePublished(),
        ])->title($title)->layoutData([
            'metaDescription' => $description,
            'canonicalUrl' => route('ebooks.show', ['slug' => $this->ebook->slug]),
            'ogType' => 'book',
            'ogImage' => $this->ebook->cover_path ? asset('storage/'.$this->ebook->cover_path) : asset('images/MephEd.png'),
            'structuredData' => $structuredData,
            'paystackPublicKey' => config('services.paystack.public'),
        ]);
    }
}
