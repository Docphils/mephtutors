<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intervention Enquiry</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:700px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0e7490,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">New Intervention Enquiry</h1>
            <p style="margin:8px 0 0;font-size:13px;">A parent just submitted an academic support enquiry.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;"><strong>Intervention:</strong> {{ $enquiry->programme?->name ?? 'General Enquiry' }}</p>
            <p><strong>Learner:</strong> {{ $enquiry->learner_name }} ({{ $enquiry->class_level }})</p>
            <p><strong>Subjects:</strong> {{ implode(', ', $enquiry->subjects ?? []) }}</p>
            <p><strong>Mode:</strong> {{ ucfirst($enquiry->lesson_mode) }}</p>
            <p><strong>Frequency:</strong> {{ $enquiry->preferred_frequency }}</p>
            <p><strong>Duration:</strong> {{ $enquiry->preferred_duration }}</p>

            <hr style="border:none;border-top:1px solid #e2e8f0;margin:16px 0;">

            <p><strong>Parent Name:</strong> {{ $enquiry->user?->name ?? $enquiry->parent_name }}</p>
            <p><strong>Phone:</strong> {{ $enquiry->user?->userProfile?->phone ?? $enquiry->parent_phone }}</p>
            <p><strong>Email:</strong> {{ $enquiry->user?->email ?? 'N/A' }}</p>
            <p><strong>Location:</strong> {{ $enquiry->city_area }}, {{ $enquiry->state }}</p>

            @if (!empty($enquiry->weak_areas))
                <p><strong>Weak Areas / Concerns:</strong><br>{{ $enquiry->weak_areas }}</p>
            @endif

            @if (!empty($enquiry->recent_performance_notes))
                <p><strong>Recent Performance Notes:</strong><br>{{ $enquiry->recent_performance_notes }}</p>
            @endif
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
