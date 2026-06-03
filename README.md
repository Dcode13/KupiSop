# CodeCoffee

CodeCoffee adalah aplikasi manajemen kedai kopi berbasis Laravel untuk menu publik,
POS kasir, antrian barista, stok bahan baku, pengguna berbasis role, laporan
penjualan, struk, dan pembayaran online.

Status deployment utama project ini:

- App hosting: Vercel
- Database production: TiDB Cloud Starter, MySQL-compatible
- Storage gambar produk: Supabase Storage S3-compatible
- Frontend assets: Vite

## Tech Stack

- PHP 8.2+
- Laravel 12
- Livewire 3
- Livewire Volt dan Laravel Breeze untuk auth
- Tailwind CSS 3
- Alpine.js
- MySQL / MariaDB / TiDB Cloud
- Supabase Storage S3-compatible untuk gambar produk
- Midtrans Core API untuk pembayaran online
- Spatie Laravel Permission untuk role
- barryvdh/laravel-dompdf untuk export PDF
- Vercel PHP runtime `vercel-php`

## Fitur Utama

- Halaman menu publik di `/menu`
- Pemesanan pelanggan tanpa login
- Pembayaran online via Midtrans: QRIS, GoPay, DANA via QRIS, BCA, BNI, BRI
- Pembayaran di kasir
- POS kasir di `/pos`
- Antrian pesanan barista di `/orders`
- Manajemen kategori, produk, gambar produk, dan bahan baku
- Resep produk melalui pivot `product_ingredient`
- Pengurangan stok otomatis ketika transaksi dibuat
- Manajemen pengguna dan role
- Riwayat transaksi
- Cetak struk HTML dan PDF
- Laporan penjualan dan export CSV/PDF

## Role dan Hak Akses

| Modul | Admin | Kasir | Barista |
| --- | --- | --- | --- |
| Dashboard | Ya | Ya | Ya |
| POS kasir | Ya | Ya | Tidak |
| Riwayat transaksi | Ya | Ya | Tidak |
| Struk transaksi | Ya | Ya | Tidak |
| Antrian pesanan | Ya | Tidak | Ya |
| Kategori | Ya | Tidak | Tidak |
| Produk | Ya | Tidak | Tidak |
| Bahan baku | Ya | Tidak | Tidak |
| Pengguna | Ya | Tidak | Tidak |
| Laporan | Ya | Tidak | Tidak |

## Akun Default Seeder

Akun ini dibuat oleh `php artisan migrate --seed`.

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@coffee.test` | `adminkupisop` |
| Kasir | `kasir@coffee.test` | `kasirkupisop` |
| Barista | `barista@coffee.test` | `baristakupisop` |

Ubah password default setelah aplikasi dipakai untuk production.

## Struktur Penting

```text
app/Livewire/Menu.php                 Halaman menu dan order publik
app/Livewire/Pos.php                  POS kasir
app/Livewire/Orders/Index.php         Antrian barista
app/Livewire/Products/Index.php       CRUD produk dan upload gambar
app/Services/OrderService.php         Penyimpanan transaksi dan pengurangan stok
app/Services/MidtransService.php      Integrasi pembayaran Midtrans Core API
config/filesystems.php                Disk public dan Supabase Storage
routes/web.php                        Route publik, auth, role, webhook Midtrans
api/index.php                         Bootstrap Laravel untuk Vercel serverless
vercel.json                           Konfigurasi deploy Vercel
Dockerfile                            Opsi deploy Docker/Render
```

## Instalasi Lokal

### 1. Clone dan install dependency

```bash
composer install
npm install
```

### 2. Buat file environment

```bash
cp .env.example .env
php artisan key:generate
```

Jika memakai PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### 3. Pilih database lokal

Default `.env.example` memakai SQLite.

```env
DB_CONNECTION=sqlite
```

Buat file SQLite:

```bash
touch database/database.sqlite
```

Jika memakai PowerShell:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
```

Jika ingin memakai MySQL lokal seperti Laragon/XAMPP:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=codecoffee
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi dan seed

