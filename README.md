# Sistem LPPM Universitas Mayasari Bakti

Laravel 10.50.2 dengan Blade, Vite, MySQL, SSO, dan workflow hibah internal penelitian/PkM. Implementasi SOP 004-UMB-LPPM (1 Februari 2026), terutama SOP 5, 6, 7, 9, 10, 11, 18, 35, 38, dan 40.

## Panduan dan handover QA

- [Panduan pemakaian hibah dari awal sampai selesai](doc/panduan_pengguna_hibah.md).
- [Daftar dokumen dan 52 langkah SOP](doc/daftar_dokumen_hibah.md).
- [Checklist dan format laporan QA kampus](doc/checklist_qa_hibah.md).
- [Login lokal sementara dan daftar identitas akun](doc/login_lokal_sementara.md).
- [Temuan audit yang masih terbuka](doc/audit_ulang_laravel10.md) dan [hasil pemeriksaan responsivitas](doc/responsivitas_2026-11-20.md).

Kondisi handover 20 November 2026: Laravel **10.50.2** tetap versi awal, login lokal sementara aktif untuk audit, 22 akun dibuat atas permintaan pengguna, satu periode hibah lokal terbuka 20 November-19 Desember 2026, dan QA sudah dapat membuat proposal sendiri. Periode lokal itu tidak menyatakan dokumen persiapan resmi sudah disahkan. Temuan A01-A03 dan advisory PHP masih terbuka; jangan memakai laporan nol advisory pada upgrade Laravel 12 yang sudah dibatalkan sebagai status saat ini.

Akun, password, periode dan proposal pada komputer pengembang **tidak ikut terbentuk otomatis dari clone GitHub**. Berkas password privat tidak boleh disertakan dalam repository. Panduan ini bukan keputusan penerimaan QA atau deployment; commit/push mengikuti instruksi pemilik repository.

## Instalasi dari GitHub

Panduan lengkap: [instalasi baru, konfigurasi MySQL, akun pertama dan troubleshooting](doc/instalasi_dari_github.md).

Kebutuhan lockfile saat ini: **PHP 8.2-8.5** beserta ekstensi yang diperiksa Composer, Composer 2, MySQL, serta Node.js 22.12+. Batas PHP ini berasal dari dependency yang sudah ada pada lockfile awal, bukan pembaruan Laravel. Gunakan versi kode yang sudah dipublikasikan dan disetujui tim; perubahan lokal belum otomatis ada di GitHub sampai ada push yang diizinkan.

```bash
git clone https://github.com/arfajhf/lppm.git
cd lppm
composer install
composer check-platform-reqs
npm ci
cp .env.example .env
```

Langkah salin `.env` hanya untuk instalasi baru. Buat database/user MySQL, lalu isi `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_URL=http://127.0.0.1:8001` dan `APP_TIMEZONE=Asia/Jakarta` sesuai lingkungan. Untuk login lokal sementara, set `APP_ENV=local`, `APP_DEBUG=false`, `LOCAL_AUTH_ENABLED=true`. Konfigurasi SSO diperoleh dari pengelola, bukan memakai kredensial pada panduan.

```bash
php artisan key:generate
php artisan config:clear
php artisan migrate
npm run build
php artisan auth:local-user admin@example.ex --name=admin --role=lppm_admin
php artisan serve --host=127.0.0.1 --port=8001
```

Buka **http://127.0.0.1:8001/login**, pilih login lokal, lalu pakai password pribadi yang diminta interaktif saat akun dibuat. Akun/role lain dan periode dibuat melalui alur pada panduan. Clone tidak membawa database, 22 akun, password, atau periode dari komputer pengembang.

Gunakan `composer install`/`npm ci`, bukan pembaruan dependency. Jangan menjalankan `key:generate` pada instalasi lama atau `migrate:fresh` pada database yang berisi data. Dokumen hibah privat tidak memerlukan `storage:link`. Untuk SSO, callback tetap `/auth/sso/callback` dan provider harus mengembalikan OAuth state yang sama.

