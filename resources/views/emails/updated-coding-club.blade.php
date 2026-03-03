<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution Request Updated</title>
</head>
<body style="margin:0; padding:24px; background:#f1f5f9; font-family:Arial, sans-serif; color:#0f172a;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e2e8f0;">
        <div style="background:#0e7490; color:#ffffff; padding:20px 24px;">
            <h1 style="margin:0; font-size:22px;">Institution Request Update</h1>
            <p style="margin:6px 0 0; font-size:13px; opacity:.9;">Your request status and commercial details were updated.</p>
        </div>

        <div style="padding:24px;">
            <p style="margin-top:0;">Hello {{ $updatedRequest->user->name }},</p>
            <p>Your institution request <strong>#CRM-{{ $updatedRequest->id }}</strong> has been updated.</p>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin:18px 0;">
                <p style="margin:0 0 8px;"><strong>Status:</strong> {{ str_replace('_', ' ', $updatedRequest->status) }}</p>
                <p style="margin:0 0 8px;"><strong>Institution:</strong> {{ $updatedRequest->institution_name }}</p>
                <p style="margin:0 0 8px;"><strong>Quote Amount:</strong> {{ $updatedRequest->quote_amount ? '₦' . number_format($updatedRequest->quote_amount, 2) : 'Pending' }}</p>
                <p style="margin:0 0 8px;"><strong>Payment Status:</strong> {{ strtoupper($updatedRequest->payment_status) }}</p>
                <p style="margin:0;"><strong>Payment Reference:</strong> {{ $updatedRequest->payment_reference ?: 'Not generated' }}</p>
            </div>

            @if ($updatedRequest->quote_notes)
                <div style="margin-bottom:16px;">
                    <p style="margin:0 0 8px; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:#64748b;"><strong>Quote Notes</strong></p>
                    <div style="background:#f8fafc; border-radius:10px; padding:12px;">{!! $updatedRequest->quote_notes !!}</div>
                </div>
            @endif

            @if ($updatedRequest->contract_terms)
                <div style="margin-bottom:16px;">
                    <p style="margin:0 0 8px; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:#64748b;"><strong>Contract Terms</strong></p>
                    <div style="background:#f8fafc; border-radius:10px; padding:12px;">{!! $updatedRequest->contract_terms !!}</div>
                </div>
            @endif

            @if ($updatedRequest->payment_link && $updatedRequest->payment_status !== 'paid')
                <p style="margin:20px 0;">
                    <a href="{{ $updatedRequest->payment_link }}"
                        style="display:inline-block; background:#16a34a; color:#ffffff; text-decoration:none; font-weight:700; padding:12px 18px; border-radius:10px;">
                        Complete Payment
                    </a>
                </p>
            @endif

            <p style="margin:0;">Regards,<br><strong>MephEd Admin</strong></p>
        </div>
    </div>
</body>
</html>