```bash
php artisan migrate --seed
```

### 5. Jalankan aplikasi

```bash
npm run dev
php artisan serve
```

Buka:

```text
http://127.0.0.1:8000
```

Halaman `/` akan mengarahkan tamu ke `/menu`.

## Build Production Lokal

```bash
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Untuk development, cukup gunakan:

```bash
php artisan optimize:clear
```

## Konfigurasi Gambar Produk

Upload gambar produk mendukung dua mode:

1. `public` untuk lokal
2. `supabase` untuk production/Vercel

### Lokal sederhana

Gunakan storage lokal:

```env
PRODUCT_IMAGE_DISK=public
```

Lalu buat symlink:

```bash
php artisan storage:link
```

### Supabase Storage S3-compatible

Buat bucket di Supabase Storage, misalnya:

```text
product-images
```

Untuk gambar bisa tampil langsung di website, bucket harus public.

Environment yang diperlukan:

```env
PRODUCT_IMAGE_DISK=supabase
SUPABASE_STORAGE_ACCESS_KEY_ID=
SUPABASE_STORAGE_SECRET_ACCESS_KEY=
SUPABASE_STORAGE_BUCKET=product-images
SUPABASE_STORAGE_REGION=
SUPABASE_STORAGE_ENDPOINT=https://<project_ref>.storage.supabase.co/storage/v1/s3
SUPABASE_STORAGE_PUBLIC_URL=https://<project_ref>.supabase.co/storage/v1/object/public/product-images
SUPABASE_STORAGE_USE_PATH_STYLE_ENDPOINT=true
```

Catatan:

- Jangan commit access key dan secret key.
- Di Vercel, isi env di menu Project Settings -> Environment Variables.
- Setelah env diubah di Vercel, lakukan redeploy.
- TiDB hanya menyimpan path gambar seperti `products/2026/06/file.jpg`.
- File gambar asli disimpan di Supabase Storage.

## Konfigurasi TiDB Cloud

TiDB Cloud Starter menggunakan koneksi MySQL port `4000` dan wajib SSL.

Contoh env production:

```env
DB_CONNECTION=mysql
DB_HOST=gateway01.<region>.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=manajemen_coffee_shop
DB_USERNAME=<username>
DB_PASSWORD=<password>
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
```

Untuk Vercel, `api/index.php` sudah menyiapkan storage sementara di `/tmp`,
logging ke `stderr`, dan kandidat CA certificate agar koneksi TiDB bisa berjalan.

Jika menjalankan migrasi dari lokal ke TiDB:

```bash
php artisan migrate --force
```

Jika ingin reset dan isi ulang data contoh:

```bash
php artisan migrate:fresh --seed
```

Hati-hati: `migrate:fresh` akan menghapus data lama.

## Konfigurasi Midtrans

Midtrans bersifat opsional, tetapi diperlukan jika pembayaran online ingin aktif.

```env
MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

Webhook production:

```text
https://domain-kamu.com/midtrans/notification
```

Route webhook:

```text
POST /midtrans/notification
```

Untuk testing sandbox, gunakan simulator Midtrans dan biarkan
`MIDTRANS_IS_PRODUCTION=false`.

## Deploy ke Vercel

Repository sudah memiliki:

- `vercel.json`
- `api/index.php`
- `.vercelignore`

Langkah deploy:

1. Push kode ke GitHub.
2. Import repository ke Vercel.
3. Pastikan Vercel memakai framework preset `Other` atau tidak memakai preset.
4. Isi Environment Variables.
5. Deploy.

Build command:

```bash
npm run build
```

Output directory:

```text
public
```

Runtime PHP:

```text
vercel-php@0.7.4
```

### Environment Variables minimal di Vercel

