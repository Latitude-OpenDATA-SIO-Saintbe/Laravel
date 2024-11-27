{{--

    This Blade template renders a password reset form for users. The form includes fields for the user's email, new password, and password confirmation.
    It uses Laravel's @csrf directive for CSRF protection and @error directive to display validation errors for the password field.
    The form submits a POST request to the 'password.update' route.
--}}
<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
    </div>

    <div>
        <label for="password">New Password</label>
        <input id="password" type="password" name="password" required>
        @error('password')
            <div>{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="password_confirmation">Confirm New Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>
    </div>

    <div>
        <button type="submit">Reset Password</button>
    </div>
</form>
