<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Tutor Request</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    @php
        $subjects = collect((array) $tutorRequest->subjects)->filter()->implode(', ');
        $learners = collect((array) $tutorRequest->learners)->pluck('name')->filter()->implode(', ');
        $days = collect((array) $tutorRequest->preferred_days)->filter()->implode(', ');
    @endphp
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0891b2,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Tutor Request Submitted</h1>
            <p style="margin:8px 0 0;font-size:13px;">A client submitted a new tutor request.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello Admin,</p>
            <p style="margin:0 0 14px;">New request from <strong>{{ $client->name ?? 'Client' }}</strong>.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Request ID:</strong> #{{ $tutorRequest->id }}</p>
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $tutorRequest->serviceItem?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Delivery Mode:</strong> {{ ucfirst($tutorRequest->delivery_mode ?? 'N/A') }}</p>
                <p style="margin:0 0 8px;"><strong>Session Type:</strong> {{ ucfirst($tutorRequest->session_type ?? 'N/A') }}</p>
                <p style="margin:0 0 8px;"><strong>Duration/Session:</strong> {{ $tutorRequest->duration_per_session ?? 'N/A' }} mins</p>
                <p style="margin:0 0 8px;"><strong>Budget:</strong> NGN {{ number_format((float) ($tutorRequest->budget_min ?? 0), 2) }} - NGN {{ number_format((float) ($tutorRequest->budget_max ?? 0), 2) }}</p>
                <p style="margin:0 0 8px;"><strong>Preferred Tutor Gender:</strong> {{ ucfirst($tutorRequest->preferred_tutor_gender ?? 'Any') }}</p>
                <p style="margin:0 0 8px;"><strong>Subjects:</strong> {{ $subjects ?: 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Learners:</strong> {{ $tutorRequest->is_for_self ? ($client->name ?? 'Self') : ($learners ?: 'N/A') }}</p>
                <p style="margin:0 0 8px;"><strong>Preferred Days:</strong> {{ $days ?: 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Address:</strong> {{ $tutorRequest->lesson_address ?? 'N/A' }}</p>
                <p style="margin:0;"><strong>Notes:</strong> {{ $tutorRequest->additional_notes ?: 'None' }}</p>
            </div>

            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Request Intake</strong></p>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
