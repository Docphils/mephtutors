<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class PaystackController extends Controller
{
    public function callback(Request $request, PaystackService $paystack)
    {
        $reference = $request->reference;

        $response = $paystack->verifyPayment($reference);

        if ($response['data']['status'] === 'success') {

            $bookingId = $response['data']['metadata']['booking_id'];

            $booking = Booking::findOrFail($bookingId);

            $booking->update([
                'client_payment_status' => 'Paid',
                'status' => 'Active',
            ]);

            return redirect()->route('client.lessons', $booking)
                ->with('success', 'Payment successful. Lesson activated.');
        }

        return redirect()->back()->with('error', 'Payment failed.');
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');

        $hash = hash_hmac(
            'sha512',
            $request->getContent(),
            config('services.paystack.secret')
        );

        if ($hash !== $signature) {
            abort(403);
        }

        $event = $request->input('event');

        if ($event === 'charge.success') {

            $data = $request->input('data');

            $bookingId = $data['metadata']['booking_id'];

            $booking = Booking::find($bookingId);

            if ($booking && $booking->client_payment_status !== 'Paid') {

                $booking->update([
                    'client_payment_status' => 'Paid',
                    'status' => 'Accepted',
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}