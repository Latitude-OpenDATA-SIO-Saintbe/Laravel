{{--
    This Blade template renders a form for users to request a password reset link.
    The form uses the POST method to send the request to the 'password.email' route.
    It includes CSRF protection and handles validation errors for the email input field.

    Fields:
    - email: The user's email address, required and pre-filled with old input if available.

    Buttons:
    - Send Reset Link: Submits the form to request a password reset link.
--}}
<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        @error('email')
            <div>{{ $message }}</div>
        @enderror
    </div>

    <div>
        <button type="submit">Send Reset Link</button>
    </div>
</form>
