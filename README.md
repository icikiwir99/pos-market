# POS Indowowok - Aplikasi Kasir & Inventaris Modern (Laravel)

POS Indowowok adalah aplikasi Point of Sale (POS) dan manajemen inventaris kasir minimarket berbasis web modern. Dibangun menggunakan framework Laravel 13 dan PHP 8.3, aplikasi ini dirancang untuk menangani transaksi penjualan harian kasir, manajemen pergudangan (restock dan penyesuaian stok), serta dashboard analitik dan pelaporan keuangan untuk manager toko.

---

## 🔗 Link Deployment & Repository

* **Live Demo (HTTPS):** [https://pos-indowowok.vercel.app](https://pos-indowowok.vercel.app)
* **GitHub Repository:** [https://github.com/icikiwir99/pos-indowowok](https://github.com/icikiwir99/pos-indowowok)

---

## 🔑 Akun Demo untuk Pengujian (Login)

Gunakan kredensial berikut untuk menguji hak akses masing-masing role (tersedia di database seeder bawaan):

| Role | Username | Email Alternatif | Password | Hak Akses & Fitur Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Manager** | `manager` | `manager@indomaret.local` | `password` | Dashboard omset, laporan penjualan, export CSV, manajemen user & promosi |
| **Gudang** | `gudang` | `gudang@indomaret.local` | `password` | Katalog produk, restock barang masuk supplier, penyesuaian stok opname |
| **Kasir 1** | `kasir` | `kasir@indomaret.local` | `password` | Buka/tutup shift kasir, scan barcode, checkout transaksi, cetak struk nota |
| **Kasir 2** | `kasir2` | `kasir2@indomaret.local` | `password` | Akun kasir cadangan untuk pengujian multi-kasir / pergantian shift |

> **Catatan Login:** Form login mendukung input berupa **Username** maupun **Email**.

---

## 🛠️ Teknologi yang Digunakan

* **Backend:** [Laravel 13](https://laravel.com/) (PHP 8.3+)
* **Frontend:** Blade Templating, [Tailwind CSS](https://tailwindcss.com/), [Alpine.js](https://alpinejs.dev/)
* **Visualisasi & Ikon:** [Chart.js](https://www.chartjs.org/), [Lucide Icons](https://lucide.dev/)
* **Database:** [Aiven for MySQL](https://aiven.io/mysql) (Cloud Database dengan enkripsi TLS 1.3 / SSL)
* **Hosting:** [Vercel](https://vercel.com/) Serverless Functions (`vercel-php@0.7.4` untuk PHP 8.3)
* **Keamanan:** Role-Based Access Control (RBAC), CSRF Protection, Bcrypt Password Hashing, Enforced HTTPS

---

## 📦 Fitur-Fitur Utama Aplikasi

### 1. Modul Kasir (Point of Sale)
* **Manajemen Shift Kasir:** Kasir wajib membuka shift dengan saldo kas awal laci (*starting cash*), mencatat total transaksi tunai, dan melakukan penutupan shift (*closing shift*) dengan validasi selisih kas fisik vs sistem.
* **Pencarian Cepat Produk:** Pencarian instan berdasarkan scan Barcode, kode SKU, nama produk, maupun filter kategori.
* **Kalkulasi Otomatis Transaksi:** Perhitungan subtotal keranjang belanja, diskon promosi aktif, pajak PPN 11%, dan nominal uang kembalian.
* **Multi-metode Pembayaran:** Mendukung pembayaran Tunai (Cash), QRIS (dengan nomor referensi RRN), dan Kartu Debit.
* **Cetak Struk Transaksi:** Tampilan nota thermal yang rapi dan siap cetak langsung dari browser.

### 2. Modul Inventaris & Gudang
* **Katalog Produk & Kategori:** Pengelolaan master data produk, satuan (pcs, pack, botol, box), harga beli/pokok, harga jual, dan batas minimum stok.
* **Peringatan Stok Rendah (Low Stock Alert):** Notifikasi otomatis untuk produk yang stoknya di bawah batas aman.
* **Restock Barang Masuk (Purchasing):** Pencatatan penerimaan barang dari supplier rekanan lengkap dengan nomor faktur dan tanggal kadaluarsa (*expiry batch*).
* **Penyesuaian Stok (Stock Adjustment):** Modul audit stok fisik (*stock opname*) dengan opsi pencatatan alasan: barang rusak, kemasan bocor, kadaluarsa, atau selisih hitung.

### 3. Modul Manager (Dashboard & Laporan)
* **Dashboard Finansial Real-time:** Ringkasan total pendapatan harian/bulanan, laba kotor, jumlah transaksi sukses, dan grafik tren penjualan mingguan.
* **Laporan Penjualan Komprehensif:** Riwayat transaksi dengan filter rentang tanggal, pencarian kasir, dan fitur **Export CSV** (streaming tanpa beban memori server).
* **Laporan Shift & Rekonsiliasi Kasir:** Audit riwayat buka/tutup shift seluruh kasir untuk memastikan tidak ada kecurangan kas.
* **Manajemen Karyawan:** Menambah kasir/staf gudang baru, mengubah role, atau menonaktifkan akun karyawan yang sudah tidak bertugas.
* **Manajemen Promosi:** Pengaturan diskon bertarget dengan periode tanggal berlaku dan batas minimum belanja.

---

## 🗄️ Informasi Database

Aplikasi ini menggunakan database cloud **Aiven for MySQL** dengan spesifikasi:
* **Engine:** MySQL 8.x
* **Keamanan Jaringan:** Enkripsi koneksi SSL/TLS wajib (`DB_SSLMODE=REQUIRED`).
* **Verifikasi Sertifikat:** Dilengkapi sertifikat resmi Aiven Project CA (`database/certs/aiven-ca.pem`) yang otomatis dihubungkan oleh PDO MySQL Laravel.
* **Stateless Persistence:** Data transaksi, sesi login pengguna (`sessions`), dan cache aplikasi tersimpan secara persisten di database cloud.

*(Kredensial rahasia seperti password tidak dicantumkan di publik demi keamanan).*

---

## 💻 Panduan Instalasi Lokal (Development)

### Prasyarat
* PHP >= 8.3 dengan ekstensi `pdo_mysql`, `openssl`, `mbstring`, `curl`, `tokenizer`
* Composer 2.x
* Node.js (v18+) dan NPM
* MySQL Server (atau Laragon / XAMPP)

### Langkah Instalasi
1. **Clone repository:**
   ```bash
   git clone https://github.com/icikiwir99/pos-indowowok.git
   cd pos-market
   ```

2. **Install dependensi PHP & JavaScript:**
   ```bash
   composer install
   npm install
   ```

3. **Salin file environment:**
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Konfigurasi Database di file `.env`:**
   Sesuaikan parameter database lokal Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pos_indomaret
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Jalankan Migration & Database Seeder:**
   ```bash
   php artisan migrate --seed
   ```

7. **Kompilasi Aset Frontend:**
   ```bash
   npm run build
   ```

8. **Jalankan Aplikasi:**
   ```bash
   php artisan serve
   ```
   Buka peramban Anda di [http://localhost:8000](http://localhost:8000).

---

## ☁️ Deployment ke Vercel

Aplikasi ini telah dikonfigurasi sepenuhnya untuk lingkungan serverless Vercel:
* Entry point serverless: `api/index.php`
* File routing & konfigurasi runtime: `vercel.json` (`vercel-php@0.7.4`)
* Penyimpanan view & cache sementara di `/tmp`
* Session dialirkan secara aman melalui database MySQL Aiven atau encrypted cookie

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah lisensi terbuka [MIT](LICENSE).
