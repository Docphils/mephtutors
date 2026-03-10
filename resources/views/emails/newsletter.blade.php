<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $content['subject'] }}</title>
</head>

<body style="font-family:Arial,Helvetica,sans-serif;background:#f8fafc;color:#0f172a;padding:24px;">
    <div style="max-width:620px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <div style="background:#0891b2;color:#fff;padding:18px 20px;">
            <h1 style="margin:0;font-size:20px;">{{ $content['title'] }}</h1>
            <p style="margin:6px 0 0;font-size:12px;opacity:.9;">MephEd newsletter update</p>
        </div>

        <div style="padding:20px;">
            <p style="margin:0 0 14px;">Hello {{ $user->name ?? 'there' }},</p>

            <div style="margin:0 0 14px;line-height:1.6;color:#334155;">
                {!! str_replace(
                    ['<ul>', '<ol>'],
                    ['<ul style="padding-left:20px; margin:10px 0;">', '<ol style="padding-left:20px; margin:10px 0;">'],
                    $content['body'],
                ) !!}
            </div>

            @if (!empty($content['body2']))
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin:0 0 14px;color:#334155;">
                    {{ $content['body2'] }}
                </div>
            @endif

            <p style="margin:16px 0 0;">Best regards,<br><strong>MephEd Support Team</strong></p>
            <p style="margin:16px 0 0;font-size:12px;color:#64748b;">
                Changed your mind?
                <a href="{{ $unsubscribeUrl }}" style="color:#0891b2;text-decoration:underline;font-weight:700;">
                    Click here to Unsubscribe
                </a>
            </p>
            <p style="margin:8px 0 0;font-size:12px;color:#64748b;">&copy; {{ date('Y') }} MephEd. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
