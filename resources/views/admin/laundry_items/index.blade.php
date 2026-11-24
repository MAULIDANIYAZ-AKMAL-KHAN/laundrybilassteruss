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
        <h1>Master Data Layanan</h1>
        <a href="{{ route('laundry_items.create') }}" class="button button-primary">+ Tambah Layanan</a>
    </div>
        </div>
    </div>
    <main class="admin-main">
@if (session('success'))
    <div class="admin-notice notice-success" role="status">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="admin-notice notice-error" role="alert">{{ session('error') }}</div>
@endif
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th class="p-3">Gambar</th><th class="p-3">Nama</th><th class="p-3">Kategori</th><th class="p-3">Harga/kg</th><th class="p-3">Estimasi</th><th class="p-3">Aksi</th></tr>
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
                    <td>{{ $laundryItem->estimated_duration }}</td>
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
                <tr><td colspan="6" class="empty-row">Belum ada layanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="admin-pagination">{{ $laundry_items->links() }}</div>
    </main>
</body>
</html>
