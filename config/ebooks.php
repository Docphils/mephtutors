<?php

return [
    'sales_enabled' => (bool) env('EBOOK_SALES_ENABLED', false),
    'queue_connection' => env('EBOOK_QUEUE_CONNECTION', 'database'),
    'thank_you_url_minutes' => (int) env('EBOOK_THANK_YOU_URL_MINUTES', 15),
    'fulfilment' => [
        'download_ttl_hours' => (int) env('EBOOK_DOWNLOAD_TTL_HOURS', 72),
        'max_downloads' => (int) env('EBOOK_MAX_DOWNLOADS', 5),
        'pdf_stamper_binary' => env('EBOOK_PDF_STAMPER_BINARY', PHP_OS_FAMILY === 'Windows' ? 'gswin64c' : 'gs'),
        'pdf_stamper_timeout' => (int) env('EBOOK_PDF_STAMPER_TIMEOUT', 240),
        'ocr_binary' => env('EBOOK_OCR_BINARY'),
    ],
    'checkout_attempts_per_email_per_minute' => (int) env('EBOOK_CHECKOUT_ATTEMPTS_PER_MINUTE', 5),
    'download_attempts_per_minute' => (int) env('EBOOK_DOWNLOAD_ATTEMPTS_PER_MINUTE', 12),
    'resend_attempts_per_order_per_hour' => (int) env('EBOOK_RESEND_ATTEMPTS_PER_HOUR', 3),
    'pending_order_check_minutes' => (int) env('EBOOK_PENDING_CHECK_MINUTES', 30),
    'unfulfilled_alert_minutes' => (int) env('EBOOK_UNFULFILLED_ALERT_MINUTES', 10),
    'reconciliation_minutes' => (int) env('EBOOK_RECONCILIATION_MINUTES', 10),
    'alert_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('EBOOK_ALERT_EMAILS', ''))))),
    'alert_dedupe_minutes' => (int) env('EBOOK_ALERT_DEDUPE_MINUTES', 60),
];
