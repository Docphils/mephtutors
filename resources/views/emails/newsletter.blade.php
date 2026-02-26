<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title>{{ $content['subject'] }}</title>
    <style>
        .body {
            margin: 0;
            padding: 0;
            width: 100%;
            word-break: break-word;
            -webkit-font-smoothing: antialiased;
            background-color: #f8fafc;
        }

        .content-table {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            margin-top: 40px;
            margin-bottom: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #0891b2;
            padding: 40px 20px;
            text-align: center;
        }

        .inner-body {
            padding: 40px 30px;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            line-height: 1.6;
        }

        .footer {
            background-color: #f1f5f9;
            padding: 24px;
            text-align: center;
            font-family: sans-serif;
            font-size: 12px;
            color: #64748b;
        }

        h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .button {
            background-color: #0891b2;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>

<body class="body">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                <table class="content-table" role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td class="header">
                            <img src="{{ asset('images/MephEd.png') }}" alt="MephEd Logo" width="120"
                                style="margin-bottom: 20px; display: inline-block; border: 0;">
                            <h1>{{ $content['title'] }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td class="inner-body">
                            {{-- <p style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 16px;">
                                Hello there,
                            </p> --}}

                            <div style="font-size: 15px; color: #475569;">
                                {!! str_replace(
                                    ['<ul>', '<ol>'],
                                    ['<ul style="padding-left:20px; margin:10px 0;">', '<ol style="padding-left:20px; margin:10px 0;">'],
                                    $content['body'],
                                ) !!}
                            </div>

                            @if (!empty($content['body2']))
                                <div
                                    style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #f1f5f9; font-size: 14px; color: #64748b; font-style: italic;">
                                    {{ $content['body2'] }}
                                </div>
                            @endif

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                style="margin-top: 40px;">
                                <tr>
                                    <td>
                                        <p style="margin: 0; font-size: 14px; color: #1e293b;">Best regards,</p>
                                        <p style="margin: 0; font-size: 15px; font-weight: 800; color: #0891b2;">MephEd
                                            Support Team</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td class="footer">
                            <p style="margin: 0 0 10px 0;">&copy; {{ date('Y') }} MephEd. All rights reserved.</p>
                            <p style="margin: 0;">
                                Changed your mind?
                                <a href="{{ $unsubscribeUrl }}"
                                    style="color: #0891b2; text-decoration: underline; font-weight: bold;">
                                    Click here to Unsubscribe
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
