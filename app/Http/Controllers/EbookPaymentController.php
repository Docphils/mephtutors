<?php

namespace App\Http\Controllers;

use App\Models\EbookOrder;
use App\Services\EbookPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class EbookPaymentController extends Controller
{
    public function verify(Request $request, EbookPaymentService $payments): JsonResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'regex:/^[A-Za-z0-9.=-]{1,100}$/'],
        ]);

        try {
            $result = $payments->verifyAndRecord($validated['reference']);
        } catch (\Throwable $exception) {
            Log::warning('eBook payment verification request failed.', [
                'reference_hash' => hash('sha256', $validated['reference']),
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'We could not verify your payment right now. Please try again or contact support.',
            ], 422);
        }

        return response()->json([
            'paid' => $result['paid'],
            'status' => $result['status'],
            'message' => $result['paid']
                ? 'Payment verified. Please check this page for delivery updates.'
                : 'Payment is not confirmed yet. You can retry verification shortly.',
        ]);
    }

    public function callback(Request $request, EbookPaymentService $payments): RedirectResponse
    {
        $reference = $request->query('reference');
        if (! is_string($reference) || ! preg_match('/^[A-Za-z0-9.=-]{1,100}$/', $reference)) {
            return redirect()->route('welcome')->with('ebook_payment_error', 'We could not verify that payment. Please contact support.');
        }

        try {
            $result = $payments->verifyAndRecord($reference);
        } catch (\Throwable $exception) {
            Log::warning('eBook payment callback verification failed.', [
                'reference_hash' => hash('sha256', $reference),
                'exception' => $exception,
            ]);

            return redirect()->route('welcome')->with('ebook_payment_error', 'We could not verify your payment right now. Please contact support.');
        }

        $order = EbookOrder::query()->with('ebook')->where('paystack_reference', $reference)->first();
        if (! $order) {
            return redirect()->route('welcome')->with('ebook_payment_error', 'We could not find that order. Please contact support.');
        }

        if ($result['paid']) {
            return redirect()->to(URL::temporarySignedRoute(
                'ebooks.thank-you',
                now()->addMinutes(config('ebooks.thank_you_url_minutes')),
                ['reference' => $order->reference],
            ));
        }

        if (! $order->ebook) {
            return redirect()->route('welcome')->with('ebook_payment_error', 'We could not find that eBook. Please contact support.');
        }

        return redirect()->route('ebooks.show', ['slug' => $order->ebook->slug])
            ->with('ebook_payment_error', 'Payment is not confirmed yet. Please try again shortly.');
    }
}
