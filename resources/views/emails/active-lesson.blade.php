<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Active Lesson</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    @php
        $subjectRaw = $activeLesson->subjects;
        if (is_string($subjectRaw)) {
            $decodedSubjects = json_decode($subjectRaw, true);
            $subjectRaw = is_array($decodedSubjects) ? $decodedSubjects : [$subjectRaw];
        }
        $subjectList = collect((array) $subjectRaw)->map(fn ($s) => is_array($s) ? ($s['name'] ?? null) : $s)->filter()->implode(', ');

        $scheduleRaw = $activeLesson->days_times;
        if (is_string($scheduleRaw)) {
            $decodedSchedule = json_decode($scheduleRaw, true);
            $scheduleRaw = is_array($decodedSchedule) ? $decodedSchedule : [$scheduleRaw];
        }
        $scheduleList = collect((array) $scheduleRaw)
            ->map(function ($slot) {
                if (is_array($slot)) return trim(($slot['day'] ?? '') . ' ' . ($slot['time'] ?? ''));
                return (string) $slot;
            })->filter()->implode(' | ');
    @endphp

    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #cbd5e1;">
        <div style="background:linear-gradient(120deg,#155e75,#0f172a);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Lesson Activated</h1>
            <p style="margin:8px 0 0;font-size:13px;opacity:.95;">A booking has moved to Active and requires tutor action.</p>
        </div>

        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello {{ $activeLesson->tutor?->tutorProfile?->fullName ?? $activeLesson->tutor?->name ?? 'Tutor' }},</p>
            <p style="margin:0 0 14px;">Your lesson with <strong>{{ $activeLesson->client?->name ?? 'Client' }}</strong> is now active.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Booking ID:</strong> #{{ $activeLesson->id }}</p>
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $activeLesson->serviceItem?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Start Date:</strong> {{ optional($activeLesson->start_date)->format('M d, Y') ?? $activeLesson->start_date }}</p>
                <p style="margin:0 0 8px;"><strong>End Date:</strong> {{ optional($activeLesson->end_date)->format('M d, Y') ?? $activeLesson->end_date ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Location:</strong> {{ $activeLesson->location ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Subjects:</strong> {{ $subjectList ?: 'N/A' }}</p>
                <p style="margin:0;"><strong>Schedule:</strong> {{ $scheduleList ?: 'N/A' }}</p>
            </div>

            <p style="margin:16px 0 0;">Please check your tutor dashboard and continue with delivery.</p>
            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Support Team</strong></p>
        </div>

        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
