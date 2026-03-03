<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Profile Status</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    @php
        $status = ($tutorProfile->status ?? '') === 'Approved' ? 'Approved' : 'Review';
    @endphp
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0891b2,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Tutor Profile Status Update</h1>
            <p style="margin:8px 0 0;font-size:13px;">Your profile review outcome is available.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello {{ $tutorProfile->fullName ?? $tutorProfile->user?->name ?? 'Tutor' }},</p>
            <p style="margin:0 0 14px;">Your tutor profile was reviewed with the details below:</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Status:</strong> {{ $status }}</p>
                <p style="margin:0;"><strong>Remark:</strong> {{ $tutorProfile->approvalRemark ?: 'No remark provided' }}</p>
            </div>

            @if ($status === 'Approved')
                <p style="margin:16px 0 0;">Congratulations. You are now eligible for tutor assignments on MephEd.</p>
            @else
                <p style="margin:16px 0 0;">Please update your profile based on the remark and resubmit for approval.</p>
            @endif

            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Support Team</strong></p>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
