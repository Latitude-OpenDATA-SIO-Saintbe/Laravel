<!DOCTYPE html>
<html>
<head>
    <title>Invitation to Register</title>
</head>
<body>
    <h1>You have been invited to register!</h1>

    <p>We are excited to have you on board! To complete your registration, please click the link below:</p>

    <p>
        <a href="{{ url('/invite/' . $token) }}">Click here to register</a>
    </p>

    @if ($expires_at)
        <p>Note: This invitation will expire on {{ $expires_at->format('F j, Y, g:i a') }}.</p>
    @endif

    <p>If you did not request this invitation, you can safely ignore this message.</p>
</body>
</html>