## Alur operasional

1. LPPM membuka **Hibah Internal** dan membuat draft periode. Halaman **Persiapan, tindak lanjut, dan rekap SOP** menampung nota dinas, jadwal, sosialisasi, pengumuman, serta koordinasi fakultas/prodi. Periode baru dapat diterbitkan setelah langkah persiapan lengkap. Periode calon reviewer dibuat terpisah dengan jenis penerimaan reviewer.
2. Pengusul membuat proposal hibah sebagai **draft**, melengkapi tim dan ringkasan, lalu meminta pengesahan Dekan. Rekomendasi roadmap fakultas/prodi adalah kelengkapan wajib pada tahap validasi; dapat diisi Dekan atau Kaprodi sesuai lingkup, bukan tahap persetujuan Kaprodi terpisah sebelum Dekan.
3. Dekan sesuai fakultas mengunggah halaman pengesahan. Pengusul mengunggah proposal dan mengirim ke validasi LPPM dalam periode yang terbuka.
4. LPPM memeriksa proposal, tim, pengesahan, dan roadmap. Revisi harus memiliki perbaikan atau tindak lanjut sebelum dikirim ulang.
5. Calon reviewer mendaftar beserta portofolio. Pengumuman penerimaan tersedia melalui `/announcements`. Reviewer internal mendapatkan rekomendasi Dekan/Wr; rekomendasi eksternal dicatat LPPM dari surat instansi eksternal. Dekan/Wr mengkaji rekomendasi internal (SOP 6.3); LPPM melakukan seleksi berdasarkan instrumen resmi, lalu Rektor mencatat SK dan masa berlaku. Reviewer eksternal mengikuti permohonan LPPM → surat Rektor ke instansi → rekomendasi resmi instansi → SK Rektor, tanpa menambahkan seleksi internal SOP 35. Sesudah penetapan, LPPM dapat menerbitkan daftar nama reviewer pada halaman pengumuman publik; portofolio dan surat rekomendasi tetap privat. Pada build ini akun penilai perlu role reviewer serta permission penilaian. Kasus permission SSO tanpa nama role reviewer masih terblokir oleh temuan A02, bukan otomatis terselesaikan dengan provisioning permission.
6. LPPM menugaskan reviewer proposal, monev, atau seminar sesuai tahap. Penugasan membutuhkan SK aktif; untuk reviewer internal, pengumuman hasil dan bukti penyampaian daftar reviewer (SOP 35.5) juga harus lengkap; pengusul dan anggota tim tidak boleh menilai kegiatannya. Setiap reviewer mengisi skor dan catatan. Sesudah revisi pada tahap review/monev, penugasan dan penilaian harus dibuat ulang untuk putaran tersebut.
7. LPPM meneruskan hasil review untuk penetapan penerima. Rektor mencatat SK dan nilai hibah yang disetujui.
8. Kontrak bertanda tangan dicatat sebelum pencairan 70%. Petugas keuangan mengunggah bukti transfer dan kuitansi. Sistem menghitung nominal rupiah bulat, dengan sisa pembulatan pada pencairan kedua agar total tepat 100%.
9. Peneliti mengisi logbook selama tahap pelaksanaan, lalu masuk tahap laporan kemajuan 70% tersendiri (SOP 18.5). LPPM menugaskan monev; semua penilai menyelesaikan tugas sebelum hasil monev dapat diterima.
10. Pencairan 30% membutuhkan hasil monev dan bukti pembayaran kedua. Peneliti kemudian mengunggah laporan akhir, realisasi anggaran 100%, dan bukti luaran.
11. Seminar hasil dilengkapi penugasan, penilaian, dan berita acara. Peneliti mengunggah laporan final diperbaiki.
12. Peneliti menyerahkan berkas fisik; LPPM memeriksa laporan dan kesesuaian data/kontrak, memvalidasi luaran, serta mencatat tanda terima. Peneliti menyerahkan arsip akhir; LPPM memeriksa klasifikasi, digitalisasi, dan lokasi fisik. Kepala LPPM mengesahkan inventaris sebelum kegiatan ditutup. Pemeliharaan/retensi dicatat sesudahnya tanpa menghapus audit trail elektronik.

