# Instalasi LPPM dari GitHub untuk pengembangan dan QA lokal

Diperbarui 20 November 2026. Panduan ini memakai **Laravel 10.50.2** dan lockfile proyek yang sudah disepakati. Tidak memerlukan upgrade framework atau akun bawaan.

Perubahan lokal belum dipush pada saat panduan ini ditulis. Setelah versi yang disetujui dipublikasikan, pilih branch/tag yang **memuat panduan dan fitur ini**. Clone branch lama tidak otomatis memperoleh login lokal, workflow hibah terbaru, atau dokumen pada komputer pengembang.

## 1. Siapkan kebutuhan

- Git dan akses ke repository `https://github.com/arfajhf/lppm.git`.
- **PHP 8.2-8.5** sesuai `composer.lock` awal, Composer 2, dan ekstensi PHP yang diperlukan. Pastikan driver `pdo_mysql`, Fileinfo, Mbstring dan XML/DOM tersedia; pemeriksaan lengkap dilakukan oleh Composer.
- MySQL yang dapat diakses dan hak operator untuk membuat database/user.
- Node.js **22.12+** dan npm untuk build aset. Versi Node yang terakhir dipakai pengembang adalah 22.23.1.
- Terminal shell untuk perintah di bawah. Pada Windows, sesuaikan perintah salin file dengan shell yang dipakai, misalnya `Copy-Item` pada PowerShell.

Laravel tetap **10.50.2**. Walaupun constraint framework/proyek menerima PHP 8.1, beberapa dependency pada lockfile awal (misalnya cron-expression, Nette Utils dan Symfony) sudah memerlukan PHP 8.2; karena itu instalasi dari lockfile memakai rentang di atas. Lingkungan pengembang terakhir memakai PHP 8.5.13. Tidak ada dependency yang diubah untuk menulis panduan ini.

Periksa alat sebelum mulai:

```bash
git --version
php -v
composer --version
node -v
npm -v
```

## 2. Clone versi yang disetujui

```bash
git clone https://github.com/arfajhf/lppm.git
cd lppm
git checkout full-request-done-aplication
git branch --show-current
```

Branch QA untuk perubahan ini adalah `full-request-done-aplication`; branch `main` tetap terpisah. Jika tim memakai branch/tag lain, checkout nama versi yang benar-benar sudah dipublikasikan sebelum instalasi. Jangan menganggap seluruh perubahan lokal saat ini sudah ada pada branch default GitHub.

```bash
composer install
composer check-platform-reqs
npm ci
```

Gunakan **install dari lockfile**, bukan `composer update`, `npm update`, atau perubahan versi Laravel. Jika Composer menemukan ekstensi yang kurang, aktifkan ekstensi pada PHP CLI yang ditampilkan `php --ini`, lalu ulangi instalasi.

## 3. Siapkan database aplikasi

Masuk ke MySQL memakai akun operator database. Contoh SQL di bawah untuk instalasi lokal baru; pilih password sendiri, jangan memakai tulisan placeholder sebagai password sungguhan.

```sql
CREATE DATABASE lppm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lppm_app'@'127.0.0.1' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_LOKAL_KUAT';
GRANT ALL PRIVILEGES ON lppm.* TO 'lppm_app'@'127.0.0.1';
```

Akun aplikasi pada contoh hanya memperoleh hak ke database `lppm`. Jika database/user sudah ada, gunakan konfigurasi yang telah ditetapkan operator; jangan menimpa atau menghapus data agar contoh SQL bisa diulang.

## 4. Buat konfigurasi lokal

Hanya untuk instalasi baru yang belum mempunyai `.env`:

```bash
cp .env.example .env
```

PowerShell dapat memakai `Copy-Item .env.example .env`. Jangan menimpa `.env` instalasi yang sudah dipakai.

Edit nilai berikut sesuai komputer QA:

```dotenv
APP_NAME="LPPM"
APP_ENV=local
APP_DEBUG=false
APP_URL=http://127.0.0.1:8001
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lppm
DB_USERNAME=lppm_app
DB_PASSWORD=GANTI_DENGAN_PASSWORD_LOKAL_KUAT

LOCAL_AUTH_ENABLED=true
```

Jika MySQL berjalan pada port lain, ubah `DB_PORT`. Jangan mengambil kredensial database milik komputer pengembang dari panduan; `.env` dan password tidak berada di GitHub.

