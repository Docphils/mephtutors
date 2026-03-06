<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Class Session Scheduled</title>
</head>

<body style="font-family:Arial,Helvetica,sans-serif;background:#f8fafc;color:#0f172a;padding:24px;">
    <div style="max-width:620px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <div style="background:#0891b2;color:#fff;padding:18px 20px;">
            <h1 style="margin:0;font-size:20px;">Online Class Scheduled</h1>
            <p style="margin:6px 0 0;font-size:12px;opacity:.9;">Jitsi class session invitation</p>
        </div>
        <div style="padding:20px;">
            <p style="margin:0 0 14px;">Hello {{ $recipient->name }},</p>
            <p style="margin:0 0 14px;">A class session has been scheduled for your lesson.</p>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;">
                <p style="margin:0 0 8px;"><strong>Title:</strong> {{ $meeting->title }}</p>
                <p style="margin:0 0 8px;"><strong>Start:</strong> {{ $meeting->starts_at?->format('M d, Y h:i A') }}</p>
                <p style="margin:0 0 8px;"><strong>End:</strong> {{ $meeting->ends_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                <p style="margin:0;"><strong>Room:</strong> {{ $meeting->jitsi_room }}</p>
            </div>
            <p style="margin:16px 0 0;">
                <a href="{{ $joinUrl }}" style="display:inline-block;background:#0891b2;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:700;">
                    Join Session
                </a>
            </p>
            <p style="margin:16px 0 0;font-size:12px;color:#64748b;">
                This link is also available in your dashboard under Online Sessions.
            </p>
        </div>
    </div>
</body>

</html>
