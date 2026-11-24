<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rekap Transaksi - {{ config('app.name', 'Bilassss Terussss') }}</title>
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
                <h1>Rekap Transaksi Laundry</h1>
                <a href="{{ route('customer.index') }}" class="button button-primary">Transaksi Baru</a>
            </div>
        </div>
    </div>
    <main class="admin-main">
        <section class="report-summary" aria-label="Ringkasan transaksi">
            <article class="report-summary-card report-summary-transactions">
                <p>Jumlah Transaksi</p>
                <strong>{{ number_format($summary['transactions'], 0, ',', '.') }}</strong>
            </article>
            <article class="report-summary-card report-summary-processing">
                <p>Transaksi Proses</p>
                <strong>{{ number_format($summary['processing'], 0, ',', '.') }}</strong>
            </article>
            <article class="report-summary-card report-summary-completed">
                <p>Transaksi Selesai</p>
                <strong>{{ number_format($summary['completed'], 0, ',', '.') }}</strong>
            </article>
            <article class="report-summary-card report-summary-cancelled">
                <p>Transaksi Dibatalkan</p>
                <strong>{{ number_format($summary['cancelled'], 0, ',', '.') }}</strong>
            </article>
            <article class="report-summary-card report-summary-revenue">
                <p>Pendapatan Selesai</p>
                <strong>Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</strong>
            </article>
        </section>
        <div class="report-section-heading">
            <h2>Detail Transaksi</h2>
            <span>{{ number_format($summary['transactions'], 0, ',', '.') }} data</span>
        </div>
        <section class="report-transaction-list" aria-label="Daftar transaksi">
            @forelse ($orders as $order)
                <article class="report-transaction-card">
                    <div class="report-transaction-heading">
                        <strong>#{{ $order->order_number ?? $order->id }}</strong>
                        <span class="status-badge status-{{ strtolower($order->status) }}">
                            {{ ['pending' => 'Proses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'][strtolower($order->status)] ?? ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="report-transaction-fields">
                        <div class="report-field">
                            <span>Pelanggan</span>
                            <strong>{{ $order->customer?->name ?? $order->customer_name }}</strong>
                        </div>
                        <div class="report-field">
                            <span>Telepon</span>
                            <strong>{{ $order->customer?->phone_number ?? $order->phone_number ?? 'Belum tersedia' }}</strong>
                        </div>
                        <div class="report-field report-services">
                            <span>Jenis Layanan</span>
                            <ul>
                                @foreach ($order->orderDetails as $detail)
                                    <li>{{ $detail->laundryItem?->name ?? 'Layanan dihapus' }} x{{ $detail->quantity }} kg</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="report-field report-total">
                            <span>Total Transaksi</span>
                            <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </article>
            @empty
                <p class="report-empty">Belum ada transaksi.</p>
            @endforelse
        </section>
    </main>
</body>
</html>
