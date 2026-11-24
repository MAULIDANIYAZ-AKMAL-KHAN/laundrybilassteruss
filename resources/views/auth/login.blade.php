<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Bilassss Terussss') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-site auth-site">
    <header class="guest-nav">
        <a href="{{ route('customer.index') }}" class="guest-brand">
            <img src="{{ asset('images/laundry-logo.svg') }}" alt="">
            <span>Bilassss Terussss</span>
        </a>
        <nav>
            <a href="{{ route('customer.index') }}">Layanan</a>
        </nav>
    </header>
    <main class="auth-main">
<div class="auth-card">
    <h1>Login</h1>
    @if ($errors->any())
        <div class="form-errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('login.store') }}" method="POST" class="auth-form">
        @csrf
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>
        <div>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
        </div>
        <label class="remember-label"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
        <button type="submit" class="button button-primary">Masuk</button>
    </form>
    <p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
</div>
    </main>
</body>
</html>
