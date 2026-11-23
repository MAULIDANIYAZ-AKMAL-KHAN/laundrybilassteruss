<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Layanan - {{ config('app.name', 'Bilassss Terussss') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-site">
    <header class="admin-topbar">
        <div class="admin-bar-inner">
            <a href="{{ route('dashboard') }}" class="admin-mark" aria-label="Bilassss Terussss">
                <svg viewBox="0 0 32 32" aria-hidden="true">
                    <path d="M16 3 27 9.5v13L16 29 5 22.5v-13L16 3Z" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m16 3 0 13m0 0 11-6.5M16 16 5 9.5m11 6.5v13" fill="none" stroke="currentColor" stroke-width="1.8"/>
                </svg>
            </a>
            <a href="{{ route('dashboard') }}" class="admin-brand">Bilassss Terussss</a>
            <nav class="admin-nav">
                <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard', 'admin.dashboard')])>Dashboard</a>
                <a href="{{ route('laundry_items.index') }}" @class(['active' => request()->routeIs('laundry_items.*')])>Kelola Layanan</a>
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
        <h1>Edit Layanan</h1>
        <a href="{{ route('laundry_items.index') }}" class="button button-muted">Kembali ke Layanan</a>
    </div>
        </div>
    </div>
    <main class="admin-main">
<form action="{{ route('laundry_items.update', $laundryItem) }}" method="POST" enctype="multipart/form-data" class="admin-form">
    @csrf
    @method('PUT')
    @if ($errors->any())
        <div class="form-errors" role="alert">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div>
        <label for="name">Nama Layanan</label>
        <input id="name" name="name" value="{{ old('name', $laundryItem->name) }}" required maxlength="255">
    </div>
    <div>
        <label for="category">Kategori</label>
        <select id="category" name="category" required>
            @foreach (['Cuci', 'Setrika', 'Paket'] as $category)
                <option value="{{ $category }}" @selected(old('category', $laundryItem->category) === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="price">Harga (Rp)</label>
        <input id="price" name="price" type="number" min="0" step="1" value="{{ old('price', $laundryItem->price) }}" required>
    </div>
    <div>
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="3" required>{{ old('description', $laundryItem->description) }}</textarea>
    </div>
    <div>
        <label for="image">Gambar (opsional)</label>
        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png">
        <img src="{{ $laundryItem->image_url }}" alt="{{ $laundryItem->name }}" class="form-image-preview">
    </div>
    <div class="form-actions">
        <button type="submit" class="button button-primary">Update Data</button>
        <a href="{{ route('laundry_items.index') }}" class="button button-muted">Batal</a>
    </div>
</form>
    </main>
</body>
</html>
