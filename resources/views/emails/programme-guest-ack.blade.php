<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intervention Request Received</title>
</head>

<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div
        style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0e7490,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Your Intervention Request Is In</h1>
            <p style="margin:8px 0 0;font-size:13px;">We have logged your request and queued it for review.</p>
        </div>

        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hi {{ $user->name }},</p>
            <p style="margin:0 0 14px;">Thank you for requesting
                {{ $programmeRequest->programme?->name ?? 'an intervention' }}. Your account has been prepared for
                tracking and payment.</p>

            @if (!empty($resetUrl))
                <p style="margin:0 0 12px;">Set your password to activate your account:</p>
                <p style="margin:0 0 14px;">
                    <a href="{{ $resetUrl }}"
                        style="display:inline-block;background:#0891b2;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:700;">Set
                        Password</a>
                </p>
            @endif

            <p style="margin:0 0 12px;">
                After login, go to your Intervention Requests dashboard to complete payment and track status updates:
            </p>
            <p style="margin:0 0 14px;">
                <a href="{{ route('login') }}"
                    style="display:inline-block;background:#0f172a;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:700;">Login
                    to Continue</a>
            </p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Request ID:</strong> #{{ $programmeRequest->id }}</p>
                <p style="margin:0 0 8px;"><strong>Status:</strong> {{ ucfirst($programmeRequest->status) }}</p>
                <p style="margin:0 0 8px;"><strong>Intervention:</strong> {{ $programmeRequest->programme?->name }}</p>
                <p style="margin:0 0 8px;"><strong>Learner:</strong> {{ $programmeRequest->learner_name }}@if (!empty($programmeRequest->class_level))
                        ({{ $programmeRequest->class_level }})
                    @endif
                </p>
                <p style="margin:0 0 8px;"><strong>Subjects:</strong>
                    {{ implode(', ', $programmeRequest->subjects ?? []) }}</p>
                <p style="margin:0;"><strong>Estimated Price:</strong> <em>Provided after admin review</em></p>
            </div>

            <p style="margin:16px 0 0;">Regards,<br><strong>MephEd Support Team</strong></p>
        </div>

        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>

</html>
