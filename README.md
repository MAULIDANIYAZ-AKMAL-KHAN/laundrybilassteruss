# Bilassss Terussss

Aplikasi pengelolaan layanan dan pesanan laundry dengan Laravel 12, Blade, dan Vite.
Zona waktu aplikasi menggunakan WIB (`Asia/Jakarta`).

## Menjalankan aplikasi

Persyaratan: PHP 8.2+, Composer, Node.js, npm, dan MySQL.

1. Pasang dependency:

   ```sh
   composer install
   npm install
   ```

2. Salin `.env.example` menjadi `.env`, lalu atur koneksi database:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laundry
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Pastikan database `laundry` sudah dibuat di MySQL.

3. Siapkan aplikasi dan data awal:

   ```sh
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan storage:link
   npm run build
   php artisan serve
   ```

   `migrate:fresh` menghapus tabel dan data yang sudah ada pada database yang dipilih. Untuk database yang berisi data penting, gunakan `php artisan migrate` sebagai gantinya.

4. Buka alamat yang ditampilkan oleh `php artisan serve`. Untuk pengembangan frontend, jalankan `npm run dev` di terminal terpisah.

## Akun awal

Seeder membuat akun admin berikut:

- Email: `admin@gmail.com`
- Password: `password123`

Seeder juga membuat akun kasir:

- Email: `kasir@gmail.com`
- Password: `password123`

Ubah password awal sebelum aplikasi digunakan secara publik.

## Fitur

- Katalog layanan dengan filter kategori, harga per kg, estimasi selesai, ilustrasi lokal, dan formulir pemesanan.
- CRUD layanan, termasuk harga per kg, estimasi selesai, unggah dan penghapusan gambar.
- Pemesanan mencatat nomor telepon/WhatsApp pelanggan.
- Struk ringkas muncul sebagai kotak di tengah halaman setelah pesanan dikirim.
- Nomor pesanan berurutan per tanggal dan dimulai kembali dari 1 setiap hari; penghapusan pesanan merapikan nomor hari tersebut.
- Pemesanan dibuka pukul 05.00–00.00 WIB dan ditutup sementara pukul 00.00–05.00 WIB.
- Login admin, kasir, dan pelanggan; kasir dapat mengakses dashboard, mencatat transaksi, memperbarui status, dan melihat rekap tanpa mengelola layanan.
- Dashboard admin dengan menu Data Layanan dan Rekap Transaksi.
- Admin dapat mengelola layanan dan transaksi; status transaksi dapat diubah dari Proses menjadi Selesai.
- Rekap transaksi menampilkan data pelanggan, layanan terkait, status, dan pendapatan transaksi selesai.
- Relasi transaksi ke pelanggan dan detail layanan; harga detail menyimpan subtotal saat transaksi dibuat.
- Seeder berisi layanan Reguler (Rp 7.000/kg, 3-4 hari), Ekspres (Rp 12.000/kg, 1-2 hari), dan Superkilat (Rp 25.000/kg, 5 jam selesai).

## Ringkasan query

CRUD layanan menggunakan Eloquent: `LaundryItem::latest()->paginate(10)` membaca dan membatasi daftar, `create()` menambah baris, `update()` mengubah baris, dan `delete()` menghapusnya. Filter katalog dilakukan pada browser berdasarkan kategori.

Checkout memakai transaksi database: buat satu baris `orders`, buat baris `order_details` untuk setiap layanan yang dipilih, lalu simpan jumlah harga ke `orders.total_price`. Transaksi memastikan seluruh perubahan dibatalkan jika satu langkah gagal. Dashboard mengambil relasi dengan `Order::with('orderDetails.laundryItem')` agar rincian layanan dapat ditampilkan bersama pesanan.

## Pemeriksaan

```sh
php artisan test
npm run build
```
