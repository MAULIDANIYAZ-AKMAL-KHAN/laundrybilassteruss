<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Pesanan - {{ config('app.name', 'Bilassss Terussss') }}</title>
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
                @if (auth()->user()->is_admin)
                    <a href="{{ route('laundry_items.index') }}" @class(['active' => request()->routeIs('laundry_items.*')])>Data Layanan</a>
                @endif
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
        <h1>Daftar Pesanan Masuk</h1>
        <a href="{{ route('customer.index') }}" class="button button-primary">Transaksi Baru</a>
    </div>
        </div>
    </div>
    <main class="admin-main">
@if (session('success'))
    <div class="admin-notice notice-success" role="status">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="admin-notice notice-error" role="alert">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
<div class="admin-table-wrap dashboard-table-wrap">
    <table class="admin-table dashboard-table">
        <thead>
            <tr>
                <th>ID</th><th>Pelanggan</th><th>Telepon / WhatsApp</th>
                <th>Rincian Pesanan</th><th>Total Harga</th><th>Status</th><th>Ubah Status / Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td class="order-id">#{{ $order->order_number ?? $order->id }}</td>
                    <td>{{ $order->customer?->name ?? $order->customer_name }}</td>
                    <td>
                        @if ($order->customer?->phone_number || $order->phone_number)
                            <a class="whatsapp-link" href="{{ $order->whatsapp_url }}" target="_blank" rel="noopener">
                                {{ $order->customer?->phone_number ?? $order->phone_number }}
                            </a>
                        @else
                            <span class="legacy-phone">Belum tersedia</span>
                        @endif
                    </td>
                    <td>
                        <ul class="order-detail-list">
                            @foreach ($order->orderDetails as $detail)
                                <li>
                                    {{ $detail->laundryItem?->name ?? 'Layanan dihapus' }} x{{ $detail->quantity }} kg
                                    — Rp {{ number_format($detail->price, 0, ',', '.') }}
                                    @if ($detail->laundryItem)
                                        (Estimasi: {{ $detail->laundryItem->estimated_duration }})
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="admin-price">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($order->status) }}">
                            {{ ['pending' => 'Proses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'][strtolower($order->status)] ?? ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="order-action-group">
                            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" aria-label="Status pesanan {{ $order->id }}" onchange="this.form.submit()" class="status-select">
                                    @foreach (['pending' => 'Proses', 'completed' => 'Selesai', 'cancelled' => 'Batalkan'] as $value => $label)
                                        <option value="{{ $value }}" @selected(strtolower($order->status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @if (auth()->user()->is_admin)
                                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pesanan #{{ $order->id }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-order">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-row">Belum ada pesanan masuk.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
    </main>
</body>
</html>
