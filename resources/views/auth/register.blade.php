<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ config('app.name', 'Bilassss Terussss') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-site auth-site">
    <header class="guest-nav">
        <a href="{{ route('customer.index') }}" class="guest-brand">Bilassss Terussss</a>
        <nav>
            <a href="{{ route('customer.index') }}">Layanan</a>
            <a href="{{ route('login') }}" class="nav-button">Login</a>
        </nav>
    </header>
    <main class="auth-main">
<div class="auth-card">
    <h1>Buat Akun</h1>
    @if ($errors->any())
        <div class="form-errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('register.store') }}" method="POST" class="auth-form">
        @csrf
        <div>
            <label for="name">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
        </div>
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
        </div>
        <div>
            <label for="password">Password (minimal 8 karakter)</label>
            <input id="password" name="password" type="password" required autocomplete="new-password">
        </div>
        <div>
            <label for="password_confirmation">Ulangi Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </div>
        <button type="submit" class="button button-primary">Daftar</button>
    </form>
    <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
</div>
    </main>
</body>
</html>
