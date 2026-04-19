<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Crm;
use App\Models\Payment;
use App\Models\ProgrammeEnquiry;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PaystackController extends Controller
{
    public function callback(Request $request, PaystackService $paystack)
    {
        $reference = $request->reference;

        $response = $paystack->verifyPayment($reference);
        $metadata = $response['data']['metadata'] ?? [];

        if (($response['data']['status'] ?? null) === 'success') {
            if (($metadata['payment_for'] ?? null) === 'crm') {
                $crm = Crm::findOrFail($metadata['crm_id']);

                $crm->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $reference,
                    'paid_at' => now(),
                ]);

                return redirect()->route('client.crm.manager')
                    ->with('success', 'Payment successful. Institution contract has been funded.');
            }

            if (($metadata['payment_for'] ?? null) === 'programme_request') {
                $programmeEnquiry = ProgrammeEnquiry::findOrFail($metadata['programme_enquiry_id']);
                $programmeEnquiry->update([
                    'payment_status' => 'paid',
                    'status' => 'in_progress',
                    'payment_reference' => $reference,
                ]);

                // Activate any existing assignment so tutor can see the active request
                $assignment = $programmeEnquiry->assignments()->whereIn('status', ['assigned','accepted'])->latest()->first();
                if ($assignment) {
                    $assignment->update([
                        'status' => 'active',
                        'started_at' => $assignment->started_at ?: now(),
                    ]);

                    Payment::query()->updateOrCreate(
                        ['programme_enquiry_assignment_id' => $assignment->id],
                        [
                            'tutor_id' => $assignment->tutor_id,
                            'booking_id' => null,
                            'amount' => round(max((float) ($programmeEnquiry->price_quote ?? 0), 0) * 0.7, 2),
                            'status' => 'Pending',
                        ]
                    );
                }

                return redirect()->route('client.programmeRequests.manager')
                    ->with('success', 'Payment successful. Your programme request is now active.');
            }

            $bookingId = $metadata['booking_id'] ?? null;
            abort_unless($bookingId, 404);

            $booking = Booking::findOrFail($bookingId);

            $booking->update([
                'client_payment_status' => 'Paid',
                'status' => 'Active',
            ]);

            return redirect()->route('client.lessons', $booking)
                ->with('success', 'Payment successful. Lesson activated.');
        }

        if (($metadata['payment_for'] ?? null) === 'programme_request') {
            return redirect()->route('client.programmeRequests.manager')
                ->with('error', 'Payment was not successful. Please try again from your programme requests dashboard.');
        }

        return redirect()->route('client.lessons')->with('error', 'Payment failed.');
    }

    public function webhook(Request $request)
    {
        $signature = (string) $request->header('x-paystack-signature', '');

        $rawPayload = $request->getContent();

        $hash = hash_hmac(
            'sha512',
            $rawPayload,
            config('services.paystack.secret')
        );

        if (! hash_equals($hash, $signature)) {
            abort(403);
        }

        if ($request->input('event') !== 'charge.success') {
            return response()->json(['status' => 'ok']);
        }

        $data = (array) $request->input('data', []);
        $metadata = (array) ($data['metadata'] ?? []);
        $app = (string) ($metadata['app'] ?? 'mephed');

        if ($app === 'solar_sapient') {
            $this->forwardToSolarSapient($rawPayload, $signature);

            return response()->json(['status' => 'ok']);
        }

        // Default: mephed flow
        if (($metadata['payment_for'] ?? null) === 'crm') {
            $crm = Crm::find($metadata['crm_id'] ?? 0);

            if ($crm && $crm->payment_status !== 'paid') {
                $crm->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $data['reference'] ?? $crm->payment_reference,
                    'paid_at' => now(),
                ]);
            }

            return response()->json(['status' => 'ok']);
        }

        if (($metadata['payment_for'] ?? null) === 'programme_request') {
            $programmeEnquiry = ProgrammeEnquiry::find($metadata['programme_enquiry_id'] ?? 0);

            if ($programmeEnquiry && $programmeEnquiry->payment_status !== 'paid') {
                $programmeEnquiry->update([
                    'payment_status' => 'paid',
                    'status' => 'in_progress',
                    'payment_reference' => $data['reference'] ?? $programmeEnquiry->payment_reference,
                ]);

                $assignment = $programmeEnquiry->assignments()->whereIn('status', ['assigned','accepted'])->latest()->first();
                if ($assignment) {
                    $assignment->update([
                        'status' => 'active',
                        'started_at' => $assignment->started_at ?: now(),
                    ]);

                    Payment::query()->updateOrCreate(
                        ['programme_enquiry_assignment_id' => $assignment->id],
                        [
                            'tutor_id' => $assignment->tutor_id,
                            'booking_id' => null,
                            'amount' => round(max((float) ($programmeEnquiry->price_quote ?? 0), 0) * 0.7, 2),
                            'status' => 'Pending',
                        ]
                    );
                }
            }

            return response()->json(['status' => 'ok']);
        }

        $bookingId = $metadata['booking_id'] ?? null;
        if (! $bookingId) {
            return response()->json(['status' => 'ok']);
        }

        $booking = Booking::find($bookingId);

        if ($booking && $booking->client_payment_status !== 'Paid') {
            $booking->update([
                'client_payment_status' => 'Paid',
                'status' => 'Active',
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function forwardToSolarSapient(string $rawPayload, string $signature): void
    {
        $url = config('services.solar_sapient.webhook_url');

        if (! $url) {
            throw new \RuntimeException('Solar Sapient webhook URL is not configured.');
        }

        $response = Http::timeout(15)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-paystack-signature' => $signature,
            ])
            ->withBody($rawPayload, 'application/json')
            ->post($url);

        if (! $response->successful()) {
            Log::error('Solar Sapient webhook forward failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Failed to forward webhook to Solar Sapient.');
        }
    }
}
