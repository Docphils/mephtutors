<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EbookAlertService
{
    public function sendOnce(string $key, string $subject, string $message): void
    {
        $emails = config('ebooks.alert_emails', []);
        if ($emails === []) {
            Log::warning('eBook alert was not emailed because no alert recipient is configured.', [
                'alert_key' => $key,
                'subject' => $subject,
            ]);

            return;
        }

        $cacheKey = 'ebook-alert:'.hash('sha256', $key);
        if (! Cache::add($cacheKey, true, now()->addMinutes(config('ebooks.alert_dedupe_minutes', 60)))) {
            return;
        }

        try {
            Mail::raw($message, function ($mail) use ($emails, $subject): void {
                $mail->to($emails)->subject('[MephEd eBooks] '.$subject);
            });
        } catch (\Throwable $exception) {
            Cache::forget($cacheKey);
            Log::error('eBook alert email could not be sent.', [
                'alert_key' => $key,
                'exception' => $exception,
            ]);
        }
    }

    public function recordWebhookSignatureFailure(): void
    {
        $counterKey = 'ebook-webhook-signature-failures:'.now()->format('YmdH');
        Cache::add($counterKey, 0, now()->addHours(2));
        $failures = Cache::increment($counterKey);

        if ($failures >= 5) {
            $this->sendOnce('webhook-signature-spike-'.now()->format('YmdH'), 'Paystack signature failure spike', "At least {$failures} eBook webhook signatures failed during the current hour.");
        }
    }
}
