<?php 

namespace App\Services;

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
                    'booking_id' => $booking->id,
                ],
            ])->json();

        if (!$response['status']) {
            throw new \Exception('Payment initialization failed.');
        }

        $booking->update([
            'payment_reference' => $reference,
            'client_payment_status' => 'Pending',
        ]);

        return $response['data']['authorization_url'];
    }

    public function verifyPayment(string $reference)
    {
        return Http::withToken(config('services.paystack.secret'))
            ->get("{$this->baseUrl}/transaction/verify/{$reference}")
            ->json();
    }
}