<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registration Received</title>
</head>

<body style="font-family:Arial,Helvetica,sans-serif;background:#f8fafc;color:#0f172a;padding:24px;">
    <div
        style="max-width:620px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <div style="background:#0891b2;color:#fff;padding:18px 20px;">
            <h1 style="margin:0;font-size:20px;">Registration Received</h1>
            <p style="margin:6px 0 0;font-size:12px;opacity:.9;">MephEd</p>
        </div>
        <div style="padding:20px;">
            <p style="margin:0 0 14px;">Hello {{ $enrollee->name }},</p>
            <p style="margin:0 0 14px;">We've received your registration. Here's what you signed up for:</p>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;">
                <p style="margin:0 0 8px;"><strong>Track:</strong> {{ $enrollee->cohort->serviceItem->name ?? 'N/A' }}
                </p>
                <p style="margin:0 0 8px;"><strong>Cohort:</strong> {{ $enrollee->cohort->name ?? 'N/A' }}
                    ({{ $enrollee->cohort->code ?? 'N/A' }})</p>
                <p style="margin:0;"><strong>Start Date:</strong>
                    {{ $enrollee->cohort->start_date?->format('M d, Y') ?? 'N/A' }}</p>
            </div>
            <p style="margin:16px 0 0;">To secure your spot, please complete payment on the page you were redirected to.
                If you closed that page, you can return to the enrollment link any time to try again.</p>
            <p style="margin:16px 0 0;font-size:12px;color:#64748b;">
                If you've already completed payment, you'll receive a separate confirmation shortly.
            </p>
        </div>
    </div>
</body>

</html>
