<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution Request Update</title>
</head>
<body style="margin:0;padding:24px;background:#e2e8f0;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #cbd5e1;">
        <div style="background:linear-gradient(120deg,#0e7490,#1e293b);color:#ffffff;padding:20px 24px;">
            <h1 style="margin:0;font-size:22px;">Institution Request Updated</h1>
            <p style="margin:8px 0 0;font-size:13px;opacity:.95;">Status, quote, or commercial terms changed on your request.</p>
        </div>

        <div style="padding:22px 24px;">
            <p style="margin-top:0;">Hello {{ $updatedRequest->user->name }},</p>
            <p style="margin:0 0 14px;">Your request <strong>#CRM-{{ $updatedRequest->id }}</strong> has new details.</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:14px;">
                <p style="margin:0 0 8px;"><strong>Status:</strong> {{ str_replace('_', ' ', $updatedRequest->status) }}</p>
                <p style="margin:0 0 8px;"><strong>Institution:</strong> {{ $updatedRequest->institution_name }}</p>
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $updatedRequest->serviceItem?->name ?? 'N/A' }}</p>
                <p style="margin:0 0 8px;"><strong>Quote Amount:</strong> {{ $updatedRequest->quote_amount ? 'NGN ' . number_format((float) $updatedRequest->quote_amount, 2) : 'Pending' }}</p>
                <p style="margin:0 0 8px;"><strong>Payment Status:</strong> {{ strtoupper($updatedRequest->payment_status) }}</p>
                <p style="margin:0;"><strong>Payment Reference:</strong> {{ $updatedRequest->payment_reference ?: 'Not generated' }}</p>
            </div>

            @if ($updatedRequest->quote_notes)
                <div style="margin-bottom:14px;">
                    <p style="margin:0 0 8px;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:#64748b;"><strong>Quote Notes</strong></p>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;">{!! $updatedRequest->quote_notes !!}</div>
                </div>
            @endif

            @if ($updatedRequest->contract_terms)
                <div style="margin-bottom:14px;">
                    <p style="margin:0 0 8px;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:#64748b;"><strong>Contract Terms</strong></p>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;">{!! $updatedRequest->contract_terms !!}</div>
                </div>
            @endif

            @if ($updatedRequest->payment_link && $updatedRequest->payment_status !== 'paid')
                <p style="margin:18px 0 0;">
                    <a href="{{ $updatedRequest->payment_link }}" style="display:inline-block;background:#0891b2;color:#ffffff;text-decoration:none;font-weight:700;padding:11px 16px;border-radius:8px;">
                        Complete Payment
                    </a>
                </p>
            @endif

            <p style="margin:16px 0 0;">Regards,<br><strong>MephEd Admin</strong></p>
        </div>

        <div style="background:#0f172a;color:#cbd5e1;text-align:center;padding:12px;font-size:12px;">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
