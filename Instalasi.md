# 🧺 Laundryan - Sistem Manajemen Laundry

Laundryan adalah aplikasi manajemen laundry modern berbasis web yang dibangun dengan framework **Laravel 12**, **Tailwind CSS v4** (melalui Vite), **AlpineJS**, serta mendukung fitur real-time updates via **Laravel Reverb** dan sistem antrean background (**Queue**).

---

## 📋 Prasyarat Sistem (Prerequisites)

Sebelum melakukan instalasi, pastikan laptop Anda sudah terpasang perangkat lunak berikut:
- **PHP >= 8.2** (pastikan ekstensi PHP seperti `pdo_mysql`, `mbstring`, `openssl`, `xml`, `zip`, `gd` telah aktif)
- **Composer** (untuk manajemen dependensi PHP)
- **Node.js** (versi 18.x atau lebih baru) & **npm** (untuk manajemen package Javascript/CSS)
- **MySQL Database Server** (melalui XAMPP, Laragon, MySQL installer standalone, atau Docker)
- **Git** (untuk mengkloning repository dari GitHub)

---

## 🛠️ Langkah-Langkah Instalasi

Ikuti langkah-langkah di bawah ini untuk menjalankan project **Laundryan** di laptop Anda:

### 1. Clone Repository
Pertama, clone project ini dari GitHub ke direktori lokal laptop Anda, kemudian masuk ke dalam direktori project:
```bash
git clone <url-repository-github>
cd Laundryan
```

### 2. Duplikasi File Environment (`.env`)
Salin file konfigurasi lingkungan bawaan (`.env.example`) menjadi file `.env` aktif:
```bash
cp .env.example .env
```

### 3. Konfigurasi File `.env`
Buka file `.env` menggunakan editor teks (seperti VS Code). Anda harus menyesuaikan konfigurasi koneksi database MySQL, driver penting, serta konfigurasi Laravel Reverb agar fitur aplikasi berfungsi dengan benar:

#### A. Konfigurasi Utama & Database
Sesuaikan variabel berikut sesuai dengan lingkungan lokal laptop Anda:
```env
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Laundryan     # Buat database kosong dengan nama ini di MySQL Anda
DB_USERNAME=root          # Username database Anda (default XAMPP biasanya 'root')
DB_PASSWORD=              # Password database Anda (default XAMPP dikosongkan)

SESSION_DRIVER=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb
```

> [!IMPORTANT]
> Pastikan Anda telah membuat database kosong bernama **`Laundryan`** di phpMyAdmin, HeidiSQL, atau MySQL Client Anda sebelum melangkah ke tahap berikutnya.

#### B. Konfigurasi Laravel Reverb (Websocket Real-time)
Tambahkan atau sesuaikan variabel berikut di bagian bawah file `.env` agar websocket lokal Anda dapat terhubung secara real-time:
```env
REVERB_APP_ID=330395
REVERB_APP_KEY=4lorcludce1io9sh8mc5
REVERB_APP_SECRET=1qfzqmoicy0y9kiyq4h4
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${VITE_REVERB_HOST}"
VITE_REVERB_PORT="${VITE_REVERB_PORT}"
VITE_REVERB_SCHEME="${VITE_REVERB_SCHEME}"
```

### 4. Jalankan Instalasi & Setup Otomatis
Project ini dilengkapi dengan perintah setup otomatis yang didefinisikan dalam `composer.json` untuk mempercepat instalasi. Perintah ini akan menginstal paket PHP & Node.js, generate app key, melakukan migrasi database, dan mem-build asset frontend.

Jalankan perintah berikut pada terminal Anda:
```bash
composer run setup
```

Setelah proses setup otomatis selesai, jalankan perintah berikut untuk mengisi database dengan data uji coba awal (seeder) agar akun uji coba dapat digunakan:
```bash
php artisan db:seed
```

---

### *Alternatif: Langkah Instalasi Manual (Satu per Satu)*
Jika perintah otomatis di atas mengalami kendala atau Anda ingin melakukan instalasi secara bertahap, jalankan perintah berikut secara berurutan:

1. **Install dependensi PHP (Laravel):**
   ```bash
   composer install
   ```
2. **Buat Application Key:**
   ```bash
   php artisan key:generate
   ```
3. **Jalankan Migrasi Database beserta Seeder:**
   ```bash
   php artisan migrate --seed
   ```
