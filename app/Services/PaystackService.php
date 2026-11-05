<?php

namespace App\Services;

use App\Models\Cohort;
use App\Models\Crm;
use App\Models\EbookOrder;
use App\Models\Enrollee;
use App\Models\ProgrammeEnquiry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaystackService
{
    protected string $baseUrl = 'https://api.paystack.co';

    public function initializePayment($booking)
    {
        $reference = Str::uuid();

        $response = Http::withToken(config('services.paystack.secret'))
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $booking->client->email,
                'amount' => $booking->amount * 100,
                'reference' => $reference,
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'app' => 'mephed',
                    'payment_for' => 'booking',
                    'booking_id' => $booking->id,
                ],
            ])->json();

        if (! $response['status']) {
            throw new \Exception('Payment initialization failed.');
        }

        $booking->update([
            'payment_reference' => $reference,
            'client_payment_status' => 'Pending',
        ]);

        return $response['data']['authorization_url'];
    }

    public function initializeCrmPayment(Crm $crm)
    {
        if ((float) $crm->quote_amount <= 0) {
            throw new \Exception('CRM quote amount must be greater than zero.');
        }

        $reference = $crm->payment_reference ?: (string) Str::uuid();

        $response = Http::withToken(config('services.paystack.secret'))
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $crm->user->email,
                'amount' => (int) round($crm->quote_amount * 100),
                'reference' => $reference,
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'app' => 'mephed',
                    'payment_for' => 'crm',
                    'crm_id' => $crm->id,
                ],
            ])->json();

        if (! ($response['status'] ?? false)) {
            throw new \Exception($response['message'] ?? 'CRM payment initialization failed.');
        }

        $crm->update([
            'payment_reference' => $reference,
            'payment_link' => $response['data']['authorization_url'],
            'payment_status' => 'pending',
        ]);

        return $response['data']['authorization_url'];
    }

    public function initializeProgrammeEnquiryPayment(ProgrammeEnquiry $programmeEnquiry): string
    {
        if ((float) $programmeEnquiry->price_quote <= 0) {
            throw new \Exception('Programme request quote must be greater than zero.');
        }

        $reference = $programmeEnquiry->payment_reference ?: (string) Str::uuid();

        $response = Http::withToken(config('services.paystack.secret'))
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $programmeEnquiry->user->email,
                'amount' => (int) round($programmeEnquiry->price_quote * 100),
                'reference' => $reference,
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'app' => 'mephed',
                    'payment_for' => 'programme_request',
                    'programme_enquiry_id' => $programmeEnquiry->id,
                ],
            ])->json();

        if (! ($response['status'] ?? false)) {
            throw new \Exception($response['message'] ?? 'Programme payment initialization failed.');
        }

        $programmeEnquiry->update([
            'payment_reference' => $reference,
            'payment_status' => 'pending',
        ]);

        return $response['data']['authorization_url'];
    }

    public function verifyPayment(string $reference)
    {
        return Http::withToken(config('services.paystack.secret'))
            ->get("{$this->baseUrl}/transaction/verify/{$reference}")
            ->json();
    }

    public function initializeEbookPayment(EbookOrder $order): array
    {
        if (! config('services.paystack.secret')) {
            throw new \RuntimeException('Paystack secret key is not configured.');
        }

        $response = Http::withToken(config('services.paystack.secret'))
            ->connectTimeout(5)
            ->timeout(15)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $order->buyer_email,
                'amount' => (string) $order->amount_kobo,
                'currency' => $order->currency,
                'reference' => $order->paystack_reference,
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'app' => 'mephed',
                    'payment_for' => 'ebook',
                    'order_reference' => $order->reference,
                ],
            ])->json();

        if (! is_array($response)
            || ! ($response['status'] ?? false)
            || ! is_string($response['data']['access_code'] ?? null)
            || ! is_string($response['data']['authorization_url'] ?? null)
            || ($response['data']['reference'] ?? null) !== $order->paystack_reference) {
            throw new \RuntimeException('eBook payment initialization failed.');
        }

        return [
            'access_code' => $response['data']['access_code'],
            'reference' => $order->paystack_reference,
            'authorization_url' => $response['data']['authorization_url'],
        ];
    }

    // Bootcamp payment initialization
    public function initializeEnrolleePayment(Enrollee $enrollee, Cohort $cohort): string
    {
        $fee = (float) ($cohort->fee ?? 0);

        if ($fee <= 0) {
            throw new \Exception('Cohort fee must be greater than zero.');
        }

        $meta = $enrollee->meta ?? [];
        $reference = $meta['payment_reference'] ?? (string) Str::uuid();

        $response = Http::withToken(config('services.paystack.secret'))
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $enrollee->email,
                'amount' => (int) round($fee * 100),
                'reference' => $reference,
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'app' => 'mephed',
                    'payment_for' => 'bootcamp_enrollment',
                    'enrollee_id' => $enrollee->id,
                    'cohort_id' => $cohort->id,
                    'cohort_code' => $cohort->code,
                    'service_item_slug' => $cohort->serviceItem->slug ?? null,
                ],
            ])->json();

        if (! ($response['status'] ?? false)) {
            throw new \Exception($response['message'] ?? 'Enrollment payment initialization failed.');
        }

        $enrollee->update([
            'meta' => array_merge($meta, [
                'payment_reference' => $reference,
                'payment_status' => 'pending',
            ]),
        ]);

        return $response['data']['authorization_url'];
    }
}
