<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Request Received</title>
</head>

<body>
    <p>Hi {{ $user->name }},</p>

    <p>Thanks for submitting your request with MephEd. We've created an account for you so you can manage your requests
        and messages.</p>

    @if (!empty($resetUrl))
        <p>To set your password and complete your account setup, click the link below (this link will expire):</p>

        <p>
            <a href="{{ $resetUrl }}">Set your password</a>
        </p>

        <p>If you didn't request this, you can ignore this email.</p>
    @endif

    <p>Request details:</p>
    <ul>
        <li>Request ID: {{ $requestModel->id ?? 'N/A' }}</li>
        @if (isset($requestModel->service_item_id))
            <li>Service Item ID: {{ $requestModel->service_item_id }}</li>
        @endif
        @if (isset($requestModel->status))
            <li>Status: {{ $requestModel->status }}</li>
        @endif

        @if ($requestModel instanceof \App\Models\Crm)
            <li>Institution: {{ $requestModel->institution_name }}</li>
            <li>Number of tutors required: {{ $requestModel->number_of_tutors_required }}</li>
            <li>Engagement type: {{ $requestModel->engagement_type }}</li>
        @elseif($requestModel instanceof \App\Models\TutorRequest)
            <li>Delivery mode: {{ $requestModel->delivery_mode }}</li>
            <li>Sessions per week: {{ $requestModel->sessions_per_week ?? 'N/A' }}</li>
        @endif
    </ul>

    <p>Best regards,<br />MephEd Team</p>
</body>

</html>
