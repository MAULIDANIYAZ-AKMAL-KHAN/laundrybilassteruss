<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Layanan Laundry - {{ config('app.name', 'Bilassss Terussss') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-site customer-site">
    <header class="customer-header">
        <div class="customer-actions">
            @auth
                <span class="account-indicator" title="Akun yang sedang masuk">
                    {{ auth()->user()->name }}
                </span>
                @if (auth()->user()->is_admin || auth()->user()->is_cashier)
                    <a href="{{ route('dashboard') }}" class="top-action">Dashboard</a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="top-action top-action-quiet" type="submit">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="top-action top-action-quiet">Login</a>
            @endauth
        </div>
        <div class="customer-brand">
            <img src="{{ asset('images/laundry-logo.svg') }}" alt="" class="customer-logo">
            <div>
                <h1>Bilassss Terussss</h1>
                <p>Pilih layanan dan masukkan nomor WhatsApp Anda</p>
            </div>
        </div>
    </header>
    <main class="customer-main">
@if (session('success') && !session('receipt'))
    <div class="customer-notice notice-success" role="status">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="customer-notice notice-error" role="alert">{{ session('error') }}</div>
@endif
@unless ($isOrderingOpen)
    <div class="customer-notice notice-error" role="status">
        Pemesanan tutup pukul 00.00–05.00 WIB. Kami buka kembali pukul 05.00 WIB.
    </div>
@endunless
@if ($errors->any())
    <div class="customer-notice notice-error" role="alert">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="category-filters" aria-label="Filter kategori">
    <button type="button" data-filter="all" class="category-filter active">Semua Layanan</button>
    @foreach (['Cuci', 'Setrika', 'Paket'] as $category)
        <button type="button" data-filter="{{ $category }}" class="category-filter">{{ $category }}</button>
    @endforeach
</div>

<form action="{{ route('customer.checkout') }}" method="POST">
    @csrf
    <section class="order-info">
        <h2>1. Informasi Pemesan</h2>
        <div class="order-fields">
            <div>
                <label for="customer_name">Nama Lengkap</label>
                <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required maxlength="255" placeholder="Masukkan nama pemesan">
            </div>
            <div>
                <label for="phone_number">Nomor Telepon / WhatsApp</label>
                <input id="phone_number" name="phone_number" type="tel" inputmode="tel" autocomplete="tel" minlength="8" maxlength="25" value="{{ old('phone_number') }}" required placeholder="Contoh: 081234567890">
            </div>
        </div>
    </section>

    <h2 class="services-title">2. Pilih Layanan</h2>
    @if ($laundry_items->isEmpty())
        <p class="empty-services">Belum ada layanan yang tersedia.</p>
    @else
        <div class="laundry-grid">
            @foreach ($laundry_items as $laundryItem)
                <article data-category="{{ $laundryItem->category }}" class="laundry-card">
                    <div>
                        <img src="{{ $laundryItem->image_url }}" alt="{{ $laundryItem->name }}" class="service-image">
                        <div class="service-details">
                            <div class="service-meta">
                                <span class="service-category">{{ $laundryItem->category }}</span>
                                <span class="service-price">Rp {{ number_format($laundryItem->price, 0, ',', '.') }}/kg</span>
                            </div>
                            <h3>{{ $laundryItem->name }}</h3>
                            <p>{{ $laundryItem->description }}</p>
                            <p>Estimasi: {{ $laundryItem->estimated_duration }}</p>
                        </div>
                    </div>
                    <div class="service-quantity">
                        <label for="item-{{ $laundryItem->id }}">Jumlah (kg)</label>
                        <input id="item-{{ $laundryItem->id }}" type="number" name="items[{{ $laundryItem->id }}]" min="0" step="1" value="{{ old('items.'.$laundryItem->id, 0) }}" data-price="{{ $laundryItem->price }}">
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <div class="customer-submit">
        <p class="customer-total">Total: <strong id="orderTotal">Rp 0</strong></p>
        <button type="submit" @disabled(! $isOrderingOpen)>Pesan Sekarang</button>
    </div>
</form>

@if (session()->has('receipt'))
    @php($receipt = session('receipt'))
    <div class="receipt-overlay" id="receiptModal" role="dialog" aria-modal="true" aria-labelledby="receiptTitle">
        <section class="receipt-card">
            <button type="button" class="receipt-close" onclick="closeReceipt()" aria-label="Tutup struk">&times;</button>
            <p class="receipt-brand">
                <img src="{{ asset('images/laundry-logo.svg') }}" alt="">
                Bilassss Terussss
            </p>
            <h2 id="receiptTitle">Pesanan berhasil dibuat!</h2>
            <p class="receipt-number">Struk #{{ $receipt['number'] }} &middot; {{ $receipt['business_date'] }} {{ $receipt['created_at'] }}</p>
            <div class="receipt-customer">
                <span>Nama</span><strong>{{ $receipt['customer_name'] }}</strong>
                <span>WhatsApp</span><strong>{{ $receipt['phone_number'] }}</strong>
            </div>
            <div class="receipt-items">
                @foreach ($receipt['items'] as $item)
                    <div class="receipt-item">
                        <span>{{ $item['name'] }} &times; {{ $item['quantity'] }}</span>
                        <strong>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
            <div class="receipt-total">
                <span>Total</span>
                <strong>Rp {{ number_format($receipt['total_price'], 0, ',', '.') }}</strong>
            </div>
            <button type="button" class="button button-primary receipt-done" onclick="closeReceipt()">Tutup</button>
        </section>
    </div>
@endif

<script>
    const quantityInputs = document.querySelectorAll('input[name^="items["]');
    const orderTotal = document.getElementById('orderTotal');

    function updateOrderTotal() {
        const total = Array.from(quantityInputs).reduce((sum, input) => {
            const quantity = Number(input.value);

            return sum + (Number.isInteger(quantity) && quantity > 0
                ? quantity * Number(input.dataset.price)
                : 0);
        }, 0);

        if (orderTotal) {
            orderTotal.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;
        }
    }

    quantityInputs.forEach((input) => input.addEventListener('input', updateOrderTotal));
    updateOrderTotal();

    document.querySelectorAll('.category-filter').forEach((button) => {
        button.addEventListener('click', () => {
            const category = button.dataset.filter;
            document.querySelectorAll('.category-filter').forEach((filterButton) => {
                filterButton.classList.remove('active');
            });
            button.classList.add('active');
            document.querySelectorAll('.laundry-card').forEach((card) => {
                card.hidden = category !== 'all' && card.dataset.category !== category;
            });
        });
    });

    function closeReceipt() {
        const receipt = document.getElementById('receiptModal');
        if (receipt) {
            receipt.remove();
        }
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeReceipt();
        }
    });
</script>
    </main>
</body>
</html>
