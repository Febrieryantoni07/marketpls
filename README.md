# SmashZone - Marketplace Raket Badminton Original 🏸

Aplikasi Web Marketplace Tradisional Spesialis Raket Badminton menggunakan **PHP Native**, **MySQLi**, dan **Bootstrap 5**.

---

## 📋 DAFTAR ISI
1. [Fitur Utama Aplikasi](#fitur-utama-aplikasi)
2. [Entitas dan Struktur Database (5+ Entitas)](#entitas-dan-struktur-database)
3. [Panduan Menjalankan di Komputer Lokal (XAMPP / MySQL)](#panduan-menjalankan-di-komputer-lokal-xampp--mysql)
4. [Panduan Deployment ke Hosting Gratis InfinityFree](#panduan-deployment-ke-hosting-gratis-infinityfree)
5. [Struktur Direktori Proyek](#struktur-direktori-proyek)

---

## 🌟 FITUR UTAMA APLIKASI

1. **Katalog Produk Raket Badminton (Daftar Produk)**
   - Filter berdasarkan Kategori Karakter Raket (*Power & Smash*, *Speed & Drive*, *All-Around Control*, *Beginner Friendly*).
   - Filter Brand Populer (*Yonex*, *Victor*, *Li-Ning*, *Mizuno*, *Felet*).
   - Fitur Pencarian Kata Kunci (*Search*) dan Pengurutan Harga (*Sorting*).
   - Indikator spesifikasi cepat: Bobot & Ukuran Grip (3U/4U/G5), Tarikan Max LBS, Titik Keseimbangan (Head Heavy, Head Light, Even Balance).

2. **Halaman Detail Produk Raket**
   - Galeri visual raket beresolusi tinggi dengan SVG vektor dinamis.
   - Tabel spesifikasi teknis bulutangkis lengkap.
   - Pilihan jumlah beli (Quantity counter).
   - Tab deskripsi teknologi frame carbon dan ulasan testimoni pembeli (*Reviews*).
   - Rekomendasi raket terkait yang sejenis.

3. **Keranjang Belanja (*Shopping Cart*)**
   - Manajemen keranjang berbasis PHP Session yang aman dan responsif.
   - Fitur ubah kuantitas dengan pengecekan stok maksimal otomatis.
   - Fitur hapus item tunggal atau kosongkan seluruh keranjang.
   - Kalkulasi subtotal dan ringkasan pembayaran otomatis.

4. **Checkout dan Pembuatan Transaksi Pemesanan**
   - Form data pembeli (Nama, No. WhatsApp, Email, Alamat Lengkap).
   - Pilihan kurir logistik (JNE Regular dengan Pipa PVC Guard, J&T, SiCepat).
   - Pilihan metode bayar (BCA, Mandiri, BRI, QRIS).
   - **Database Transaction (`mysqli_begin_transaction`)**: Menyimpan transaksi ke tabel `orders` dan `order_items`, serta memotong kuantitas stok di tabel `products` secara otomatis.
   - Generate Nomor Invoice Unik (Format: `INV-YYYYMMDD-XXXXX`).

5. **Faktur / Invoice Resmi**
   - Bukti transaksi resmi dengan tombol **Cetak / Simpan PDF** ramah printer (`@media print`).
   - Rincian nomor rekening pembayaran dan konfirmasi WhatsApp.

6. **Kelola Produk (CRUD Penjual / Admin)**
   - **Create**: Tambah raket badminton baru dengan upload foto atau preset grafis.
   - **Read**: Tabel data raket lengkap dengan badge stok dan omset penjualan.
   - **Update**: Edit harga, ketersediaan stok, dan parameter teknis raket.
   - **Delete**: Hapus raket dengan konfirmasi keamanan.
   - **Kelola Pesanan**: Halaman pemantauan pesanan dan update status pengiriman (*Pending*, *Paid*, *Shipped*, *Completed*, *Cancelled*).

7. **Autentikasi Pengguna & Pendaftaran Akun (Admin & User)**
   - **Pendaftaran Akun Baru (`register.php`)**: Calon pengguna dapat mendaftar terlebih dahulu sebelum masuk, dengan opsi memilih peran **Pembeli (User)** atau **Penjual (Admin Store)**.
   - **Login Akun (`login.php`)**: Masuk dengan email dan password terenkripsi bcrypt (`password_hash`), dilengkapi proteksi session dan tombol cepat uji akun demo.
   - **Hak Akses & Proteksi Halaman**: Panel admin (`/admin/`) secara otomatis diproteksi; hanya pengguna dengan peran *seller/admin* yang dapat mengakses CRUD dan kelola pesanan.
   - **Riwayat Pesanan Pembeli (`my_orders.php`)**: Pembeli yang login dapat memantau seluruh transaksi dan membuka kembali faktur/invoice kapan saja.
   - **Logout (`logout.php`)**: Fitur keluar sistem secara aman dengan penghapusan data session.

---

## 🔑 KREDENSIAL AKUN DEMO

Untuk kemudahan pengujian, tersedia 2 akun siap pakai:

| Tipe Akun | Email Login | Password | Akses & Hak Peran |
| :--- | :--- | :--- | :--- |
| **Admin / Penjual** | `admin@smashzone.com` | `password123` | Akses penuh dashboard CRUD produk & kelola pesanan masuk |
| **Pembeli / User** | `budi@gmail.com` | `password123` | Akses belanja, checkout otomatis, & riwayat pesanan saya |

*Atau Anda dapat mendaftarkan akun baru secara bebas melalui menu **"Daftar"**.*

---

## 🗄️ ENTITAS DAN STRUKTUR DATABASE

Aplikasi ini menggunakan minimal 6 entitas relasional:
1. `users`: Menyimpan data penjual (*seller*) dan pembeli (*customer*).
2. `categories`: Menyimpan kategori karakter raket badminton.
3. `products`: Menyimpan data produk raket badminton beserta spesifikasi teknisnya.
4. `orders`: Menyimpan header transaksi pemesanan pembeli dan alamat pengiriman.
5. `order_items`: Menyimpan rincian item raket, kuantitas, dan subtotal yang dibeli.
6. `reviews`: Menyimpan rating dan ulasan pelanggan terhadap raket.

---

## 🚀 PANDUAN MENJALANKAN DI KOMPUTER LOKAL (XAMPP / MySQL)

1. **Jalankan Apache & MySQL di XAMPP**:
   - Buka aplikasi **XAMPP Control Panel**.
   - Klik tombol **Start** pada modul **Apache** dan **MySQL** hingga indikator berwarna hijau.

2. **Inisialisasi Database (Hanya 1 Klik)**:
   - Buka browser Anda (Google Chrome, Firefox, atau Edge).
   - Kunjungi URL berikut:
     ```
     http://localhost/web_marketpls/setup.php
     ```
   - Klik tombol **"Mulai Inisialisasi Database Sekarang"**.
   - Script akan otomatis membuat database `badminton_marketplace` beserta seluruh tabel relasional dan data katalog raket badminton.

3. **Akses Aplikasi**:
   - **Katalog Toko (Pembeli)**: `http://localhost/web_marketpls/`
   - **Panel Kelola Produk (Penjual/Admin)**: `http://localhost/web_marketpls/admin/`
   - **Daftar Transaksi Pesanan**: `http://localhost/web_marketpls/admin/orders.php`

---

## 🌐 PANDUAN DEPLOYMENT KE HOSTING GRATIS INFINITYFREE

Untuk mengonlinekan web marketplace ini ke internet via [InfinityFree](https://infinityfree.com):

### Langkah 1: Pendaftaran Akun InfinityFree
1. Buka situs [https://infinityfree.com](https://infinityfree.com) dan klik **Register Now**.
2. Masukkan email dan password Anda, lalu verifikasi email pendaftaran.

### Langkah 2: Buat Akun Hosting Baru
1. Masuk ke Client Area InfinityFree, klik **Create Account**.
2. Pilih subdomain gratis (misalnya: `raket-smashzone.infinityfreeapp.com`).
3. Tunggu hingga akun aktif, kemudian klik **Manage** untuk membuka detail akun.

### Langkah 3: Buat Database MySQL di vPanel
1. Masuk ke **Control Panel (vPanel)** akun hosting Anda.
2. Cari menu **MySQL Databases**.
3. Buat database baru (misalnya dengan nama: `badminton_marketplace`).
4. Catat informasi kredensial yang muncul:
   - **MySQL Hostname**: (Contoh: `sql123.infinityfree.com`)
   - **MySQL Database Name**: (Contoh: `if0_12345678_badminton_marketplace`)
   - **MySQL Username**: (Contoh: `if0_12345678`)
   - **MySQL Password**: (Password akun vPanel Anda)

### Langkah 4: Import Database via phpMyAdmin InfinityFree
1. Di dalam vPanel, klik menu **phpMyAdmin**.
2. Pilih database yang baru Anda buat di panel kiri.
3. Klik tab **Import** di bagian atas.
4. Klik **Choose File** dan pilih file `database.sql` dari folder proyek `web_marketpls`.
5. Klik tombol **Go / Import** di bagian bawah dan pastikan semua query sukses.

### Langkah 5: Sesuaikan Konfigurasi Database di `config/database.php`
Buka file `config/database.php` dan sesuaikan bagian `else` dengan kredensial InfinityFree Anda:
```php
define('DB_HOST', 'sql123.infinityfree.com'); // Ganti dengan Hostname Anda
define('DB_USER', 'if0_12345678');           // Ganti dengan Username Anda
define('DB_PASS', 'PasswordHostingAnda');    // Ganti dengan Password Anda
define('DB_NAME', 'if0_12345678_badminton_marketplace'); // Ganti dengan Nama Database Anda
define('BASE_URL', '');
```

### Langkah 6: Upload Source Code ke Folder `htdocs`
1. Buka menu **Online File Manager** di vPanel atau gunakan **FileZilla FTP**.
2. Masuk ke folder `/htdocs`.
3. Hapus file `index2.html` bawaan hosting jika ada.
4. Upload seluruh file dan folder proyek `web_marketpls` ke dalam direktori `/htdocs`.
5. Buka domain gratis Anda di browser (misal `http://raket-smashzone.infinityfreeapp.com`) dan aplikasi Web Marketplace Raket Badminton Anda telah online dan siap digunakan!

---

## 📁 STRUKTUR DIREKTORI PROYEK

```text
web_marketpls/
├── api/
│   ├── helpers.php              # Bootstrap REST API, CORS, JSON Response & Error Handler
│   ├── index.php                # Endpoint dokumentasi spesifikasi REST API
│   ├── kategori.php             # GET /api/kategori.php (Daftar & Detail Kategori)
│   ├── produk.php               # GET /api/produk.php & ?id=X (Daftar & Detail Produk)
│   └── pesanan.php              # POST /api/pesanan.php (Pembuatan Pesanan & Transaksi MySQL)
├── assets/
│   ├── css/
│   │   └── style.css            # Desain kustom bertema bulutangkis modern
│   ├── js/
│   │   └── main.js              # Interaktivitas keranjang dan modal
│   └── images/
│       └── products/            # Gambar vektor SVG & upload raket badminton
├── config/
│   └── database.php             # Koneksi database MySQLi (Lokal & Hosting auto-switch)
├── includes/
│   ├── functions.php            # Helper Format Rupiah, Flash Message, Hitung Cart
│   ├── header.php               # Navigasi atas, brand, dan indikator keranjang
│   └── footer.php               # Footer responsif dan teknologi pendukung
├── admin/
│   ├── index.php                # Read & Dashboard kelola produk raket
│   ├── product_add.php          # Create data raket baru & upload gambar
│   ├── product_edit.php         # Update data dan spesifikasi raket
│   ├── product_delete.php       # Delete raket dengan konfirmasi
│   └── orders.php               # Pemantauan pesanan & update status pengiriman
├── .gitignore                   # Proteksi kredensial, logs, cache, & file lokal
├── database.sql                 # Skema DDL & DML 6 entitas relasional lengkap
├── setup.php                    # 1-Click database installer lokal/hosting
├── login.php                    # Halaman masuk (Admin & Pembeli) dengan demo switcher
├── register.php                 # Halaman pendaftaran akun baru (User & Penjual)
├── logout.php                   # Pembersihan session keluar akun
├── my_orders.php                # Riwayat transaksi belanja untuk pembeli
├── index.php                    # Katalog utama raket dengan search & filter
├── detail.php                   # Detail spesifikasi badminton & tombol order
├── cart.php                     # Keranjang belanja berbasis session
├── checkout.php                 # Transaksi pemesanan & pengurangan stok
├── invoice.php                  # Faktur pembayaran resmi & cetak PDF
├── SmashZone_Marketplace_API.postman_collection.json # Koleksi Postman resmi pengujian API
└── README.md                    # Dokumentasi lengkap sistem & REST API
```

---

## ⚡ DOKUMENTASI LENGKAP REST API MARKETPLACE (SOAL 12 - PENGEMBANGAN WEB SERVICE / REST API)

Website SmashZone telah dikembangkan dengan modul **Web Service / RESTful API** untuk memenuhi penugasan **Soal 12**. Modul ini dibangun secara native menggunakan **PHP Native** dan **MySQLi**, mengembalikan format **JSON**, menerapkan kode status HTTP standar, dan terintegrasi langsung dengan database `badminton_marketplace` yang sudah ada tanpa mengubah fitur lama antarmuka web.

### 🌐 Base URL API
```text
http://localhost/web_marketpls/api
```

---

### 📌 Ringkasan Endpoint

| No | Metode | Endpoint | Deskripsi | Kode Status |
|---|---|---|---|---|
| 1 | `GET` | `/api/index.php` | Menampilkan dokumentasi dan katalog endpoint API | `200 OK`, `405` |
| 2 | `GET` | `/api/produk.php` | Menampilkan daftar seluruh produk raket (mendukung filter & search) | `200 OK`, `400`, `405`, `500` |
| 3 | `GET` | `/api/produk.php?id={id}` | Menampilkan rincian detail satu produk raket dan ulasan | `200 OK`, `400`, `404`, `405`, `500` |
| 4 | `GET` | `/api/kategori.php` | Menampilkan daftar seluruh kategori raket & jumlah produk | `200 OK`, `405`, `500` |
| 5 | `GET` | `/api/kategori.php?id={id}` | Menampilkan detail kategori tertentu beserta daftar produknya | `200 OK`, `400`, `404`, `405`, `500` |
| 6 | `POST` | `/api/pesanan.php` | Membuat transaksi pesanan baru (Orders & Order Items) & potong stok | `201 Created`, `400`, `404`, `405`, `500` |

---

### 📖 Detail & Spesifikasi Endpoint

#### 1. GET `/api/kategori.php`
- **Tujuan**: Mengambil daftar kategori raket badminton.
- **Header**: `Accept: application/json`
- **Contoh Permintaan**:
  ```http
  GET /web_marketpls/api/kategori.php HTTP/1.1
  Host: localhost
  ```
- **Contoh Respon (200 OK)**:
  ```json
  {
      "status": "success",
      "code": 200,
      "message": "Daftar kategori berhasil diambil.",
      "data": {
          "total": 4,
          "categories": [
              {
                  "id": 1,
                  "name": "Power & Smash (Head Heavy)",
                  "slug": "power-smash",
                  "description": "Raket dengan bobot condong ke kepala untuk menghasilkan smash keras dan bertenaga.",
                  "total_products": 4
              },
              {
                  "id": 2,
                  "name": "Speed & Drive (Head Light)",
                  "slug": "speed-drive",
                  "description": "Raket lincah dengan frame aerodinamis untuk rally cepat dan permainan ganda lincah.",
                  "total_products": 2
              }
          ]
      }
  }
  ```

---

#### 2. GET `/api/produk.php`
- **Tujuan**: Menampilkan seluruh katalog produk raket badminton.
- **Parameter Query (Opsional)**:
  - `category_id` (int): Filter ID kategori (contoh: `?category_id=1`).
  - `brand` (string): Filter brand (contoh: `?brand=Yonex`).
  - `search` (string): Kata kunci pencarian nama atau brand (contoh: `?search=Ryuga`).
  - `featured` (int/bool): Filter produk unggulan (`?featured=1`).
  - `sort` (string): Pengurutan (`price_asc`, `price_desc`, `latest`, `name_asc`, `stock_desc`).
- **Contoh Permintaan**:
  ```http
  GET /web_marketpls/api/produk.php?brand=Yonex HTTP/1.1
  Host: localhost
  ```
- **Contoh Respon (200 OK)**:
  ```json
  {
      "status": "success",
      "code": 200,
      "message": "Daftar produk berhasil diambil.",
      "data": {
          "total": 4,
          "filters": {
              "category_id": null,
              "brand": "Yonex",
              "search": null,
              "sort": "default (id asc)"
          },
          "products": [
              {
                  "id": 1,
                  "seller_id": 1,
                  "seller_name": "Official SmashZone Store",
                  "category_id": 1,
                  "category_name": "Power & Smash (Head Heavy)",
                  "category_slug": "power-smash",
                  "name": "Yonex Astrox 88D Pro (3rd Gen)",
                  "brand": "Yonex",
                  "price": 2850000,
                  "stock": 14,
                  "weight_grip": "4U/G5",
                  "tension_lbs": "20 - 28 LBS",
                  "balance_point": "Head Heavy (305mm)",
                  "flexibility": "Stiff",
                  "image": "racket_astrox_88d.svg",
                  "is_featured": 1,
                  "created_at": "2026-11-06 22:58:00",
                  "image_url": "http://localhost/web_marketpls/assets/images/racket_astrox_88d.svg"
              }
          ]
      }
  }
  ```

---

#### 3. GET `/api/produk.php?id={id}`
- **Tujuan**: Mengambil rincian detail satu produk raket dan ulasan testimoni pembeli.
- **Parameter Wajib**: `id` (integer positif >= 1).
- **Contoh Permintaan**:
  ```http
  GET /web_marketpls/api/produk.php?id=1 HTTP/1.1
  Host: localhost
  ```
- **Contoh Respon Sukses (200 OK)**:
  ```json
  {
      "status": "success",
      "code": 200,
      "message": "Berhasil mendapatkan detail produk ID 1.",
      "data": {
          "id": 1,
          "seller_id": 1,
          "seller_name": "Official SmashZone Store",
          "category_id": 1,
          "category_name": "Power & Smash (Head Heavy)",
          "name": "Yonex Astrox 88D Pro (3rd Gen)",
          "brand": "Yonex",
          "price": 2850000,
          "stock": 14,
          "weight_grip": "4U/G5",
          "tension_lbs": "20 - 28 LBS",
          "balance_point": "Head Heavy (305mm)",
          "flexibility": "Stiff",
          "description": "Raket andalan pemain ganda belakang level dunia...",
          "rating": {
              "average": 5,
              "count": 1,
              "reviews": [
                  {
                      "id": 1,
                      "user_name": "Taufik H.",
                      "rating": 5,
                      "comment": "Smash terasa sangat padat dan bertenaga! Raket 100% original.",
                      "created_at": "2026-11-06 22:58:00"
                  }
              ]
          }
      }
  }
  ```

---

#### 4. POST `/api/pesanan.php`
- **Tujuan**: Membuat transaksi pesanan baru.
- **Keamanan & Konsistensi**:
  - Menggunakan MySQL Transaction (`mysqli_begin_transaction`).
  - Mengunci baris produk (`FOR UPDATE`) untuk verifikasi stok terkini.
  - Menghitung ulang total harga berdasarkan harga asli di database (mencegah manipulasi harga dari client).
  - Mengurangi stok produk secara otomatis di tabel `products`.
  - Menyimpan transaksi ke tabel `orders` dan `order_items`.
- **Header**:
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Contoh Request Body (JSON)**:
  ```json
  {
      "customer_name": "Muhammad Rizky Pratama",
      "customer_email": "rizky.pratama@gmail.com",
      "customer_phone": "081298765432",
      "shipping_address": "Jl. Gatot Subroto Kav. 52, Tebet, Jakarta Selatan 12870",
      "courier": "JNE Regular",
      "payment_method": "Transfer Bank BCA",
      "notes": "Mohon sertakan pelindung pipa PVC untuk raket.",
      "items": [
          {
              "product_id": 1,
              "quantity": 1
          }
      ]
  }
  ```
- **Contoh Respon Berhasil (201 Created)**:
  ```json
  {
      "status": "success",
      "code": 201,
      "message": "Pesanan berhasil dibuat dengan nomor invoice INV-20261116-84992.",
      "data": {
          "order_id": 7,
          "invoice_code": "INV-20261116-84992",
          "status": "pending",
          "customer": {
              "name": "Muhammad Rizky Pratama",
              "email": "rizky.pratama@gmail.com",
              "phone": "081298765432",
              "shipping_address": "Jl. Gatot Subroto Kav. 52, Tebet, Jakarta Selatan 12870"
          },
          "shipping": {
              "courier": "JNE Regular",
              "payment_method": "Transfer Bank BCA",
              "notes": "Mohon sertakan pelindung pipa PVC untuk raket."
          },
          "summary": {
              "total_items": 1,
              "total_amount": 2850000,
              "total_amount_formatted": "Rp 2.850.000"
          },
          "items": [
              {
                  "product_id": 1,
                  "product_name": "Yonex Astrox 88D Pro (3rd Gen)",
                  "price": 2850000,
                  "quantity": 1,
                  "subtotal": 2850000,
                  "id": 8
              }
          ],
          "created_at": "2026-11-16 13:15:21"
      }
  }
  ```

---

### 🛡️ Penanganan Error & Kode Status HTTP

API dirancang dengan penanganan error ketat dan konsisten:

| Kode Status | Kategori | Kondisi Pemicu | Format Respon JSON |
|---|---|---|---|
| `200 OK` | Sukses | Request GET berhasil mengambil data | `{"status":"success","code":200,...}` |
| `201 Created` | Sukses | Request POST pesanan berhasil disimpan ke database | `{"status":"success","code":201,...}` |
| `400 Bad Request` | Klien | Format JSON salah, validasi input gagal, atau stok tidak cukup | `{"status":"error","code":400,"message":"...","errors":{...}}` |
| `404 Not Found` | Klien | ID produk atau ID kategori tidak ditemukan di database | `{"status":"error","code":404,"message":"... tidak ditemukan."}` |
| `405 Method Not Allowed` | Klien | Metode HTTP salah (misal POST ke endpoint produk atau GET ke pesanan) | `{"status":"error","code":405,"message":"Metode HTTP ... tidak diizinkan"}` |
| `500 Internal Server Error` | Server | Gagal koneksi ke database MySQL atau query gagal eksekusi | `{"status":"error","code":500,"message":"Terjadi kesalahan pada database"}` |

---

### 🧪 Panduan Pengujian

#### 1. Menggunakan Browser
Buka tautan berikut langsung di browser (Google Chrome / Microsoft Edge / Firefox):
- `http://localhost/web_marketpls/api/index.php` (Dokumentasi API)
- `http://localhost/web_marketpls/api/kategori.php` (Daftar Kategori)
- `http://localhost/web_marketpls/api/produk.php` (Daftar Produk)
- `http://localhost/web_marketpls/api/produk.php?id=1` (Detail Produk ID 1)

#### 2. Menggunakan Postman
1. Buka aplikasi **Postman**.
2. Klik tombol **Import** di pojok kiri atas.
3. Pilih file `SmashZone_Marketplace_API.postman_collection.json` yang terletak di root direktori proyek `web_marketpls`.
4. Seluruh 18 pengujian siap dijalankan secara instan (GET, POST, skenario sukses, skenario error 400, 404, 405).

#### 3. Menggunakan cURL (Terminal / Command Prompt)
```bash
# 1. Menampilkan seluruh produk
curl -X GET "http://localhost/web_marketpls/api/produk.php" -H "Accept: application/json"

# 2. Menampilkan detail produk ID 1
curl -X GET "http://localhost/web_marketpls/api/produk.php?id=1" -H "Accept: application/json"

# 3. Menampilkan daftar kategori
curl -X GET "http://localhost/web_marketpls/api/kategori.php" -H "Accept: application/json"

# 4. Membuat pesanan baru
curl -X POST "http://localhost/web_marketpls/api/pesanan.php" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"customer_name":"Rizky","customer_email":"rizky@gmail.com","customer_phone":"08123456789","shipping_address":"Bandung","items":[{"product_id":1,"quantity":1}]}'
```

---

### 📦 Panduan Unggah ke GitHub

1. Pastikan file `.gitignore` telah menyaring file konfigurasi lokal, file rahasia, cache, dan temporary files.
2. Jangan pernah mengunggah password database produksi ke repository publik. Gunakan `config/database.php` default lokal atau file template `.env.example`.
3. Inisialisasi Git dan lakukan commit:
   ```bash
   cd c:\xampp\htdocs\web_marketpls
   git init
   git add .
   git commit -m "feat: complete Marketplace Soal 11 with REST API endpoints (kategori, produk, pesanan)"
   git branch -M main
   git remote add origin https://github.com/USERNAME/REPO_NAME.git
   git push -u origin main
   ```

