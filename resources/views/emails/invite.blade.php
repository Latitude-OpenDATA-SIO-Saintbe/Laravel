{{--

    This Blade template is used to generate an email invitation for users to register.
    The email contains a title, a message inviting the user to register, and a link to complete the registration process.
    If an expiration date is provided, it will display a note indicating when the invitation will expire.
    The template uses the following variables:
    - $token: The unique token for the registration link.
    - $expires_at: (optional) The expiration date and time for the invitation.
--}}
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
