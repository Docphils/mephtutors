<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Bootcamp Registration</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(120deg,#0e7490,#1e293b);padding:20px 24px;color:#ecfeff;">
            <h1 style="margin:0;font-size:22px;">Bootcamp Registration Received</h1>
            <p style="margin:8px 0 0;font-size:13px;">A new enrollee submitted the bootcamp form.</p>
        </div>

        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello Admin,</p>
            <p style="margin:0 0 14px;">Please review this registration in Bootcamp Manager.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                <p style="margin:0 0 8px;"><strong>Name:</strong> {{ $registrant['name'] ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Email:</strong> {{ $registrant['email'] ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Phone:</strong> {{ $registrant['phone'] ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $registrant['service'] ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Track:</strong> {{ $registrant['service_item'] ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Cohort:</strong> {{ $registrant['cohort'] ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Cohort Code:</strong> {{ $registrant['cohort_code'] ?? 'N/A' }}</p>
                <p style="margin:0;"><strong>Address:</strong> {{ $registrant['address'] ?? 'N/A' }}</p>
            </div>

            <p style="margin:14px 0 0;">Regards,<br><strong>MephEd Intake Bot</strong></p>
        </div>

        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
