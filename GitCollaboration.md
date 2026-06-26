# 🔀 Panduan Git Collaboration

> Panduan lengkap untuk berkolaborasi di GitHub antara **Owner** dan **Collaborator**.

## 👑 BAGIAN OWNER

### 1️⃣ Setup Awal — Push Project ke GitHub

> Lakukan ini sekali saja saat pertama kali upload project ke GitHub.

```bash
# Masuk ke folder project kamu
cd /root/perkuliahan/ABL/Laundryan

# Inisialisasi git (jika belum)
git init

# Tambahkan semua file ke staging
git add .

# Commit pertama
git commit -m "feat: initial project setup"

# Hubungkan ke repository GitHub (ganti URL sesuai repo kamu)
git remote add origin https://github.com/username-kamu/nama-repo.git

# Push ke branch main
git branch -M main
git push -u origin main
```

> [!TIP]
> Setelah `git push -u origin main`, untuk push selanjutnya cukup pakai `git push` saja.

---

### 2️⃣ Push Updatean Terbaru (Rutin)

```bash
# 1. Cek file apa saja yang berubah
git status

# 2. Tambahkan file yang mau di-commit
git add .               # Semua file
# ATAU pilih file tertentu:
git add app/Http/Controllers/Admin/DashboardController.php

# 3. Commit dengan pesan yang jelas
git commit -m "fix: perbaikan navigasi grid dashboard"

# 4. Push ke GitHub
git push
```

> [!IMPORTANT]
> Tulis pesan commit yang **jelas dan deskriptif**. Gunakan format:
> - `feat:` → fitur baru
> - `fix:` → perbaikan bug
> - `refactor:` → perubahan struktur kode
> - `style:` → perubahan UI/tampilan

---

### 3️⃣ Invite Collaborator

1. Buka repo di **GitHub.com**
2. Klik **Settings** → **Collaborators**
3. Klik **Add people**
4. Masukkan **username GitHub** teman kamu
5. Teman kamu akan menerima **email undangan** — minta dia untuk Accept

---

### 4️⃣ Review & Ambil Update dari Collaborator (Pull Request)

#### A. Review Pull Request di GitHub

1. Buka repo → klik tab **Pull requests**
2. Klik PR yang dibuat collaborator
3. Klik tab **Files changed** — lihat semua perubahan yang dia buat
4. Beri komentar jika ada yang perlu diperbaiki, atau klik **Approve**
5. Jika sudah oke → klik **Merge pull request** → **Confirm merge**

#### B. Ambil Update ke Local Setelah Merge

```bash
# Pastikan kamu di branch main
git checkout main

# Ambil semua update terbaru dari GitHub
git pull origin main
```

> [!NOTE]
> Selalu lakukan `git pull` sebelum mulai coding untuk memastikan kamu punya versi terbaru.

---

### 5️⃣ Jika Ada Konflik Saat Pull

```bash
# Setelah pull, git akan memberitahu file mana yang konflik
git pull origin main

# Buka file yang konflik, cari marker seperti ini:
# <<<<<<< HEAD
# kode kamu
# =======
# kode collaborator
# >>>>>>> origin/main

# Edit file, pilih mana yang benar, hapus marker konflik
# Lalu:
git add .
git commit -m "fix: resolve merge conflict"
git push
```

---

## 👥 BAGIAN COLLABORATOR

### 1️⃣ Clone Repository

> Lakukan ini **sekali saja** di awal.

```bash
# Clone repo dari GitHub (minta URL dari Owner)
git clone https://github.com/username-owner/nama-repo.git

# Masuk ke folder project
cd nama-repo

# Install dependencies (untuk Laravel)
composer install
npm install
cp .env.example .env
php artisan key:generate
```

---

### 2️⃣ Buat Branch Baru Sebelum Coding

> **JANGAN pernah langsung coding di branch `main`!**

```bash
# Pastikan branch main kamu sudah terbaru
git checkout main
git pull origin main

# Buat branch baru dengan nama yang deskriptif
git checkout -b nama-branch

# Contoh nama branch yang baik:
git checkout -b fix/dashboard-navigation
git checkout -b feat/tambah-filter-kurir
git checkout -b style/perbaikan-tampilan-payroll
```

> [!IMPORTANT]
> Gunakan **satu branch per fitur/perbaikan**. Jangan campur banyak pekerjaan dalam satu branch.

---

### 3️⃣ Coding, Commit, dan Push Branch

```bash
# Koding seperti biasa...

# Cek perubahan
git status

# Tambahkan file ke staging
git add .

# Commit
git commit -m "fix: perbaikan navigasi grid active couriers"

# Push branch ke GitHub (pertama kali)
git push -u origin nama-branch

# Push selanjutnya cukup:
git push
```

---

### 4️⃣ Buat Pull Request ke Owner

1. Buka repo di **GitHub.com**
2. GitHub biasanya langsung menampilkan banner **"Compare & pull request"** — klik itu
3. Atau manual: klik tab **Pull requests** → **New pull request**
4. Pilih: **base: main** ← **compare: nama-branch-kamu**
5. Isi **judul** dan **deskripsi** perubahan yang kamu buat
6. Klik **Create pull request**
7. **Tunggu Owner mereview dan merge**

---

### 5️⃣ Ambil Update Terbaru dari Owner (Sync)

> Lakukan ini **setiap mulai kerja** agar kode kamu selalu up-to-date.

```bash
# Pindah ke branch main
git checkout main

# Ambil semua update terbaru
git pull origin main

# Kembali ke branch kerja kamu
git checkout nama-branch-kamu

# Merge update main ke branch kamu (agar tidak ketinggalan)
git merge main
```

> [!TIP]
> Alternatif lebih bersih menggunakan `rebase`:
> ```bash
> git rebase main
> ```
> Ini membuat history commit lebih linear dan rapi.

---

### 6️⃣ Setelah PR Di-merge — Bersihkan Branch Lama

```bash
# Kembali ke main dan pull terbaru
git checkout main
git pull origin main

# Hapus branch lama di local
git branch -d nama-branch-lama

# Hapus branch lama di GitHub
git push origin --delete nama-branch-lama
```

---

## 📋 Cheat Sheet Cepat

| Perintah                  | Fungsi                                    |
|---------------------------|-------------------------------------------|
| `git status`              | Lihat file yang berubah                   |
| `git add .`               | Staging semua perubahan                   |
| `git commit -m "pesan"`   | Simpan snapshot perubahan                 |
| `git push`                | Upload ke GitHub                          |
| `git pull`                | Download update terbaru                   |
| `git checkout -b nama`    | Buat & pindah ke branch baru             |
| `git checkout main`       | Pindah ke branch main                    |
| `git branch`              | Lihat semua branch lokal                  |
| `git log --oneline`       | Lihat history commit ringkas              |
| `git merge main`          | Gabungkan update main ke branch aktif    |

---

## ⚠️ Aturan Emas Kolaborasi

> [!CAUTION]
> **Jangan pernah `force push` ke branch main** (`git push --force origin main`). Ini akan menghapus history commit orang lain!

> [!WARNING]
> **Jangan commit langsung ke `main`**. Selalu gunakan branch terpisah dan Pull Request agar Owner bisa review terlebih dahulu.

> [!NOTE]
> Selalu **`git pull` sebelum mulai kerja** setiap hari untuk menghindari konflik yang besar.
