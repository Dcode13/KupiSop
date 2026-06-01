# CodeCoffee Management System (TALL Stack)

Aplikasi manajemen CodeCoffee internal: POS (kasir), manajemen menu & stok,
manajemen pesanan (barista), manajemen pengguna berbasis peran, dan laporan penjualan.

**Stack:** Laravel 12 · Livewire 3 · Alpine.js · Tailwind CSS 3 · MySQL (XAMPP/MariaDB)
· Breeze (Livewire/Volt) · spatie/laravel-permission · barryvdh/laravel-dompdf

---

## 🔑 Akun Default (setelah seeding)

| Peran    | Email                | Password   |
|----------|----------------------|------------|
| Admin    | `admin@coffee.test`  | `password` |
| Kasir    | `kasir@coffee.test`  | `password` |
| Barista  | `barista@coffee.test`| `password` |

Registrasi publik dinonaktifkan — akun baru dibuat oleh admin via menu **Pengguna**.

---

## 🚀 Cara Menjalankan

Pastikan **MySQL** aktif di XAMPP dan database `coffee_shop` sudah ada.

```bash
# 1. Dependency (jika fresh clone)
composer install
npm install

# 2. Konfigurasi (file .env sudah diset ke DB coffee_shop)
php artisan key:generate        # jika APP_KEY kosong
php artisan storage:link        # symlink storage publik (untuk foto produk)

# 3. Migrasi + seed data contoh
php artisan migrate --seed

# 4. Build asset & jalankan
npm run build                   # atau: npm run dev (mode watch)
php artisan serve               # http://127.0.0.1:8000
```

Buka `http://127.0.0.1:8000` → login.

---

## 🌐 Halaman Publik (Pelanggan)

`GET /menu` — halaman menu & pemesanan **tanpa login**. Pelanggan menelusuri menu,
menambah ke keranjang (drawer), mengisi nama + catatan (mis. nomor meja), lalu **Pesan Sekarang**.
Pesanan masuk sebagai transaksi `pending` (tanpa kasir, `paid = 0`) — muncul di antrian
**Pesanan** (barista) dan **Transaksi** (kasir untuk penyelesaian pembayaran di kasir).
Beranda `/` mengarahkan tamu ke `/menu`, staff yang login ke `/dashboard`.

POS dan pemesanan publik berbagi `App\Services\OrderService::place()` (pengurangan stok +
penyimpanan transaksi dalam satu `DB::transaction`).

### 💳 Pembayaran Online (Midtrans Core API — popup kustom)

Di halaman menu, pelanggan memilih **Bayar Online** lalu muncul **popup pembayaran kustom**
(bukan Snap) dengan pilihan metode: **QRIS, GoPay, DANA, BCA, BNI, BRI**.

- **QRIS / GoPay / DANA** → tampil **QR code** untuk dipindai (DANA dibayar via QRIS karena
  Midtrans tak punya channel DANA khusus; GoPay juga punya tombol deeplink "Buka Aplikasi").
- **BCA / BNI / BRI** → tampil **Virtual Account** (nomor bisa disalin) + instruksi transfer.
- Status pembayaran disimpan di `payment_status` (`unpaid → pending → paid/failed/expired`),
  terpisah dari status dapur (`status`). Detail charge (qr_url/va_number/expiry) di `payment_details`,
  order_id charge di `payment_ref`.

Alur teknis:
- `Menu::placeOrder` (online) membuat transaksi `unpaid` → popup tampil pilihan metode.
- `Menu::selectPayment($method)` → `MidtransService::charge()` (`\Midtrans\CoreApi::charge`) →
  simpan `payment_ref` + `payment_details` → popup menampilkan QR / VA.
- Konfirmasi status:
  - **Webhook** `POST /midtrans/notification` (CSRF-exempt) untuk produksi — set di
    *Merchant Portal → Settings → Configuration → Payment Notification URL* (lokal: `ngrok http 8000`).
  - **Fallback lokal:** popup polling tiap 5 detik + tombol "Cek Status" memanggil
    `MidtransService::syncStatus()` (Status API Midtrans), jadi status update tanpa webhook publik.
- Kredensial di `.env` (`MIDTRANS_*`) via `config/services.php`. **Server key rahasia — jangan commit.**
  Default **sandbox** (`MIDTRANS_IS_PRODUCTION=false`).

Uji di sandbox: untuk VA/QRIS gunakan **Simulator Midtrans**
(`https://simulator.sandbox.midtrans.com/`) — pilih bank/e-wallet, masukkan nomor VA / scan QR,
lalu klik bayar; status otomatis jadi **Lunas**.

## 🧩 Modul & Hak Akses

| Modul                         | Admin | Kasir | Barista |
|-------------------------------|:-----:|:-----:|:-------:|
| Dashboard                     | ✓ | ✓ | ✓ |
| Kasir (POS) + cetak struk     | ✓ | ✓ |   |
| Riwayat Transaksi             | ✓ | ✓ (miliknya) | |
| Pesanan (ubah status)         | ✓ |   | ✓ |
| Kategori / Produk / Bahan Baku| ✓ |   |   |
| Pengguna                      | ✓ |   |   |
| Laporan + export CSV/PDF      | ✓ |   |   |

---

## 🛒 Alur POS

1. Pilih produk → masuk keranjang → atur qty (total dihitung otomatis).
2. Isi nama pelanggan (opsional), pilih metode bayar (Tunai/QRIS), masukkan jumlah bayar.
3. **Bayar & Simpan** → dalam satu `DB::transaction`:
   - Validasi & **kurangi stok bahan baku** sesuai resep (`product_ingredient`).
   - Buat `transactions` (nomor invoice unik `INV-YYYYMMDD-XXXX`) + `transaction_items`.
4. Otomatis diarahkan ke **struk** (cetak HTML / unduh PDF).

Status pesanan: `pending → diproses → selesai` (diubah barista di menu **Pesanan**).

---

## 🗄️ Skema Database

`users`, `roles`/`permissions` (spatie), `categories`, `products`,
`ingredients`, `product_ingredient` (pivot resep + `quantity`),
`transactions`, `transaction_items`.

---

## ✅ Testing

Test memakai database terpisah `coffee_shop_test` (lihat `phpunit.xml`).

```bash
php artisan test --filter=AppFlowTest
```

Mencakup: proteksi route per-peran, alur checkout POS + pengurangan stok,
penolakan bayar kurang, dan update status pesanan oleh barista.

---

## 📝 Catatan Asumsi

- **Breeze preset Livewire/Volt** dipakai untuk scaffolding auth (ringan).
- Pivot resep dinamai `product_ingredient` (sesuai brief) — relasi `belongsToMany`
  diberi nama tabel eksplisit.
- QRIS diperlakukan sebagai metode/status saja (dianggap dibayar pas).
- Stok dikurangi berdasarkan resep produk; produk tanpa resep tidak mengurangi stok.