```env
APP_NAME=CodeCoffee
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domain-kamu.vercel.app

LOG_CHANNEL=stderr
LOG_STACK=stderr

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=4000
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

PRODUCT_IMAGE_DISK=supabase
SUPABASE_STORAGE_ACCESS_KEY_ID=
SUPABASE_STORAGE_SECRET_ACCESS_KEY=
SUPABASE_STORAGE_BUCKET=product-images
SUPABASE_STORAGE_REGION=
SUPABASE_STORAGE_ENDPOINT=
SUPABASE_STORAGE_PUBLIC_URL=
SUPABASE_STORAGE_USE_PATH_STYLE_ENDPOINT=true

MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

VITE_APP_NAME=CodeCoffee
```

Setelah env lengkap, jalankan migrasi ke database production dari lokal:

```bash
php artisan migrate --force
```

Lalu redeploy Vercel.

## Deploy dengan Docker atau Render

Project juga memiliki `Dockerfile` dan `docker/render-entrypoint.sh`.

Untuk build image lokal:

```bash
docker build -t codecoffee .
docker run --env-file .env -p 10000:10000 codecoffee
```

Render dapat memakai Dockerfile ini. Pastikan environment variable production
diisi di dashboard Render.

## Alur Order

### Order publik

1. Pelanggan membuka `/menu`.
2. Pelanggan memilih produk dan mengisi nama.
3. Pelanggan memilih bayar online atau bayar di kasir.
4. Aplikasi membuat transaksi dengan status dapur `pending`.
5. Jika online, Midtrans membuat QR/VA dan status pembayaran diperbarui via webhook/polling.
6. Barista melihat pesanan di `/orders` dan mengubah status menjadi diproses atau selesai.

### POS kasir

1. Kasir membuka `/pos`.
2. Kasir menambahkan produk ke keranjang.
3. Kasir memilih metode bayar cash atau QRIS.
4. `OrderService` membuat transaksi dan mengurangi stok bahan baku dalam satu DB transaction.
5. Aplikasi mengarahkan ke halaman struk.

## Database Utama

Tabel penting:

- `users`
- `roles`, `permissions`, `model_has_roles`
- `categories`
- `products`
- `ingredients`
- `product_ingredient`
- `transactions`
- `transaction_items`
- `sessions`
- `cache`
- `jobs`

Kolom `products.image` menyimpan path file, bukan base64.

## Testing

Test feature dan unit ada di folder `tests`.

```bash
php artisan test
```

Konfigurasi default `phpunit.xml` memakai MySQL lokal:

```text
DB_DATABASE=coffee_shop_test
```

Buat database tersebut sebelum menjalankan test, atau ubah `phpunit.xml`
sesuai database testing yang tersedia.

## Troubleshooting

### Upload gambar error `Data Too Long`

Artinya kode production masih mencoba menyimpan base64 ke database.

Solusi:

1. Pastikan branch/commit terbaru sudah dipush.
2. Pastikan Vercel sudah redeploy commit terbaru.
3. Pastikan env `PRODUCT_IMAGE_DISK=supabase`.
4. Pastikan package `league/flysystem-aws-s3-v3` ikut terinstall dari `composer.lock`.

### Gambar berhasil upload tetapi tidak tampil

Biasanya bucket Supabase belum public atau `SUPABASE_STORAGE_PUBLIC_URL` salah.

Format public URL:

```text
https://<project_ref>.supabase.co/storage/v1/object/public/<bucket>
```

### TiDB error insecure transport

TiDB Cloud wajib SSL. Pastikan `MYSQL_ATTR_SSL_CA` tersedia atau gunakan
runtime Vercel yang sudah menyiapkan CA melalui `api/index.php`.

### Vercel error output directory `dist`

Pastikan `vercel.json` memakai:

```json
"outputDirectory": "public"
```

### Livewire asset 404

Pastikan route Vercel untuk `/livewire/(.*)` diarahkan ke `/api/index.php`
seperti yang ada di `vercel.json`.

## Perintah Git Umum

```bash
git status
git add .
git commit -m "Update documentation"
git push origin main
```

## Keamanan

- Jangan commit `.env`.
- Jangan commit access key Supabase, secret key Supabase, password TiDB, atau server key Midtrans.
- Rotate key jika pernah dibagikan di chat, screenshot, atau repository publik.
- Gunakan `APP_DEBUG=false` di production.
