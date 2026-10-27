# Product Requirement Document (PRD)

**Nama Project:** POS Indomaret (Point of Sale & Inventory System)  
**Teknologi Utama:** Laravel 11 (Backend), MySQL (Database), Tailwind CSS & Alpine.js (Desktop POS UI/UX)  
**Akses:** Autentikasi berbasis Role-Based Access Control (RBAC)  

---

## 1. Ringkasan Produk
Aplikasi POS Kasir Indomaret berbasis web yang dioptimalkan untuk tampilan desktop. Sistem ini dirancang untuk menangani alur transaksi kasir secara efisien dan cepat (keyboard-first), mengelola inventaris stok barang di gudang, serta memberikan laporan analitik penjualan dan stok bagi manager secara real-time.

---

## 2. Struktur Role & Hak Akses (RBAC)

Semua pengguna wajib melakukan login sebelum mengakses aplikasi.

| Role | Deskripsi Fitur Utama |
| :--- | :--- |
| **Kasir** | Scan barcode/pencarian barang, input qty, kalkulasi total & kembalian, cetak struk thermal, shift kasir, transaksi cepat via shortcut keyboard. |
| **Gudang** | Input barang masuk (restock), penyesuaian stok (*stock adjustment*), manajemen kategori & supplier, penanganan barang rusak/kadaluarsa. |
| **Manager** | Dashboard analitik penjualan, laporan laba rugi & stok, manajemen data master (user/karyawan, diskon/promo), monitoring riwayat transaksi kasir. |

---

## 3. Detail Fitur Per Role

### 3.1. General & Autentikasi
* **Login & Session Management:** Pemisahan *dashboard* otomatis berdasarkan role setelah login.
* **Shift Kasir:** Pencatatan modal awal kasir (kas masuk) saat buka kasir dan rekap uang fisik (kas keluar/penutupan shift) dengan selisih kas otomatis.

### 3.2. Role: Kasir (POS Interface)
* **Akses Ringkas/Shortcut Keyboard:** 
  * `F1`: Bantuan Shortcut
  * `F2`: Buka modal pembayaran
  * `F4`: Fokus ke kolom pencarian produk / barcode
  * `F7`: Tahan transaksi (Hold)
  * `F8`: Buka transaksi yang ditahan (Recall)
  * `F9`: Batal keranjang
  * `Enter`: Tambah / Konfirmasi
* **Scan Barcode & Pencarian Cepat:** Membaca input barcode scanner secara instan atau mengetik nama produk/SKU.
* **Manajemen Keranjang Belanja:** Edit qty, diskon per item / promo bundle, hapus item, kalkulasi otomatis subtotal, PPN 11%, dan diskon.
* **Modul Pembayaran:**
  * **Tunai:** Input nominal uang yang diterima, tombol nominal cepat (Rp 10k, 20k, 50k, 100k, Uang Pas), kalkulasi kembalian otomatis.
  * **Nontunai:** QRIS (simulasi QR interaktif), Kartu Debit / E-Wallet dengan input nomor referensi.
* **Cetak Struk:** Struk thermal format 58mm / 80mm dengan logo Indomaret, rincian barang, diskon, PPN, kasir, dan footer ucapan terima kasih.

### 3.3. Role: Gudang (Inventory Management)
* **Barang Masuk (Restock / Penerimaan):** Pencatatan penerimaan barang dari supplier beserta nomor PO/Faktur, harga beli, dan tanggal kadaluarsa (*batch/expiry date*).
* **Stock Adjustment (Penyesuaian Stok):** Koreksi stok fisik manual karena barang rusak, pecah/bocor, hilang, atau selisih stok opname dengan catatan alasan.
* **Manajemen Kategori & Satuan:** Pengelompokan produk (Makanan, Minuman, Kebutuhan Rumah Tangga, Personal Care, dll.).
* **Manajemen Supplier:** Data kontak dan riwayat pasokan supplier.
* **Peringatan Stok & Expiry:** Notifikasi barang stok menipis (low stock alert) dan barang mendekati kedaluwarsa.

### 3.4. Role: Manager (Reporting & Master Data)
* **Dashboard Analitik:**
  * Ringkasan penjualan hari ini, bulan ini, margin laba kotor.
  * Grafik tren penjualan harian/mingguan.
  * Top 10 produk terlaris (Best Seller).
  * Ringkasan transaksi per kasir.
* **Laporan Komprehensif:**
  * Laporan Penjualan (filter per rentang tanggal, kasir, metode pembayaran).
  * Laporan Shift Kasir (rekap kas awal, kas akhir, selisih).
  * Laporan Arus Stok (Kartu Stok / Mutasi Barang).
  * Fitur Export ke Excel (CSV) dan Cetak PDF.
* **Manajemen Diskon & Promo:**
  * Diskon persentase atau potongan harga nominal.
  * Promo beli X gratis Y / diskon minimum belanja.
* **Manajemen Pengguna (User Management):** Tambah, edit, nonaktifkan akun kasir, gudang, dan manager.

---

## 4. Skema Database Utama
* `users` (id, name, username, email, password, role, is_active)
* `shifts` (id, user_id, starting_cash, ending_cash, actual_cash, difference, opened_at, closed_at, status)
* `categories` (id, name, slug)
* `suppliers` (id, name, phone, address)
* `products` (id, category_id, barcode, sku, name, buy_price, sell_price, stock, min_stock, unit, image)
* `product_batches` (id, product_id, batch_number, expiry_date, stock)
* `stock_adjustments` (id, user_id, product_id, type, qty, reason, created_at)
* `purchases` & `purchase_items` (pembelian/restock dari supplier)
* `transactions` (id, invoice_number, shift_id, cashier_id, customer_name, subtotal, discount, tax, total, payment_method, payment_amount, change_amount, status, created_at)
* `transaction_items` (id, transaction_id, product_id, qty, unit_price, discount, subtotal)
* `promotions` (id, title, type, discount_value, min_spend, start_date, end_date, is_active)
