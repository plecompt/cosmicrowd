@vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/background-stars.js'])

<div class="change-email-container">
    <h2>Change your email address</h2>
    <p>
        To update the email address associated with your account, please complete the form below.
        You will need to confirm your current password for security reasons.
        After submitting the request, you will be automatically logged out, and you’ll need to log in again using your
        new email address.
    </p>

    <form method="POST" action="{{route('change-email-submit')}}">
        <input type="password" name="current-password" placeholder="Current password">
        <input type="password" name="current-password-bis" placeholder="Repeat current password">

        <!-- @if (changeEmailForm.hasError('passwordsMismatch') && changeEmailForm.touched) {
        <small class="error">Passwords do not match</small>
        } -->

        <input type="email" name="new-email" placeholder="New email">
        <input type="email" name="new-email-bis" placeholder="Repeat new email">

        <!-- @if (changeEmailForm.hasError('emailsMismatch') && changeEmailForm.touched) {
        <small class="error">Emails do not match</small>
        } -->

        <button type="submit">Submit</button>
    </form>
</div>
<div class="stars-container"></div>
