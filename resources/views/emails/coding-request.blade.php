<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coding Request</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0891b2,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Coding Service Request</h1>
            <p style="margin:8px 0 0;font-size:13px;">New coding-focused request submitted.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello Admin,</p>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Request ID:</strong> #{{ $codingRequest->id ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Client:</strong> {{ $codingRequest->user?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Institution:</strong> {{ $codingRequest->institution_name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Address:</strong> {{ $codingRequest->institution_address ?? $codingRequest->full_address ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $codingRequest->serviceItem?->name ?? $codingRequest->class_type ?? 'N/A' }}</p>
                <p style="margin:0;"><strong>Requirements:</strong> {{ $codingRequest->requirements ?? 'N/A' }}</p>
            </div>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
