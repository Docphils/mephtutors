<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Profile Submitted</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0e7490,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Tutor Profile Submitted</h1>
            <p style="margin:8px 0 0;font-size:13px;">A tutor profile needs admin review.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello Admin,</p>
            <p style="margin:0 0 14px;">A tutor profile was created or updated.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Tutor ID:</strong> {{ $tutorProfile->user?->id ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Name:</strong> {{ $tutorProfile->fullName ?? $tutorProfile->user?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Qualification:</strong> {{ $tutorProfile->qualification ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Discipline:</strong> {{ $tutorProfile->discipline ?? 'N/A' }}</p>
                <p style="margin:0;"><strong>Status:</strong> {{ $tutorProfile->status ?? 'Review' }}</p>
            </div>

            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Tutor Onboarding</strong></p>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