Konfigurasi `SSO_URL`, `SSO_CLIENT_ID`, `SSO_CLIENT_SECRET` dan `SSO_REDIRECT` diperoleh dari pengelola SSO. Selama pemeriksaan lokal, gunakan form login lokal. Menyalin konfigurasi tidak membuktikan SSO institusi sudah diuji. Callback SSO tetap `/auth/sso/callback`, dan perubahan host/port callback harus sesuai konfigurasi provider.

## 5. Siapkan aplikasi dan aset

Pada instalasi baru saja, buat application key:

```bash
php artisan key:generate
php artisan config:clear
php artisan migrate
npm run build
```

`migrate` menambahkan migrasi yang belum dijalankan. Jangan memakai `migrate:fresh` pada database berisi data. Jangan mengganti application key pada instalasi lama hanya untuk mengikuti langkah instalasi baru.

Dokumen hibah disimpan pada disk lokal privat dan diunduh melalui aplikasi. Tidak perlu `storage:link` untuk membuka dokumen hibah. Pastikan proses PHP dapat menulis ke `storage` dan `bootstrap/cache` dengan izin sesuai pemilik proses; jangan membuka seluruh proyek dengan izin 777.

## 6. Buat akun admin pribadi pertama

Clone baru tidak membawa 22 akun, password, periode atau proposal dari komputer pengembang. Buat akun yang diperlukan secara eksplisit:

```bash
php artisan auth:local-user admin@example.ex --name=admin --role=lppm_admin
```

Masukkan password pribadi saat diminta, lalu ulangi konfirmasinya. Minimal 12 karakter; input password tidak ditulis sebagai argumen shell atau contoh publik. Role Admin LPPM disimpan pada akun, bukan dipilih bebas di halaman login.

Perintah hanya bekerja saat `APP_ENV=local` dan `LOCAL_AUTH_ENABLED=true`. Akun yang sudah ada akan ditolak agar password/role existing tidak tertimpa. Jangan menjalankan seeder data contoh untuk memperoleh akun admin.

Jika ingin password acak, tambahkan `--generate-password`. Perintah akan memberitahu lokasi **berkas privat per akun** di `storage/app/local-auth/`; jangan menyertakannya ke Git. CSV daftar 22 akun pada komputer pengembang bukan berkas yang otomatis dibuat oleh clone atau satu perintah pembuatan akun.

Contoh membuat akun lain untuk pemeriksaan persetujuan:

```bash
php artisan auth:local-user dosen.informatika1@example.ex --name="Dosen Informatika 1" --role=dosen --faculty="Fakultas Teknik" --program="Teknik Informatika"
php artisan auth:local-user dekan.teknik@example.ex --name="Dekan Fakultas Teknik" --role=dekan --faculty="Fakultas Teknik"
php artisan auth:local-user kaprodi.informatika@example.ex --name="Kaprodi Teknik Informatika" --role=kaprodi --faculty="Fakultas Teknik" --program="Teknik Informatika"
php artisan auth:local-user reviewer.teknik@example.ex --name="Reviewer Teknik" --role=reviewer --faculty="Fakultas Teknik" --program="Teknik Informatika"
```

Masing-masing perintah meminta password terpisah. Buat pula Rektor, Keuangan, Sekretaris dan Kepala LPPM sesuai kebutuhan alur. [Daftar identitas dan lingkup yang pernah disiapkan](login_lokal_sementara.md) dapat menjadi referensi, bukan akun yang pasti sudah tersedia pada instalasi baru. Akun reviewer masih memerlukan proses penetapan/SK sebelum bisa ditugaskan.

## 7. Jalankan dan buka aplikasi

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

Biarkan terminal itu berjalan, lalu buka **http://127.0.0.1:8001/login**. Pilih **Login lokal sementara**, email **admin@example.ex**, dan password yang baru ditetapkan.

Bila port 8001 sudah dipakai, pilih port lain dan sesuaikan `APP_URL`. Gunakan alamat/port yang sama selama satu sesi. Login lokal dibatasi ke loopback; membuka alamat IP komputer dari LAN tidak otomatis mengaktifkan login lokal untuk QA jarak jauh.

