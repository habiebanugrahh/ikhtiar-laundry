# Ikhtiar Laundry

Sistem rekapitulasi pendapatan harian dan bulanan untuk Ikhtiar Laundry berbasis web. Sistem ini membantu Kasir mencatat transaksi dan pembayaran, serta membantu Owner memantau uang yang benar-benar diterima setiap hari dan setiap bulan.

Proyek ini dikembangkan dengan metode Waterfall sebagai bagian dari tugas kuliah, mengacu pada dokumen proposal "Implementasi Sistem Rekapitulasi Pendapatan Harian dan Bulanan untuk Ikhtiar Laundry berbasis Web".

## Fitur

### Kasir
- Login dengan username dan kata sandi
- Dashboard operasional: jumlah transaksi hari ini, transaksi menunggu pembayaran, pesanan dalam proses, daftar tagihan yang perlu ditagih, dan transaksi terbaru
- Input transaksi: pilih atau tambah pelanggan, beberapa layanan dalam satu transaksi, kuantitas (kg atau pcs), biaya ekspres, catatan pesanan
- Total biaya dihitung otomatis, pembayaran awal opsional (tunai dengan kembalian, transfer, QRIS)
- Pembayaran lanjutan untuk pelunasan atau cicilan
- Mengubah status pesanan: diterima, diproses, selesai, diambil
- Cetak nota A4
- Mencari dan menambah data pelanggan
- Melihat transaksi hari ini dan transaksi yang belum lunas

### Owner
- Seluruh fitur Kasir tanpa terkecuali, termasuk dashboard operasional dan melihat semua transaksi tanpa batasan
- Dashboard analitik: pendapatan hari ini dan bulan ini, transaksi belum lunas, grafik pendapatan (7 hari, 30 hari, 12 bulan)
- Pengaturan tarif layanan (tambah, ubah, aktifkan atau nonaktifkan)
- Data karyawan (kelola akun Kasir)
- Melihat seluruh transaksi dengan pencarian dan filter (status, rentang tanggal)
- Rekap pendapatan harian
- Rekap pendapatan bulanan dengan grafik tren harian
- Halaman Laporan: pratinjau lalu unduh PDF atau Excel

### Semua pengguna
- Ubah kata sandi sendiri

## Aturan bisnis penting

1. Setiap transaksi memiliki nomor unik dengan format `IKH-YYYYMMDD-NNN`.
2. Total biaya = kuantitas x tarif untuk setiap layanan, ditambah biaya ekspres jika dipilih. Perhitungan selalu dilakukan di server.
3. Pendapatan dihitung dari **uang yang benar-benar diterima**. Transaksi yang belum dibayar tidak dihitung sebagai uang masuk.
4. Pembayaran sebagian disimpan beserta sisa tagihan. Pembayaran berikutnya menambah akumulasi pembayaran.
5. Rekap harian dan bulanan mengelompokkan pembayaran berdasarkan **tanggal uang diterima**, bukan tanggal transaksi dibuat.
6. Perubahan tarif hanya berlaku untuk transaksi baru. Tarif pada transaksi lama tidak berubah.
7. Hak akses dibatasi di sisi server dengan middleware, sehingga halaman terlarang tetap tertolak walaupun URL diketik langsung.

## Teknologi

- PHP 8.3 dan Laravel 13 (pola MVC)
- MySQL
- Tailwind CSS 4 dan Alpine.js, dibangun dengan Vite
- Chart.js untuk grafik
- dompdf untuk PDF dan PhpSpreadsheet untuk Excel

## Persyaratan

- PHP 8.3 atau lebih baru dengan ekstensi `gd`, `zip`, `mbstring`, `xml`, dan `dom`
- Composer
- Node.js dan npm
- MySQL

## Instalasi

```bash
git clone <url-repositori> ikhtiar-laundry
cd ikhtiar-laundry

composer install
cp .env.example .env
php artisan key:generate
```

Buat database:

```bash
mysql -u root -p -e "CREATE DATABASE ikhtiar_laundry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Atur `.env`:

```env
APP_NAME="Ikhtiar Laundry"
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ikhtiar_laundry
DB_USERNAME=root
DB_PASSWORD=
```

Atur zona waktu di `config/app.php`:

```php
'timezone' => 'Asia/Jakarta',
```

Migrasi database dan isi data awal:

```bash
php artisan migrate --seed
```

Pasang dan bangun aset:

```bash
npm install
npm run build
```

Jalankan aplikasi:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`. Selama pengembangan, jalankan `npm run dev` di terminal terpisah agar perubahan tampilan langsung terlihat.

## Akun contoh (lingkungan pengembangan)

| Role | Username | Kata sandi |
|---|---|---|
| Owner | `owner` | `owner123` |
| Kasir | `kasir1` | `kasir123` |

Ganti kata sandi akun ini lewat menu **Ubah kata sandi** sebelum sistem dipakai sungguhan.

Saat `APP_ENV=local`, seeder juga membuat 12 pelanggan dan transaksi 90 hari terakhir sebagai data contoh. Data contoh ini tidak dibuat di lingkungan lain. Tarif dan nama layanan di seeder hanya contoh, ganti lewat menu Pengaturan Tarif.

## Perintah yang berguna

```bash
php artisan migrate:fresh --seed   # reset database (menghapus semua data)
php artisan laundry:verify         # memeriksa konsistensi data transaksi, pembayaran, dan rekap
npm run build                      # membangun aset untuk produksi
```

## Struktur folder utama

```
app/
  Console/Commands/     perintah laundry:verify
  Exports/              pembuat berkas Excel laporan
  Http/Controllers/     controller (auth, transaksi, pembayaran, pelanggan, tarif, karyawan, laporan)
  Http/Middleware/      pembatasan akses berdasarkan role
  Models/               User, Customer, Service, Transaction, TransactionItem, Payment
  Support/              Format (rupiah) dan IncomeReport (data laporan)
database/
  migrations/           struktur tabel
  seeders/              akun, layanan, dan data contoh
resources/
  css/ js/              aset Tailwind, Alpine, dan Chart.js (dibangun Vite)
  views/                tampilan Blade
lang/id/                pesan validasi Bahasa Indonesia
```

## Basis data

| Tabel | Isi |
|---|---|
| `users` | akun dengan `username`, `role` (owner atau cashier), dan status aktif |
| `customers` | nama, nomor telepon, alamat |
| `services` | nama layanan, satuan, tarif, status aktif |
| `transactions` | nomor, pelanggan, pencatat, tanggal, ekspres, total, akumulasi bayar, status pembayaran, status pesanan |
| `transaction_items` | rincian layanan per transaksi (nama, satuan, dan tarif disalin saat transaksi dibuat) |
| `payments` | setiap pembayaran yang diterima, dasar rekap harian dan bulanan |

## Pengujian

Pengujian dilakukan dengan metode black box, mencakup otorisasi akses antarperan, akurasi perhitungan, serta pencetakan nota dan ekspor laporan. Untuk memeriksa akurasi perhitungan secara otomatis, jalankan `php artisan laundry:verify`.
