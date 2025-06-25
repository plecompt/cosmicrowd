@vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/background-stars.js'])

<div class="container">

    <form method="POST" action="{{route('login-submit')}}">
            <h1>CosmiCrowd</h1>

            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Sign In</button>
            <a href="{{ route('forgot-password') }}'">Forgot password ?</a>
    </form>é

    <div class="stars-container"></div>
</div>