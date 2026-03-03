<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Closed Lesson Payment Alert</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0891b2,#0f172a);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Payment Ready For Processing</h1>
            <p style="margin:8px 0 0;font-size:13px;">A completed booking has been closed and tutor earning is due.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello Admin,</p>
            <p style="margin:0 0 14px;">The system auto-closed a lesson after the completion review window.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Booking ID:</strong> #{{ $closedLesson->id ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Tutor:</strong> {{ $closedLesson->tutor?->tutorProfile?->fullName ?? $closedLesson->tutor?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Client:</strong> {{ $closedLesson->client?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Amount:</strong> NGN {{ number_format((float) ($closedLesson->amount ?? 0), 2) }}</p>
                <p style="margin:0 0 8px;"><strong>Booking Status:</strong> {{ $closedLesson->status ?? 'N/A' }}</p>
                <p style="margin:0;"><strong>Payment Status:</strong> {{ $closedLesson->payments?->status ?? 'N/A' }}</p>
            </div>

            <p style="margin:16px 0 0;">Please complete internal payout processing.</p>
            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd System</strong></p>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
