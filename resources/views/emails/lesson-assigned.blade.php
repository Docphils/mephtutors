<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Assigned</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    @php
        $subjectRaw = $newBooking->subjects;
        if (is_string($subjectRaw)) {
            $decodedSubjects = json_decode($subjectRaw, true);
            $subjectRaw = is_array($decodedSubjects) ? $decodedSubjects : [$subjectRaw];
        }
        $subjectList = collect((array) $subjectRaw)->map(fn ($s) => is_array($s) ? ($s['name'] ?? null) : $s)->filter()->implode(', ');
    @endphp
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0891b2,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Your Lesson Is Assigned</h1>
            <p style="margin:8px 0 0;font-size:13px;">A tutor has been matched to your request.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello {{ $client->name ?? 'Client' }},</p>
            <p style="margin:0 0 14px;">Your request has been assigned. Please review the details and continue payment/acceptance from your dashboard.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Booking ID:</strong> #{{ $newBooking->id }}</p>
                <p style="margin:0 0 8px;"><strong>Tutor:</strong> {{ $tutor->tutorProfile?->fullName ?? $tutor->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $newBooking->serviceItem?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Start Date:</strong> {{ optional($newBooking->start_date)->format('M d, Y') ?? $newBooking->start_date }}</p>
                <p style="margin:0 0 8px;"><strong>End Date:</strong> {{ optional($newBooking->end_date)->format('M d, Y') ?? $newBooking->end_date ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Location:</strong> {{ $newBooking->location ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Subjects:</strong> {{ $subjectList ?: 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Amount:</strong> NGN {{ number_format((float) ($newBooking->amount ?? 0), 2) }}</p>
                <p style="margin:0;"><strong>Status:</strong> {{ $newBooking->status ?? 'Pending' }}</p>
            </div>

            <p style="margin:16px 0 0;">If any detail needs adjustment, use the lesson conversation thread or contact support.</p>
            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Support Team</strong></p>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