Proposal hibah lama tidak otomatis diubah. Pada halaman detail tersedia tindakan **Masukkan proposal lama ke alur SOP hibah** untuk pemilik/LPPM: memilih periode terbuka, memulai draft, dan mencatat status sebelumnya. Berkas dan data lama tetap disimpan. Endpoint persetujuan lama tidak dapat dipakai untuk melewati workflow hibah.

## Role dan SSO

| Role | Kewenangan |
|---|---|
| dosen | Pengajuan, tim, revisi, logbook, laporan, luaran |
| mahasiswa | Melihat kegiatan sebagai anggota sesuai akses |
| kaprodi | Verifikasi roadmap lingkup prodi |
| dekan | Pengesahan proposal dan rekomendasi reviewer lingkup fakultas |
| lppm_admin | Periode, validasi, seleksi reviewer, penugasan, tindak lanjut |
| sekretaris_lppm | Sosialisasi jadwal/panduan tanpa hak menerbitkan periode |
| reviewer | Melihat tugas dan memberikan penilaian |
| rektor | Mencatat SK reviewer dan penetapan penerima hibah |
| wakil_rektor | Rekomendasi reviewer internal |
| keuangan | Bukti pencairan 70% dan 30% |
| kepala_lppm | Administrasi LPPM dan pengesahan arsip akhir |
| rektorat | Pemantauan kegiatan yang disetujui/selesai |

Role teknis `super_admin` mengelola administrasi; tidak otomatis mendapat hak pengesahan Rektor, pencairan, atau pengesahan arsip. Pengguna dapat memiliki beberapa role sesuai penetapan resmi. Keputusan institusional atas proposal sendiri diblokir. Pengusul yang menjabat Dekan membutuhkan pejabat lain yang sah dengan kewenangan dan lingkup fakultas yang sesuai; sistem tidak lagi melewati pengesahan secara otomatis untuk hibah.

Permission baru yang harus diprovisikan pada server SSO:

- `lppm.grant.socialise`: sekretaris/staf LPPM atau admin yang mendapat tugas sekretariat (SOP 4.2). Role lokal `staff`/`sekretaris_lppm` dapat memakai permission ini untuk mencatat sosialisasi tanpa kewenangan menerbitkan periode.

- `lppm.grant.manage`: LPPM / Kepala LPPM / pengelola administrasi.
- `lppm.grant.award`: Rektor.
- `lppm.grant.disburse`: petugas pencatatan keuangan yang ditunjuk.
- `lppm.grant.archive`: Kepala LPPM.

Tetap gunakan permission dasar seperti `lppm.proposal.view`, `lppm.proposal.view_all` untuk pengelola lintas kegiatan, `lppm.proposal.create`, `lppm.proposal.update`, `lppm.proposal.submit`, `lppm.document.upload`, dan `lppm.review.score` sesuai tugas. Jika SSO sudah mengirim permission `lppm.*`, daftar tersebut dipakai langsung; fallback lokal hanya dipakai jika daftar permission LPPM kosong. Karena itu penambahan role saja tidak cukup bagi akun yang sudah mempunyai permission eksplisit. Konfigurasi di repo tidak mengubah server SSO secara otomatis.

## Tenggat dan pengingat

Setiap langkah memiliki acuan halaman PDF dan durasi/rentang aslinya. Tahap pengesahan 3 hari, unggah 30 hari, validasi 3 hari, pelaksanaan 90 hari, laporan kemajuan 1 hari, laporan akhir 12 hari, dan revisi seminar 14 hari dipisahkan. Tidak ada durasi SOP fiktif untuk review proposal. Rincian monev SOP 11 tetap terpisah dari aktivitas monev 1 hari pada SOP 18.6; lihat catatan perbedaan sumber pada laporan audit.

