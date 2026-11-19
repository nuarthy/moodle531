# Panduan Development — Moodle 5.3.1 + MB2NL

Repo ini berisi **kode** Moodle 5.3.1, theme MB2NL 11.8.1, dan plugin MB2
(`local_mb2builder`, `local_mb2megamenu`, `local_mb2reviews`,
`local_mb2coursenotes`, `filter_mb2shortcodes`) yang sudah disesuaikan.

Database **tidak** disimpan di Git. Setiap developer memakai database lokal
sendiri.

---

## 1. Setup pertama kali (per developer)

### 1.1 Clone repo

```bash
git clone https://github.com/AlfinMoh24/moodle531.git
cd moodle531
```

### 1.2 Siapkan database sendiri

Buat database dengan nama unik, contoh:

| Developer | Database |
|---|---|
| Developer A | `moodle531_alpin` |
| Developer B | `moodle531_nama2` |

```sql
CREATE DATABASE moodle531_alpin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 1.3 Siapkan folder moodledata sendiri

Contoh: `C:\xampp\moodledata-dev-a` (harus **di luar** folder web).

### 1.4 Buat config.php

```bash
copy config.example.php config.php     # Windows
# cp config.example.php config.php     # Linux/Mac
```

Edit `config.php`: sesuaikan `dbname`, `dbuser`, `dbpass`, `dataroot`, `wwwroot`.
File `config.php` sudah masuk `.gitignore`, jadi tidak akan ter-commit.

### 1.5 Instalasi

**Opsi A — instalasi baru:**

```bash
php admin/cli/install.php \
  --wwwroot=http://localhost/moodle \
  --dataroot=C:\xampp\moodledata-dev-a \
  --dbtype=mariadb \
  --dbhost=localhost \
  --dbname=moodle531_alpin \
  --dbuser=root \
  --dbpass= \
  --fullname="Moodle Dev" \
  --shortname="Moodle" \
  --adminuser=admin \
  --adminpass="PasswordKuat123!" \
  --non-interactive
```

**Opsi B — restore data dari rekan/tim (dump seed):**

```bash
mysql -u root moodle531_alpin < seed.sql
# lalu sesuaikan wwwroot bila berbeda
php admin/cli/cfg.php --name=wwwroot --set=http://localhost/moodle
```

> Dump seed (.sql) **jangan di-commit ke repo**. Simpan di drive bersama
> (Google Drive/OneDrive/internal) dan beri tahu versi kodenya (commit hash).

### 1.6 Aktifkan filter yang dibutuhkan

Di Moodle: *Site administration → Plugins → Filters → Manage filters*
- Aktifkan **Mb2 Shortcodes**
- Geser **Mb2 Shortcodes** di atas **Convert URLs into links and images**

### 1.7 (Opsional) Halaman Home builder

```bash
php tools/create-site-home.php
```

---

## 2. Alur kerja harian (2 developer)

```
main      → stabil / produksi
dev       → integrasi
feature/x → satu fitur / satu perbaikan
```

### Mulai fitur baru

```bash
git checkout dev
git pull origin dev
git checkout -b feature/nama-fitur
```

### Setelah selesai

```bash
git add .
git commit -m "Deskripsi perubahan"
git push -u origin feature/nama-fitur
```

Buka Pull Request ke `dev` → review → merge.
Setelah stabil, `dev` di-merge ke `main`.

### Setelah `git pull`

Selalu jalankan:

```bash
php admin/cli/upgrade.php --non-interactive
php admin/cli/purge_caches.php
```

`upgrade.php` akan menyesuaikan **database lokal Anda** dengan perubahan
skema yang ada di kode. Inilah pengganti "merge database".

---

## 3. Perubahan database / skema

**Jangan** `ALTER TABLE` manual di DB lokal.

Lakukan lewat kode:

1. Plugin: `db/install.xml` (struktur baru) atau `db/upgrade.php` (perubahan).
2. Naikkan `version.php` plugin.
3. Commit → developer lain `git pull` → `php admin/cli/upgrade.php`.

Moodle akan menjalankan perubahan skema di DB masing-masing secara identik.

---

## 4. Yang TIDAK boleh di-commit

| Item | Alasan |
|---|---|
| `config.php` | berisi kredensial DB lokal, berbeda tiap orang |
| `moodledata/` | file upload, cache, session — besar & mesin-spesifik |
| `*.sql` (dump) | tidak bisa di-merge, besar, berisi data lokal |
| `*.log`, `.idea/`, `.vscode/` | file lokal/editor |

Sudah diatur di `.gitignore`.

---

## 5. Berbagi data/konten

Karena DB tidak di-merge:

- **Konten kursus**: *Course administration → Backup* (`.mbz`) lalu *Restore*
  di situs rekan.
- **Data lengkap**: dump DB + folder `moodledata/filedir` (keduanya harus
  berpasangan), simpan di luar repo.
- **Uji integrasi**: gunakan satu **staging** bersama (1 DB + 1 moodledata)
  dari branch `dev`; jangan edit kursus yang sama bersamaan karena Moodle
  tidak punya mekanisme merge konten (last write wins).

---

## 6. Optimasi performa

Theme MB2NL memuat tiga library builder di semua halaman secara default:
**Swiper** (carousel), **Jarallax** (parallax), dan **Typed** (teks beranimasi).
Jika halaman/halaman kursus tidak memakai fitur tersebut, matikan di
*Site administration → Appearance → New Learning → Features → Optional scripts*.

Pengaruhnya:

| Setting | Hemat per halaman | Matikan hanya jika |
|---|---|---|
| Load carousel library | ~40 KB | tidak ada slider/carousel |
| Load parallax library | ~5 KB | tidak ada row dengan opsi parallax |
| Load typed text library | ~3 KB | tidak ada heading dengan typed text |

Dua modul JS theme (megamenu dan sticky nav) juga sekarang hanya dimuat saat
fitur terkait aktif. Font Google tidak lagi memblokir render halaman.

Setelah mengubah setting theme, jalankan:
```bash
php admin/cli/purge_caches.php
```

---

## 7. Troubleshooting

| Gejala | Solusi |
|---|---|
| "Site is being upgraded" | jalankan `php admin/cli/upgrade.php --non-interactive` |
| Halaman blank setelah pull | `php admin/cli/purge_caches.php` |
| URL salah / redirect aneh | cek `$CFG->wwwroot` di `config.php` |
| Error "Cannot write to dataroot" | pastikan folder `dataroot` ada dan writable |
| Theme tampil tanpa CSS | `php admin/cli/build_theme_css.php --themes=mb2nl` lalu purge cache |