4. **Install dependensi frontend (Node.js):**
   ```bash
   npm install
   ```
5. **Kompilasi asset frontend:**
   ```bash
   npm run build
   ```

---

## 🔑 Akun Uji Coba (Default Credentials)

Setelah database seeder selesai dijalankan, tabel database Anda akan terisi otomatis dengan data tiruan (dummy) dan beberapa akun uji coba dengan password bawaan **`password`**:

| Peran (Role) | Alamat Email (Email Address) | Kata Sandi (Password) |
| :--- | :--- | :--- |
| **Admin** | `ithelpsdesk1@gmail.com` | `password` |
| **Karyawan** | `karyawan@laundryan.com` | `password` |
| **Kurir** | `kurir@laundryan.com` | `password` |
| **Pelanggan** | `pelanggan@laundryan.com` | `password` |

---

## 🚀 Menjalankan Aplikasi di Lokal

Aplikasi Laundryan memanfaatkan beberapa layanan background (seperti antrean antarmuka dan websocket real-time) agar seluruh fiturnya berfungsi optimal.

### Cara Cepat & Praktis (Sangat Direkomendasikan)
Jalankan perintah berikut di terminal utama Anda:
```bash
composer dev
```
*Perintah ini secara otomatis akan menjalankan Laravel server, asset compiler (Vite), background queue worker, dan logs viewer (Pail) secara bersamaan menggunakan library `concurrently`.*

---

### Cara Manual (Menggunakan Beberapa Jendela/Tab Terminal)
Apabila Anda ingin menjalankan setiap proses secara terpisah untuk memantau logs masing-masing:

1. **Jalankan Server Utama Laravel:**
   ```bash
   php artisan serve --port=8080
   ```
   Aplikasi dapat diakses melalui browser Anda di alamat: [http://localhost:8080](http://localhost:8080)

2. **Jalankan Vite Dev Server:**
   ```bash
   npm run dev
   ```

3. **Jalankan Background Queue Worker:**
   (Diperlukan untuk memproses antrean tugas di latar belakang, seperti notifikasi & tracking):
   ```bash
   php artisan queue:work
   ```

4. **Jalankan Laravel Reverb (Websocket):**
   (Diperlukan untuk fitur notifikasi real-time tanpa perlu refresh halaman):
   ```bash
   php artisan reverb:start
   ```

---

## ⚙️ Konfigurasi Integrasi Eksternal (Mail, Stripe, & Google OAuth)

Untuk mengaktifkan fitur pengiriman email (SMTP), gerbang pembayaran online (Stripe), dan login cepat menggunakan Google OAuth, salin dan masukkan konfigurasi berikut ke dalam file `.env` Anda:

```env
# Konfigurasi SMTP Mail (Gmail)
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"    # Isi dengan Gmail App Password milik Anda
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Laundryan"
ADMIN_EMAIL=your-email@gmail.com

# Konfigurasi Stripe Gateway
STRIPE_KEY=pk_test_YOUR_STRIPE_PUBLISHABLE_KEY        # Isi dengan Stripe Publishable Key Anda
STRIPE_SECRET=sk_test_YOUR_STRIPE_SECRET_KEY          # Isi dengan Stripe Secret Key Anda

# Konfigurasi Google Socialite OAuth
GOOGLE_CLIENT_ID=YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com    # Dari Google Cloud Console
GOOGLE_CLIENT_SECRET=YOUR_GOOGLE_CLIENT_SECRET                       # Dari Google Cloud Console
```

---

## 🛠️ Penyelesaian Masalah (Troubleshooting)

- **Masalah: Database connection refused / MySQL tidak terhubung**
  Pastikan database server Anda (XAMPP/Laragon/MySQL Service) sudah dalam status **Running/Start**. Verifikasi port dan credentials pada `.env` sudah sesuai dengan server MySQL lokal Anda.
- **Masalah: Asset CSS/Javascript tidak termuat atau tampilan berantakan**
  Pastikan Anda telah menjalankan `npm run dev` saat masa pengembangan, atau `npm run build` untuk memproduksi asset yang siap pakai.
- **Masalah: Fitur real-time / update status order tidak berjalan**
  Pastikan proses `php artisan queue:work` dan `php artisan reverb:start` (atau perintah gabungan `composer dev`) dalam kondisi berjalan aktif di latar belakang tanpa adanya error.
