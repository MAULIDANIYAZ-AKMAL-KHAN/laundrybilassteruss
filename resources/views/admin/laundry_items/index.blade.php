<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Master Data Layanan - {{ config('app.name', 'Bilassss Terussss') }}</title>
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
        <h1>Master Data Layanan</h1>
        <a href="{{ route('laundry_items.create') }}" class="button button-primary">+ Tambah Layanan</a>
    </div>
        </div>
    </div>
    <main class="admin-main">
@if (session('success'))
    <div class="admin-notice notice-success" role="status">{{ session('success') }}</div>
@endif
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th class="p-3">Gambar</th><th class="p-3">Nama</th><th class="p-3">Kategori</th><th class="p-3">Harga</th><th class="p-3">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($laundry_items as $laundryItem)
                <tr>
                    <td>
                        <img src="{{ $laundryItem->image_url }}" alt="{{ $laundryItem->name }}" class="admin-service-image">
                    </td>
                    <td class="service-name">{{ $laundryItem->name }}</td>
                    <td>{{ $laundryItem->category }}</td>
                    <td class="admin-price">Rp {{ number_format($laundryItem->price, 0, ',', '.') }}</td>
                    <td class="service-actions">
                        <a href="{{ route('laundry_items.edit', $laundryItem) }}" class="edit-link">Edit</a>
                        <form action="{{ route('laundry_items.destroy', $laundryItem) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus layanan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-link">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-row">Belum ada layanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="admin-pagination">{{ $laundry_items->links() }}</div>
    </main>
</body>
</html>