Penerimaan hibah beracuan 30 hari dan pendaftaran reviewer 7–14 hari (inklusif). Jadwal berbeda membutuhkan dasar perubahan resmi yang tercatat pada periode. Tenggat tahunan tanpa tanggal kalender tidak diada-adakan dari tanggal pembuatan periode. Pengingat juga ditujukan ke pelaksana langkah SOP dan reviewer yang memiliki tugas belum dinilai. Temuan A03 masih dapat mengirim pengingat untuk assignment putaran lama; menu Review/detail hibah menjadi acuan putaran aktif sampai temuan itu diperbaiki.
```bash
php artisan grants:remind
```

Pengingat dalam aplikasi dijadwalkan pukul 08.00 Asia/Jakarta untuk tenggat tiga hari ke depan/terlewat, maksimal satu pengingat per proposal per hari. Aktifkan scheduler Laravel pada server:

```cron
* * * * * cd /path/to/lppm && php artisan schedule:run >> /dev/null 2>&1
```

## Audit kesesuaian SOP

Lihat [audit hibah per langkah](doc/audit_hibah_sop.md). Katalog `config/grant_sop.php` berisi 52 langkah terstruktur dan bukti pemetaan langkah sistem lainnya. Halaman **Lengkapi langkah dan bukti SOP** tersedia pada proposal, periode, dan penetapan reviewer.

Setiap langkah menyimpan aktor, referensi, checklist, berkas, waktu, dan prasyarat. Bukti pencatatan dapat dicetak; dokumen tetap privat kecuali pengumuman/SK yang memang diterbitkan melalui proses publikasi. Identitas luaran (jenis, judul, DOI/ISBN/nomor KI, dan URL opsional) serta jenis laporan tampil di modul laporan. Penilaian membutuhkan borang resmi PDF dan temuan/rekomendasi; PDF sumber tidak menyediakan rubrik/bobot, sehingga aplikasi tidak mengarang ambang kelulusan.

Penambahan ini memakai dua migrasi tambahan (`020000` dan `030000`). Jalankan `php artisan migrate`. Rekaman lama tidak otomatis ditandai memenuhi checklist baru: verifikasi berkas dan langkahnya sebelum dipakai dalam alur yang diperketat. Sistem belum dinyatakan sesuai 100% secara operasional tanpa uji penerimaan institusi, terutama untuk interpretasi diagram yang ambigu dan proses eksternal.

## Dependency frontend

Pembaruan keamanan 19 November 2026 memakai Axios 1.21.1, Vite 7.3.8, dan laravel-vite-plugin 2.1.0 beserta dependency transitif dari lockfile. Audit npm seluruh dependency turun dari 9 menjadi 0 kerentanan terdeteksi. `package.json` menggunakan ESM dan menetapkan Node.js minimal 22.12. Target browser produksi sebelumnya dipertahankan secara eksplisit pada `vite.config.js`.

Instalasi bersih dari lockfile, build produksi, inisialisasi Axios, transformasi aset dev server, dan integrasi manifest Laravel diperiksa. Endpoint dev server menolak akses `.env`. Jalankan `npm audit` kembali saat memperbarui dependency karena advisory dapat bertambah.

## Validasi

```bash
php artisan test
php artisan view:cache
npm run build
npm audit
composer audit
```

Tes memakai MySQL pada database khusus `lppm_test` dengan akun `lppm_test`. Kredensial dibaca dari `.env.testing` (tidak masuk Git); contoh tersedia pada `.env.testing.example`. Bootstrap tes menolak database/akun selain target pengujian agar data aplikasi tidak terhapus. Cakupan meliputi workflow lengkap, dokumen wajib, pencairan 70/30, revisi, larangan persetujuan sendiri, privasi dokumen, keamanan API, state SSO, serta pendaftaran sampai penetapan reviewer. Uji penerimaan dengan server SSO dan MySQL institusi tetap diperlukan sebelum penerapan produksi.

