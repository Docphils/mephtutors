<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Completion Declined</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    @php
        $subjectRaw = $declinedLesson->subjects;
        if (is_string($subjectRaw)) {
            $decodedSubjects = json_decode($subjectRaw, true);
            $subjectRaw = is_array($decodedSubjects) ? $decodedSubjects : [$subjectRaw];
        }
        $subjectList = collect((array) $subjectRaw)->map(fn ($s) => is_array($s) ? ($s['name'] ?? null) : $s)->filter()->implode(', ');
    @endphp
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0e7490,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Lesson Completion Declined</h1>
            <p style="margin:8px 0 0;font-size:13px;">Client requested adjustments before closure.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello {{ $declinedLesson->tutor?->tutorProfile?->fullName ?? $declinedLesson->tutor?->name ?? 'Tutor' }},</p>
            <p style="margin:0 0 14px;">The client declined completion for this booking. Please review remarks and proceed accordingly.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Booking ID:</strong> #{{ $declinedLesson->id }}</p>
                <p style="margin:0 0 8px;"><strong>Client:</strong> {{ $declinedLesson->client?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Location:</strong> {{ $declinedLesson->location ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Subjects:</strong> {{ $subjectList ?: 'N/A' }}</p>
                <p style="margin:0;"><strong>Client Remarks:</strong> {{ $declinedLesson->clientApprovalRemarks ?: 'None' }}</p>
            </div>

            <p style="margin:16px 0 0;">Coordinate with support if clarification is required.</p>
            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Support Team</strong></p>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
