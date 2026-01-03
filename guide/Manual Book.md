# Situs Web E-Commerce Top-Up Game

## Platform E-Commerce Admin & User (Seperti MidasBuy, UniPin)

Ini adalah platform e-commerce lengkap untuk top-up game dengan antarmuka Admin dan User terpisah.

## Fitur

### Fitur User

- **Registrasi & Login User** - Autentikasi aman dengan hashing password
- **Belanja Game** - Jelajahi game dan lihat paket yang tersedia
- **Keranjang Belanja** - Tambah/hapus item, update jumlah
- **Checkout** - Buat pesanan dengan ID user game dan pemilihan metode pembayaran
- **Riwayat Pesanan** - Lihat semua pesanan sebelumnya dan statusnya
- **Manajemen Profil** - Update nama, email, dan password

### Fitur Admin

- **Dashboard** - game, produk, paket, dan pesanan
- **Manajemen Game** - Tambah, edit, dan hapus game (termasuk upload gambar)
- **Manajemen Produk** - Kelola produk digital untuk setiap game (termasuk upload gambar)
- **Manajemen Paket** - Buat tingkat harga untuk produk
- **Manajemen Pesanan** - Lihat dan update status pesanan
- **Manajemen Pembayaran** - Lacak pembayaran dan log top-up

## Skema Database

Sistem menggunakan 9 tabel:

1. **users** - Akun user dengan peran (admin/user)
2. **games** - Game yang tersedia (dengan dukungan gambar)
3. **digital_products** - Produk/item spesifik game (dengan dukungan gambar)
4. **product_packages** - Paket harga untuk produk
5. **orders** - Pesanan pelanggan
6. **order_items** - Item dalam setiap pesanan
7. **payments** - Catatan pembayaran
8. **topup_logs** - Log transaksi top-up
9. **Tabel metadata tambahan**

## Manajemen Gambar

Sistem mendukung upload gambar untuk game dan produk untuk meningkatkan daya tarik visual.

### Format Gambar yang Didukung

- JPG/JPEG
- PNG
- Ukuran file maksimal: 5MB

### Penyimpanan Gambar

- Gambar disimpan di direktori `assets/images/`
- Nama file dihasilkan secara otomatis dengan ID unik untuk mencegah konflik

#### Untuk Game:

1. Pergi ke Dashboard Admin → Games
2. Klik "Add New Game" atau edit game yang ada
3. Di field "Game Image", pilih file gambar
4. Klik "Add Game" atau "Update Game"
5. Gambar akan ditampilkan di card game di halaman shop

#### Untuk Produk:

1. Pergi ke Dashboard Admin → Products
2. Klik "Add New Product" atau edit produk yang ada
3. Di field "Product Image", pilih file gambar
4. Klik "Add Product" atau "Update Product"
5. Gambar akan ditampilkan di card produk di halaman shop

### Tampilan Antarmuka User

#### Gambar Game:

- Ditampilkan di atas card game di halaman shop
- Desain responsif dengan tinggi maksimal 250px
- Visibilitas gambar penuh tanpa pemotongan

#### Gambar Produk:

- Ditampilkan di header card produk
- Desain responsif dengan tinggi maksimal 300px
- Visibilitas gambar penuh tanpa pemotongan

### Contoh Penggunaan Gambar

#### Contoh Card Game:

+------------------------+
| [Gambar Game Di Sini] |
| |
| Judul Game |
| Publisher: XXX |
| [Tombol Shop Sekarang] |
+------------------------+

#### Contoh Card Produk:

+---------------------+
| Judul Produk |
| [Gambar Produk] |
| +-----------------+ |
| | Jumlah | Harga | |
| +-----------------+ |
| | 100 | Rp10k | |
| | 200 | Rp20k | |
| +-----------------+ |
+---------------------+

## Instruksi Setup

### Prasyarat

- XAMPP (Apache, PHP 7.4+, MySQL 5.7+)
- Lingkungan Windows

### Langkah Instalasi

1. **Salin File Proyek**

   - Salin folder `UAS_PHP` ke `C:\xampp\htdocs\`

2. **Setup Database**

   - Buka Command Line MySQL atau phpMyAdmin
   - Import file `topupgames.sql`:

   bash
   mysql -u root < topupgames.sql

3. **Konfigurasi Database**

   - Edit `config.php` dengan kredensial database Anda (default adalah root tanpa password)
   - Nama database harus: `game_topup`

4. **Mulai Layanan**

   - Buka XAMPP Control Panel
   - Aktifkan layanan Apache dan MySQL

5. **Akses Website**
   - User: `http://localhost/UAS_PHP/`
   - Admin: Buat akun admin terlebih dahulu, lalu login ke `http://localhost/UAS_PHP/admin/dashboard.php`

## Kredensial Test

### Buat Akun Admin

1. Pergi ke: `http://localhost/UAS_PHP/register.php`
2. Daftar akun baru dengan:
   - Nama: `Admin User`
   - Email: `admin@test.com`
   - Password: `password123` (atau password apa saja)
3. Saat registrasi, centang kotak "Register as Admin"

### Buat Akun User Biasa

1. Pergi ke: `http://localhost/UAS_PHP/register.php`
2. Daftar dengan:
   - Nama: `Test User`
   - Email: `user@test.com`
   - Password: `password123`

## Struktur File

UAS_PHP/
├── admin/
│ ├── dashboard.php
│ ├── games.php
│ ├── products.php
│ ├── packages.php
│ ├── orders.php
│ └── payments.php
├── user/
│ ├── shop.php
│ ├── cart.php
│ ├── checkout.php
│ └── orders.php
├── includes/
│ ├── functions.php
│ └── config.php
├── assets/
│ └── images/
├── guide/
│ ├── README.md
│ ├── SETUP_GUIDE.md
│ └── ...
├── index.php
├── login.php
├── register.php
├── profile.php
├── logout.php
├── test_db.php
├── test_server.php
├── topupgames.sql
└── verify_system.php

## Troubleshooting

### Masalah Upload Gambar

- **Error: File terlalu besar** - Pastikan gambar di bawah 5MB
- **Error: Format tidak valid** - Gunakan JPG, PNG, atau GIF saja
- **Gambar tidak tampil** - Periksa izin `assets/images/` dan path
- **Upload gagal** - Pastikan direktori `assets/images/` ada dan dapat ditulis

### Error Koneksi Database

- Verifikasi MySQL sedang berjalan
- Periksa `config.php` memiliki kredensial yang benar
- Pastikan database `game_topup` ada

### Error 404 Not Found

- Pastikan file ada di `C:\xampp\htdocs\UAS_PHP\`
- Verifikasi Apache sedang berjalan
- Bersihkan cache browser

## Lisensi

Proyek ini disediakan apa adanya untuk penggunaan pendidikan dan komersial.

**Terakhir Diupdate:** January 2, 2026
**Versi PHP:** 8.2.12
**Versi MySQL:** MariaDB 10.4.32
