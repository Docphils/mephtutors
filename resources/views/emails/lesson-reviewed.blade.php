<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Reviewed By Client</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0891b2,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Client Updated Lesson Status</h1>
            <p style="margin:8px 0 0;font-size:13px;">A booking was reviewed from the client side.</p>
        </div>
        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello Admin,</p>
            <p style="margin:0 0 14px;">Please review the latest lesson update and take the next required action.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Booking ID:</strong> #{{ $reviewedLesson->id }}</p>
                <p style="margin:0 0 8px;"><strong>Client:</strong> {{ $reviewedLesson->client?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>New Status:</strong> {{ $reviewedLesson->status ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Acceptance Remarks:</strong> {{ $reviewedLesson->clientAcceptanceRemarks ?: 'None' }}</p>
                <p style="margin:0;"><strong>Approval Remarks:</strong> {{ $reviewedLesson->clientApprovalRemarks ?: 'None' }}</p>
            </div>

            <p style="margin:16px 0 0;">If status is <strong>Accepted</strong>, payment initialization is expected from the current flow.</p>
            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Support Team</strong></p>
        </div>
        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