## Batas integrasi

- SK, kontrak, rekomendasi, kuitansi, dan bukti transfer adalah dokumen resmi yang diunggah. Sistem tidak menerbitkan tanda tangan elektronik atau menjalankan transfer bank.
- Pemeriksaan isi dokumen dan keabsahan berkas fisik dilakukan petugas; aplikasi memeriksa akses, tahapan, jenis berkas, dan kelengkapan.
- Pengiriman surat ke instansi luar/Dit. Litabmas, sinkronisasi SINTA/SISTER/PDDIKTI, serta retensi/pemusnahan arsip belum merupakan integrasi otomatis.
- Hibah eksternal dan skema mandiri belum dipindahkan ke workflow hibah internal ini.

## Database dan alamat lokal

Driver aplikasi adalah `mysql`. Host, port, nama database, username dan password mengikuti `.env` privat milik operator. Jangan menganggap database aplikasi pasti bernama `lppm_local` atau memakai port 3307; konfigurasi yang dipakai pada komputer QA bisa berbeda.

Lingkungan pengujian otomatis memakai database terpisah `lppm_test`, akun `lppm_test`, dengan pengaturan pada `phpunit.xml` dan password di `.env.testing`. MySQL 8.4 rootless Podman `lppm-mysql84` pada 127.0.0.1:3307 tersedia di komputer pengembang; volume datanya `lppm-mysql84-data`. Itu detail mesin pengembang, bukan dependency hosting wajib atau kredensial instalasi kampus.

```bash
# Hanya bila memakai container MySQL pengembang yang sudah disiapkan:
podman start lppm-mysql84

# Contoh alamat yang dipakai untuk pemeriksaan UI:
php artisan serve --host=127.0.0.1 --port=8001
```

Jika memakai port 8000, gunakan port itu secara konsisten untuk login dan pengujian; jangan berpindah localhost/127.0.0.1 di tengah sesi. Domain dan callback SSO tetap perlu mengikuti konfigurasi yang disetujui pihak pengelola SSO.

## Perbaikan audit hibah 20 November 2026

Perbaikan fitur H01-H08 dipertahankan pada kode lokal; pembaruan dependency H09 dibatalkan sesuai kesepakatan versi awal. Perbaikan fitur meliputi: lingkup fakultas/prodi dan role gabungan, antrean review hibah, penguncian dokumen saat pemeriksaan, form putaran aktif, validasi PDF arsip pada upload/pemakaian ulang, pemeriksaan SK reviewer eksternal, daftar hibah berjalan. Tes regresi kini berada di suite rutin `tests/Feature/GrantDeepAuditTest.php` dan `GrantRoleSmokeTest.php`. Alur penuh dites untuk Penelitian Hibah dan Pengabdian Hibah.

Framework dan dependency dikembalikan persis ke composer.json/composer.lock awal: Laravel 10.50.2, Sanctum 3.3.3, Carbon 2.73.0, PHPUnit 10.5.63; PHP minimal 8.1. Perbaikan fitur hibah dipertahankan. Login, logout, endpoint dan kredensial SSO tidak diubah. Hasil audit dependency nol advisory pada Laravel 12 adalah riwayat, bukan kondisi dependency setelah pemulihan versi asli.

Permission SSO tetap menjadi otoritas bila dikirim oleh institusi. Aktifkan permission `lppm.grant.*` sesuai role di SSO dan `lppm.review.score` bagi reviewer yang sah; aplikasi tidak menambah privilege diam-diam. Penetapan SK adalah kelengkapan administrasi, bukan pengubahan role pada server SSO. Sinkronisasi nasional tetap berupa pencatatan bukti proses manual, bukan konektor tanpa kredensial. Untuk pengingat saat lokal, jalankan `php artisan schedule:work` pada terminal terpisah; konfigurasi scheduler tersedia pada `php artisan schedule:list`.

