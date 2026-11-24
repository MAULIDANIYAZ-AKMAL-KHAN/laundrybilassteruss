<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tambah Layanan - {{ config('app.name', 'Bilassss Terussss') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-site">
    <header class="admin-topbar">
        <div class="admin-bar-inner">
            <a href="{{ route('dashboard') }}" class="admin-mark" aria-label="Bilassss Terussss">
                <img src="{{ asset('images/laundry-logo.svg') }}" alt="" class="admin-mark-image">
            </a>
            <a href="{{ route('dashboard') }}" class="admin-brand">Bilassss Terussss</a>
            <nav class="admin-nav">
                <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard', 'admin.dashboard')])>Dashboard</a>
                <a href="{{ route('laundry_items.index') }}" @class(['active' => request()->routeIs('laundry_items.*')])>Data Layanan</a>
                <a href="{{ route('admin.reports') }}" @class(['active' => request()->routeIs('admin.reports')])>Rekap Transaksi</a>
            </nav>
            <span class="admin-customer-link" title="Akun yang sedang masuk">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="admin-logout">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </div>
    </header>
    <div class="admin-heading">
        <div class="admin-heading-inner">
    <div class="page-title-row">
        <h1>Tambah Layanan</h1>
        <a href="{{ route('laundry_items.index') }}" class="button button-muted">Kembali ke Layanan</a>
    </div>
        </div>
    </div>
    <main class="admin-main">
<form action="{{ route('laundry_items.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
    @csrf
    @if ($errors->any())
        <div class="form-errors" role="alert">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div>
        <label for="name">Nama Layanan</label>
        <input id="name" name="name" value="{{ old('name') }}" required maxlength="255">
    </div>
    <div>
        <label for="category">Kategori</label>
        <select id="category" name="category" required>
            @foreach (['Cuci', 'Setrika', 'Paket'] as $category)
                <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="price">Harga per kg (Rp)</label>
        <input id="price" name="price" type="number" min="0" step="1" value="{{ old('price') }}" required>
    </div>
    <div>
        <label for="estimated_duration">Estimasi selesai</label>
        <input id="estimated_duration" name="estimated_duration" value="{{ old('estimated_duration') }}" required maxlength="100" placeholder="Contoh: 3-4 hari">
    </div>
    <div>
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
    </div>
    <div>
        <label for="image">Gambar Layanan (opsional)</label>
        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png">
    </div>
    <div class="form-actions">
        <button type="submit" class="button button-primary">Simpan Data</button>
        <a href="{{ route('laundry_items.index') }}" class="button button-muted">Batal</a>
    </div>
</form>
    </main>
</body>
</html>