Lanjutkan [panduan pemakaian hibah](panduan_pengguna_hibah.md) dan [checklist QA kampus](checklist_qa_hibah.md). Pada instalasi baru, buat periode dan lengkapi persiapan SOP sebelum penerbitan normal. Periode November yang pernah dibuka untuk audit hanya berada pada database komputer pengembang.

Untuk pengingat lokal berkelanjutan, buka terminal kedua:

```bash
php artisan schedule:work
```

Ini diperlukan bila pengujian QA mencakup pengiriman pengingat. Tidak perlu menjalankan Vite dev server untuk memakai aset yang sudah dihasilkan `npm run build`.

## 8. Pengujian otomatis opsional

Pengujian wajib memakai database **terpisah**, bukan database aplikasi. Bootstrap tes memeriksa nama database dan username **`lppm_test`**. Contoh pembuatan oleh operator MySQL:

```sql
CREATE DATABASE lppm_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lppm_test'@'127.0.0.1' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_TES_TERPISAH';
GRANT ALL PRIVILEGES ON lppm_test.* TO 'lppm_test'@'127.0.0.1';
```

Buat `.env.testing` dari `.env.testing.example` jika belum ada. Isi password tes dan koneksinya. **`phpunit.xml` saat ini menetapkan port 3307**; bila MySQL QA memakai port 3306, sesuaikan `DB_PORT` di `phpunit.xml` dan `.env.testing` agar keduanya menunjuk server tes yang sama. Jangan mengganti database/username tes dengan akun aplikasi untuk mengatasi error.

Untuk file testing baru dengan key kosong:

```bash
php artisan key:generate --env=testing
php artisan test
```

Suite rutin berbeda dari tiga tes reproduksi temuan yang masih terbuka pada audit. Kelulusan suite rutin tidak berarti A01-A03 atau advisory PHP sudah selesai. Rincian ada pada [checklist QA](checklist_qa_hibah.md#temuan-terbuka-yang-harus-dicatat).

## 9. Memperbarui instalasi yang sudah ada

Simpan backup database/unggahan dan catat versi yang dipakai. Ambil perubahan Git yang disetujui sesuai alur tim; jangan menimpa perubahan lokal yang belum ditinjau. Setelah kode diperbarui, gunakan:

```bash
composer install
npm ci
php artisan config:clear
php artisan migrate
npm run build
php artisan view:clear
```

Pertahankan `.env`, application key, database, dokumen dan kredensial existing. Restart server lokal bila perlu dan refresh paksa browser setelah aset berubah. Akun yang diganti namanya pada komputer pengembang tidak otomatis mengubah akun di instalasi lain.

## 10. Penyelesaian masalah

| Gejala | Pemeriksaan |
|---|---|
| Perintah auth:local-user tidak ada | Pastikan branch/tag memuat versi fitur ini; perubahan lokal belum tentu sudah dipublikasikan ke GitHub. |
| Could not find driver | Pastikan PHP CLI memuat pdo_mysql; periksa php --ini. |
| Unknown database / access denied | Periksa database, host/port, user, password dan host akun MySQL; contoh memakai 127.0.0.1. |
| No application encryption key | Pada instalasi baru, jalankan key:generate setelah .env dibuat. Pertahankan key instalasi lama. |
| Form login lokal tidak muncul / 404 | Periksa APP_ENV=local, LOCAL_AUTH_ENABLED=true, asal loopback dan config:clear. |
| Halaman tanpa CSS / manifest tidak ada | Jalankan npm ci dan npm run build; periksa APP_URL, lalu refresh paksa. |
| 419 | Gunakan host/port konsisten, refresh dan login ulang. |
| Port sudah dipakai | Gunakan port yang tersedia, sesuaikan URL dan restart server. |
| Tes gagal koneksi | Cocokkan .env.testing dan phpunit.xml, khususnya port 3307/3306; jangan memakai database aplikasi. |

## Batas handover

Panduan ini untuk pengembangan dan QA lokal, bukan perintah deployment produksi. `.env.example` tetap menonaktifkan login lokal secara default. Sebelum lingkungan institusi dipakai, konfigurasi autentikasi/SSO dan temuan terbuka harus ditinjau sesuai hasil QA. Tidak ada password, database, unggahan atau akun komputer pengembang yang perlu dipush untuk mengikuti instalasi ini.
