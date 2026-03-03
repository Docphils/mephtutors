<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Received</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0e7490,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">We Received Your Request</h1>
            <p style="margin:8px 0 0;font-size:13px;">Your request has been logged and queued for review.</p>
        </div>

        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hi {{ $user->name }},</p>
            <p style="margin:0 0 14px;">Thanks for choosing MephEd. We created your account so you can track request updates.</p>

            @if (!empty($resetUrl))
                <p style="margin:0 0 12px;">Set your password to activate account access:</p>
                <p style="margin:0 0 14px;">
                    <a href="{{ $resetUrl }}" style="display:inline-block;background:#0891b2;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:700;">Set Password</a>
                </p>
            @endif

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Request ID:</strong> #{{ $requestModel->id ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Status:</strong> {{ $requestModel->status ?? 'Submitted' }}</p>

                @if ($requestModel instanceof \App\Models\Crm)
                    <p style="margin:0 0 8px;"><strong>Request Type:</strong> Institutional Service</p>
                    <p style="margin:0 0 8px;"><strong>Institution:</strong> {{ $requestModel->institution_name }}</p>
                    <p style="margin:0 0 8px;"><strong>Tutors Needed:</strong> {{ $requestModel->number_of_tutors_required }}</p>
                    <p style="margin:0 0 8px;"><strong>Delivery Mode:</strong> {{ ucfirst($requestModel->delivery_mode ?? 'N/A') }}</p>
                    <p style="margin:0 0 8px;"><strong>Engagement:</strong> {{ str_replace('_', ' ', $requestModel->engagement_type ?? 'N/A') }}</p>
                    <p style="margin:0;"><strong>Sessions/Week:</strong> {{ $requestModel->sessions_per_week ?? 'N/A' }}</p>
                @elseif($requestModel instanceof \App\Models\TutorRequest)
                    @php
                        $subjects = collect((array) $requestModel->subjects)->filter()->implode(', ');
                        $days = collect((array) $requestModel->preferred_days)->filter()->implode(', ');
                    @endphp
                    <p style="margin:0 0 8px;"><strong>Request Type:</strong> Tutor Service</p>
                    <p style="margin:0 0 8px;"><strong>Delivery Mode:</strong> {{ ucfirst($requestModel->delivery_mode ?? 'N/A') }}</p>
                    <p style="margin:0 0 8px;"><strong>Session Type:</strong> {{ ucfirst($requestModel->session_type ?? 'N/A') }}</p>
                    <p style="margin:0 0 8px;"><strong>Budget Range:</strong> NGN {{ number_format((float) ($requestModel->budget_min ?? 0), 2) }} - NGN {{ number_format((float) ($requestModel->budget_max ?? 0), 2) }}</p>
                    <p style="margin:0 0 8px;"><strong>Subjects:</strong> {{ $subjects ?: 'N/A' }}</p>
                    <p style="margin:0;"><strong>Preferred Days:</strong> {{ $days ?: 'N/A' }}</p>
                @endif
            </div>

            <p style="margin:16px 0 0;">If you did not submit this request, you can ignore this email.</p>
            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Support Team</strong></p>
        </div>

        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
