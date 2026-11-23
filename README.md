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

Ubah password awal sebelum aplikasi digunakan secara publik.

## Fitur

- Katalog layanan dengan filter kategori, ilustrasi lokal, dan formulir pemesanan.
- CRUD layanan, termasuk unggah dan penghapusan gambar sendiri.
- Pemesanan mencatat nomor telepon/WhatsApp pelanggan.
- Struk ringkas muncul sebagai kotak di tengah halaman setelah pesanan dikirim.
- Nomor pesanan berurutan per tanggal dan dimulai kembali dari 1 setiap hari; penghapusan pesanan merapikan nomor hari tersebut.
- Pemesanan dibuka pukul 05.00–00.00 WIB dan ditutup sementara pukul 00.00–05.00 WIB.
- Login, pendaftaran akun pelanggan, dan logout; akun pelanggan hanya dapat memesan.
- Dashboard, CRUD layanan, perubahan status, dan penghapusan pesanan hanya dapat diakses akun admin.
- Dashboard admin untuk melihat pesanan, menghubungi pelanggan melalui WhatsApp, memperbarui status, dan menghapus pesanan.
- Relasi pesanan ke detail pesanan dan layanan; harga detail menyimpan subtotal saat pesanan dibuat.
- Seeder berisi lima contoh layanan.

## Ringkasan query

CRUD layanan menggunakan Eloquent: `LaundryItem::latest()->paginate(10)` membaca dan membatasi daftar, `create()` menambah baris, `update()` mengubah baris, dan `delete()` menghapusnya. Filter katalog dilakukan pada browser berdasarkan kategori.

Checkout memakai transaksi database: buat satu baris `orders`, buat baris `order_details` untuk setiap layanan yang dipilih, lalu simpan jumlah harga ke `orders.total_price`. Transaksi memastikan seluruh perubahan dibatalkan jika satu langkah gagal. Dashboard mengambil relasi dengan `Order::with('orderDetails.laundryItem')` agar rincian layanan dapat ditampilkan bersama pesanan.

## Pemeriksaan

```sh
php artisan test
npm run build
```