## Login lokal sementara

Atas permintaan untuk audit manual selama SSO disesuaikan, tersedia login email/password pada /login. Aktif hanya bila APP_ENV=local dan LOCAL_AUTH_ENABLED=true, dari loopback. Identitas, role dan lingkup tersimpan di database; tidak ada pemilihan role atau akun bawaan otomatis. Sebanyak 22 akun dibuat secara eksplisit sesuai permintaan pengguna. Kemudian satu periode penerimaan lokal dibuka atas permintaan untuk pengujian, dan pengguna sudah membuat proposal sendiri; ini bukan data bawaan instalasi. Panduan dan daftar identitas: [login lokal sementara](doc/login_lokal_sementara.md). Password berada hanya di berkas privat storage/app/local-auth/DAFTAR_AKUN_LOKAL.csv yang diabaikan Git. Jalur SSO dan dependency Laravel 10 tetap dipertahankan.


Untuk instalasi lokal baru yang belum memiliki akun, operator mengatur `.env` (tanpa mengganti konfigurasi SSO):

```dotenv
APP_ENV=local
APP_DEBUG=false
LOCAL_AUTH_ENABLED=true
```

Lalu buat akun pribadi pertama dengan role administrasi dan password interaktif:

```bash
php artisan config:clear
php artisan auth:local-user admin@example.ex --name=admin --role=lppm_admin
```

Perintah hanya bekerja di lingkungan lokal dengan flag aktif dan menolak menimpa akun existing. Akun role lain dibuat sesuai kebutuhan dan lingkup fakultas/prodi; tidak ada seeder yang otomatis menyalin 22 akun pemeriksaan. Lihat [pengelolaan akun lokal](doc/login_lokal_sementara.md#pembatasan-dan-pengelolaan). Setelah SSO siap dan sesuai hasil QA, nonaktifkan flag dengan konfigurasi operator; jangan mengaktifkan login lokal pada produksi.

## Status pemeriksaan dan sebelum push

Pemeriksaan terakhir yang tersimpan mencatat 61 tes/1.098 assertion, build/Blade berhasil, dan pemeriksaan browser pada 11 ukuran layar. Lihat [checklist QA](doc/checklist_qa_hibah.md) untuk mencatat hasil kampus; pengujian pengembang tidak menggantikan UAT. Audit PHP pada lockfile asli mencatat 50 advisory pada 12 paket, sedangkan npm 0 pada pemeriksaan tersebut. Angka adalah snapshot, bukan jaminan kondisi registry di masa depan.

Sebelum push yang nantinya diizinkan pengguna:

- Tinjau hasil QA dan temuan terbuka; jangan menandai yang terblokir sebagai lulus.
- Tinjau diff/staging secara spesifik. Jangan menyertakan `.env`, CSV/password lokal, database, backup, upload pengguna, sesi atau cache hasil tes. Akun lokal bukan seeder bawaan untuk server kampus.
- Jalankan pemeriksaan yang relevan pada perubahan terakhir dan pastikan panduan sesuai build yang akan dikirim.
- `LOCAL_AUTH_ENABLED` default **false** pada `.env.example`; login sementara tetap dibatasi lokal. Menyimpan kode ke GitHub bukan instruksi mengaktifkannya di produksi atau memigrasikan data lokal.

Pembaruan panduan tidak mengubah versi dependency atau data aplikasi. Commit/push dilakukan terpisah sesuai instruksi pemilik repository; deployment belum dilakukan.

Artefak lokal pada `output/` dan skrip sementara `tmp/` tidak disertakan ke GitHub. Reproduksi temuan A01-A03 tersedia terpisah melalui `php artisan test tests/Audit/GrantEdgeAuditTest.php`; tes ini berada di luar suite rutin dan masih mereproduksi temuan terbuka.
