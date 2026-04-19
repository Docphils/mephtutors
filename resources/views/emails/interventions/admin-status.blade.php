<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intervention Admin Notification</title>
</head>

<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div
        style="max-width:680px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0891b2,#0f172a);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">{{ $headline }}</h1>
            <p style="margin:8px 0 0;font-size:13px;">Intervention Reference #{{ $enquiry->id }}</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello Admin,</p>
            <p style="margin:0 0 14px;">{{ $messageLine }}</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Intervention:</strong> {{ $enquiry->programme?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Learner:</strong> {{ $enquiry->learner_name ?: 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Client:</strong>
                    {{ $enquiry->user?->name ?? ($enquiry->parent_name ?? 'N/A') }}</p>
                <p style="margin:0 0 8px;"><strong>Client Email:</strong> {{ $enquiry->user?->email ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Status:</strong>
                    {{ str_replace('_', ' ', $enquiry->status ?? 'N/A') }}</p>
                <p style="margin:0;"><strong>Payment:</strong>
                    {{ strtoupper((string) ($enquiry->payment_status ?? 'pending')) }}</p>
            </div>

            @if (!empty($context['note']))
                <p style="margin:14px 0 0;"><strong>Note:</strong> {{ $context['note'] }}</p>
            @endif

            @if (!empty($context['dispute_reason']))
                <p style="margin:14px 0 0;"><strong>Dispute Reason:</strong> {{ $context['dispute_reason'] }}</p>
            @endif

            <p style="margin:20px 0 0;">
                <a href="{{ $ctaUrl }}"
                    style="display:inline-block;background:#0e7490;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:700;font-size:13px;">
                    {{ $ctaText }}
                </a>
            </p>

            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Notification Service</strong></p>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>

</html>
