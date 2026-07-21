<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Crm;
use App\Models\Payment;
use App\Models\ProgrammeEnquiry;
use App\Models\Enrollee;
use App\Mail\EnrolleeConfirmation;
use Illuminate\Support\Facades\Mail;
use App\Services\PaystackService;
use App\Support\InterventionStatusNotifier;
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

            //Enrollment Callback
            if (($metadata['payment_for'] ?? null) === 'bootcamp_enrollment') {
                $enrollee = Enrollee::with('cohort.service', 'cohort.serviceItem')
                    ->findOrFail($metadata['enrollee_id']);

                if (($enrollee->meta['payment_status'] ?? null) !== 'paid') {
                    $enrollee->update([
                        'status' => 'confirmed',
                        'confirmed_at' => now(),
                        'meta' => array_merge($enrollee->meta ?? [], [
                            'payment_status' => 'paid',
                            'payment_reference' => $reference,
                        ]),
                    ]);

                    try {
                        Mail::to($enrollee->email)->send(new EnrolleeConfirmation($enrollee));
                    } catch (\Exception $e) {
                        Log::error('Enrollee confirmation mail failed: ' . $e->getMessage());
                    }
                }

                return redirect()->route('bootcamp.enrollment.success', ['enrollee' => $enrollee->id])
                    ->with('success', 'Payment successful. Your spot is confirmed.');
            }


            //Programme request callback
            if (($metadata['payment_for'] ?? null) === 'programme_request') {
                $programmeEnquiry = ProgrammeEnquiry::findOrFail($metadata['programme_enquiry_id']);
                $wasPaid = ($programmeEnquiry->payment_status ?? 'pending') === 'paid';
                $wasInProgress = ($programmeEnquiry->status ?? null) === 'in_progress';
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

                if (! $wasPaid || ! $wasInProgress) {
                    InterventionStatusNotifier::notifyClient(
                        $programmeEnquiry->loadMissing(['programme', 'user.userProfile']),
                        InterventionStatusNotifier::CLIENT_ACTIVATED,
                        ['note' => 'Your payment was confirmed successfully.']
                    );
                    if ($assignment) {
                        $assignment->loadMissing(['tutor.tutorProfile', 'tutor.userProfile']);
                        InterventionStatusNotifier::notifyTutor(
                            $programmeEnquiry->loadMissing(['programme', 'user.userProfile']),
                            $assignment->tutor,
                            InterventionStatusNotifier::TUTOR_ASSIGNED,
                            [
                                'note' => 'Client payment has been confirmed and this intervention is now active.',
                                'payment_status' => 'pending',
                            ]
                        );
                    }
                }

                return redirect()->route('client.programmeRequests.manager')
                    ->with('success', 'Payment successful. Your intervention request is now active.');
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
                ->with('error', 'Payment was not successful. Please try again from your intervention requests dashboard.');
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

        if ($app === 'mephed' && ($metadata['payment_for'] ?? null) === 'enrollment') {
            $enrollee = Enrollee::find($metadata['enrollee_id'] ?? 0);

            if ($enrollee && ($enrollee->meta['payment_status'] ?? null) !== 'paid') {
                $enrollee->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'meta' => array_merge($enrollee->meta ?? [], [
                        'payment_status' => 'paid',
                        'payment_reference' => $data['reference'] ?? ($enrollee->meta['payment_reference'] ?? null),
                    ]),
                ]);

                try {
                    Mail::to($enrollee->email)->send(new EnrolleeConfirmation($enrollee->load('cohort.service', 'cohort.serviceItem')));
                } catch (\Exception $e) {
                    Log::error('Enrollee confirmation mail failed: ' . $e->getMessage());
                }
            }

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
                $wasPaid = ($programmeEnquiry->payment_status ?? 'pending') === 'paid';
                $wasInProgress = ($programmeEnquiry->status ?? null) === 'in_progress';
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

                if (! $wasPaid || ! $wasInProgress) {
                    InterventionStatusNotifier::notifyClient(
                        $programmeEnquiry->loadMissing(['programme', 'user.userProfile']),
                        InterventionStatusNotifier::CLIENT_ACTIVATED,
                        ['note' => 'Your payment was confirmed successfully.']
                    );
                    if ($assignment) {
                        $assignment->loadMissing(['tutor.tutorProfile', 'tutor.userProfile']);
                        InterventionStatusNotifier::notifyTutor(
                            $programmeEnquiry->loadMissing(['programme', 'user.userProfile']),
                            $assignment->tutor,
                            InterventionStatusNotifier::TUTOR_ASSIGNED,
                            [
                                'note' => 'Client payment has been confirmed and this intervention is now active.',
                                'payment_status' => 'pending',
                            ]
                        );
                    }
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
