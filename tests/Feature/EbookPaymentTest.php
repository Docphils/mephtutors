<?php

namespace Tests\Feature;

use App\Enums\EbookOrderStatus;
use App\Enums\EbookStatus;
use App\Jobs\FulfillEbookOrder;
use App\Livewire\Ebooks\EbookLandingPage;
use App\Models\Ebook;
use App\Models\EbookEvent;
use App\Models\EbookOrder;
use App\Models\EbookPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class EbookPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        config([
            'ebooks.sales_enabled' => true,
            'services.paystack.public' => 'pk_test_example',
            'services.paystack.secret' => 'sk_test_example',
        ]);
    }

    public function test_checkout_uses_current_database_amount_and_is_available_to_guests(): void
    {
        $ebook = $this->publishedEbook();
        Http::fake(function ($request) use ($ebook) {
            if (str_contains($request->url(), '/transaction/initialize')) {
                $data = $request->data();
                $this->assertSame((string) $ebook->price_kobo, $data['amount']);
                $this->assertSame('NGN', $data['currency']);
                $this->assertSame('buyer@example.com', $data['email']);
                $this->assertSame('ebook', $data['metadata']['payment_for']);
                $this->assertSame(route('paystack.callback'), $data['callback_url']);

                return Http::response([
                    'status' => true,
                    'data' => [
                        'access_code' => 'access-example',
                        'authorization_url' => 'https://checkout.paystack.test/example',
                        'reference' => $data['reference'],
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $response = $this->get(route('ebooks.show', ['slug' => $ebook->slug]));
        $response->assertOk()->assertSee('no account needed')->assertSee('buyerEmail', false);

        Livewire::test(EbookLandingPage::class, ['slug' => $ebook->slug])
            ->set('buyerName', 'Guest Buyer')
            ->set('buyerEmail', ' BUYER@EXAMPLE.COM ')
            ->call('beginCheckout')
            ->assertHasNoErrors()
            ->assertDispatched('ebook-checkout-initialized', accessCode: 'access-example');

        $order = EbookOrder::query()->firstOrFail();
        $this->assertSame('buyer@example.com', $order->buyer_email);
        $this->assertSame($ebook->price_kobo, $order->amount_kobo);
        $this->assertSame(EbookOrderStatus::Pending, $order->status);
        $this->assertDatabaseHas('ebook_events', ['type' => 'checkout_started', 'order_id' => $order->id]);
    }

    public function test_ebooks_reuse_the_existing_paystack_callback_and_webhook_routes(): void
    {
        $this->assertSame('/paystack/callback', parse_url(route('paystack.callback'), PHP_URL_PATH));
        $this->assertSame('/paystack/webhook', parse_url(route('paystack.webhook'), PHP_URL_PATH));
        $this->assertFalse(Route::has('ebooks.payments.callback'));
        $this->assertFalse(Route::has('ebooks.payments.webhook'));
    }

    public function test_checkout_is_closed_by_default_release_guard(): void
    {
        config(['ebooks.sales_enabled' => false]);
        $ebook = $this->publishedEbook();

        $this->get(route('ebooks.show', ['slug' => $ebook->slug]))
            ->assertOk()
            ->assertSee('Purchases are not open yet')
            ->assertDontSee('Continue to secure payment');

        Livewire::test(EbookLandingPage::class, ['slug' => $ebook->slug])
            ->set('buyerName', 'Guest Buyer')
            ->set('buyerEmail', 'buyer@example.com')
            ->call('beginCheckout')
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('ebook_orders', 0);
    }

    public function test_checkout_stays_closed_when_any_required_policy_is_unpublished(): void
    {
        $ebook = $this->publishedEbook();
        EbookPolicy::query()->where('slug', 'privacy')->update(['is_published' => false, 'published_at' => null]);

        Livewire::test(EbookLandingPage::class, ['slug' => $ebook->slug])
            ->set('buyerName', 'Guest Buyer')
            ->set('buyerEmail', 'buyer@example.com')
            ->call('beginCheckout')
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('ebook_orders', 0);
    }

    public function test_successful_verification_and_webhook_replay_mark_order_paid_once(): void
    {
        $order = $this->pendingOrder();
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => $this->transactionData($order),
            ], 200),
        ]);

        $this->get(route('paystack.callback', ['reference' => $order->paystack_reference]))
            ->assertRedirectContains('/ebooks/thank-you/'.$order->reference);

        $payload = [
            'event' => 'charge.success',
            'data' => $this->transactionData($order),
        ];
        $this->signedWebhook($payload)->assertOk();
        $this->signedWebhook($payload)->assertOk();

        $this->assertSame(EbookOrderStatus::Paid, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertSame(1, EbookEvent::query()->where('type', 'payment_succeeded')->where('order_id', $order->id)->count());
        $this->assertSame(2, EbookEvent::query()->where('type', 'webhook_received')->where('order_id', $order->id)->count());
        Queue::assertPushed(FulfillEbookOrder::class, 1);
    }

    public function test_wrong_amount_or_currency_never_confirms_payment(): void
    {
        $order = $this->pendingOrder();
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => array_merge($this->transactionData($order), ['amount' => $order->amount_kobo + 1]),
            ], 200),
        ]);

        $this->postJson(route('ebooks.payments.verify'), ['reference' => $order->paystack_reference])
            ->assertOk()
            ->assertJsonPath('paid', false);
        $this->assertSame(EbookOrderStatus::Pending, $order->fresh()->status);
        $this->assertDatabaseHas('ebook_events', ['type' => 'payment_mismatch', 'order_id' => $order->id]);

        $payload = ['event' => 'charge.success', 'data' => array_merge($this->transactionData($order), ['currency' => 'USD'])];
        $this->signedWebhook($payload)->assertOk();
        $this->assertSame(EbookOrderStatus::Pending, $order->fresh()->status);
        $this->assertDatabaseHas('ebook_events', ['type' => 'webhook_rejected', 'order_id' => $order->id]);
    }

    public function test_webhook_signature_is_checked_before_processing(): void
    {
        $order = $this->pendingOrder();
        $body = json_encode(['event' => 'charge.success', 'data' => $this->transactionData($order)], JSON_THROW_ON_ERROR);

        $this->call('POST', route('paystack.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => 'bad-signature',
        ], $body)->assertForbidden();

        $this->assertSame(EbookOrderStatus::Pending, $order->fresh()->status);
        $this->assertDatabaseMissing('ebook_events', ['type' => 'webhook_received', 'order_id' => $order->id]);
    }

    public function test_failed_verification_transitions_pending_order_to_failed(): void
    {
        $order = $this->pendingOrder();
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => array_merge($this->transactionData($order), ['status' => 'abandoned']),
            ], 200),
        ]);

        $this->postJson(route('ebooks.payments.verify'), ['reference' => $order->paystack_reference])
            ->assertOk()
            ->assertJsonPath('status', 'failed');

        $this->assertSame(EbookOrderStatus::Failed, $order->fresh()->status);
    }

    private function publishedEbook(): Ebook
    {
        return Ebook::factory()->create([
            'status' => EbookStatus::Published,
            'price_kobo' => 123456,
            'currency' => 'NGN',
        ]);
    }

    private function pendingOrder(): EbookOrder
    {
        $ebook = $this->publishedEbook();

        return EbookOrder::factory()->create([
            'ebook_id' => $ebook->id,
            'amount_kobo' => $ebook->price_kobo,
            'currency' => 'NGN',
            'status' => EbookOrderStatus::Pending,
            'paystack_reference' => Str::random(40),
        ]);
    }

    private function transactionData(EbookOrder $order): array
    {
        return [
            'reference' => $order->paystack_reference,
            'amount' => $order->amount_kobo,
            'currency' => $order->currency,
            'status' => 'success',
            'metadata' => [
                'app' => 'mephed',
                'payment_for' => 'ebook',
                'order_reference' => $order->reference,
            ],
        ];
    }

    private function signedWebhook(array $payload)
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $body, 'sk_test_example');

        return $this->call('POST', route('paystack.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ], $body);
    }
}
