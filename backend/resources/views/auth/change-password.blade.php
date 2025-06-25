@if (this.authService.isLoggedIn()){
<div class="change-password-container">
    <h2>Change your password</h2>
    <p>
        To update your account password, please complete the form below.
        You will need to confirm your current password for security reasons.
        After submitting the request, you will be automatically logged out, and you’ll need to log in again using your new password.
    </p>

    <form method="POST" action="{{route('change-password-submit')}}">
        <input type="password" formControlName="current-password" placeholder="Current password">
        <input type="password" formControlName="current-password-bis" placeholder="Repeat current password">

        <!-- @if (changePasswordForm.hasError('passwordsMismatch') && changePasswordForm.touched) {
        <small class="error">Passwords do not match</small>
        } -->

        <input type="password" formControlName="new-password" placeholder="New password">
        <input type="password" formControlName="new-password-bis" placeholder="Repeat new password">

        <!-- @if (changePasswordForm.hasError('newPasswordMismatch') && changePasswordForm.touched) {
        <small class="error">New passwords do not match</small>
        } -->

        <button type="submit">Submit</button>
    </form>
</div>
<div class="stars-container"></div>
}