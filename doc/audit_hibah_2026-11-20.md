# Audit mendalam hibah internal - 20 November 2026

> **Versi aktif dikembalikan ke Laravel 10.50.2 sesuai kesepakatan proyek.** Perbaikan fitur H01-H08 tetap ada; pembaruan dependency H09 ke Laravel 12 dibatalkan. Angka nol advisory di catatan sebelumnya tidak berlaku untuk lockfile asli.

> **Status sesudah perbaikan:** H01-H09 telah ditangani dan 52 tes lulus (1.045 assertion). Lihat `doc/perbaikan_hibah_2026-11-20.md`. Bagian berikut menyimpan kondisi dan reproduksi sebelum perbaikan. Tes regresi sudah dipindah ke suite rutin; path tmp/audit pada catatan historis tidak lagi dipakai.

## Kesimpulan

Belum dapat dinyatakan seluruh fitur hibah selesai dan bebas bug. Tes dasar lulus, tetapi delapan pemeriksaan tambahan gagal memenuhi kriteria audit. Ada juga advisory dependency PHP. Hasil ini menggantikan kesan bahwa kelulusan 36 tes berarti seluruh alur sudah tuntas.

Pekerjaan kali ini adalah audit dan reproduksi, bukan perbaikan perilaku bisnis. Tidak ada commit, push, deploy, akun bawaan, atau endpoint login lokal baru. Aplikasi disetel `APP_ENV=local`, `APP_DEBUG=false`; MySQL tetap dipakai. Autentikasi skenario HTTP memakai sesi pengguna sementara dalam database `lppm_test`, bukan akun pada database aplikasi. Halaman dan konfigurasi SSO awal dipertahankan.

## Sumber dan cakupan

Sumber: `/home/arfaj/Downloads/SOP_LPPM_REVIEW 2026.pdf`, SOP 004-UMB-LPPM, tanggal dokumen 1 Februari 2026. Nomor halaman adalah urutan fisik PDF. Dokumen diperlakukan sebagai spesifikasi alur, bukan instruksi menjalankan tindakan di luar permintaan pengguna.

Diperiksa ulang: SOP 4, 5, 6, 7, 9, 10, 11, 18, 35, 38, 39, 40; teks tabel, keluaran, role, tenggat, dan diagram terkait. Diagram SOP 6 dan 18 dirender dan diperiksa visual. Pemetaan terperinci 52 definisi langkah serta kegiatan sistem ada di `doc/audit_hibah_sop.md`. Pemetaan itu menunjukkan keberadaan implementasi, bukan bukti semua jalur bebas cacat.

SOP umum lain seperti perencanaan RIP/roadmap/panduan (1-3), etik penelitian (20), publikasi/HKI, layanan persuratan dan KKN bukan otomatis tercakup hanya karena terhubung dengan kegiatan penelitian. Audit ini tidak menyatakan seluruh 40 SOP LPPM sudah diimplementasikan. Modul-modul pendukung tersebut perlu cakupan terpisah bila diminta menjadi bagian aplikasi.

## Bukti pengujian

| Pemeriksaan | Hasil |
|---|---|
| Tes dasar, putaran pertama | 36 lulus, 639 assertion |
| Tes dasar, putaran kedua | 36 lulus, 639 assertion |
| Render navigasi yang diizinkan dan halaman reviewer untuk 11 role | 1 tes lulus, 82 assertion |
| Reproduksi temuan tambahan | 8 gagal pada kriteria yang diharapkan, 56 assertion pada putaran terakhir |
| Pengulangan temuan | Delapan skenario diulang; hasil masalah konsisten. Kasus SK diperkuat dengan penetapan eksternal yang sah lalu berkas SK dihapus. Kasus arsip diperkuat dengan JPEG nyata melalui endpoint upload |
| Build Vite | Berhasil, 61 modul |
| Kompilasi Blade | Berhasil |
| Halaman login lokal | HTTP 200, login SSO tetap tersedia |
| npm audit | 0 advisory |
| Composer seluruh dependency | 50 advisory pada 12 paket |
| Composer tanpa dependency pengembangan | 45 advisory pada 11 paket |
| Data database aplikasi sesudah audit | 0 akun, 0 proposal, 0 periode; fixture tidak masuk database aplikasi |

Pengujian render HTTP bukan pengujian interaksi visual browser menyeluruh, aksesibilitas, beban, ataupun seluruh kombinasi input. SSO pada tes dipalsukan di batas jaringan; belum ada autentikasi sungguhan ke institusi. Tidak ada klaim bebas bug absolut.

## Temuan dan lokasi

### H01 - Tinggi: Kaprodi dapat membaca hibah fakultas lain

- Lokasi: `app/Http/Controllers/Concerns/AuthorizesLppmProposalAccess.php:34`.
- Reproduksi: buat dua pengguna dengan nama prodi sama tetapi fakultas berbeda; login sesi Kaprodi dan buka detail hibah pengusul dari fakultas lain.
- Diharapkan: 403. Aktual: 200. Pembatasan query memeriksa nama prodi saja, sementara pemeriksaan tindakan SOP juga mensyaratkan fakultas.
- Dampak: isi proposal dan dokumen yang mengikuti visibilitas proposal dapat terbuka di luar lingkup fakultas. Tidak perlu menjalankan tindakan persetujuan untuk terjadi kebocoran baca.
- Acuan: konsistensi lingkup fakultas/prodi SOP 38.3 dan pemisahan akses aplikasi.
- Tindak lanjut: gunakan identitas prodi yang unik atau cocokkan fakultas sekaligus prodi pada seluruh query visibilitas dan ekspor.

### H02 - Tinggi: Role gabungan Dekan + Reviewer kehilangan akses fakultas

- Lokasi: `app/Http/Controllers/Concerns/AuthorizesLppmProposalAccess.php:26`.
- Reproduksi: Dekan juga mempunyai role reviewer, lalu membuka hibah dosen fakultas sendiri tanpa penugasan reviewer pada hibah itu.
- Diharapkan: 200 atas kewenangan Dekan. Aktual: 403, karena cabang reviewer mengembalikan query sebelum lingkup Dekan diperhitungkan.
- Dampak: pengesahan dan rekomendasi fakultas dapat macet untuk pengguna dengan beberapa jabatan.
- Acuan: SOP 5.2 dan 38.3.
- Tindak lanjut: gabungkan lingkup semua kewenangan yang sah, tetap mempertahankan pembatasan fakultas/prodi dan larangan menilai diri sendiri.

### H03 - Sedang: Antrean Review belum terhubung dengan tugas hibah

- Lokasi: `app/Http/Controllers/ReviewController.php:16`; `resources/views/reviews/index.blade.php`.
- Reproduksi: terdapat tugas reviewer hibah dalam `grant_records`, reviewer membuka menu Review.
- Diharapkan: tugas tersebut terlihat dan dapat ditindaklanjuti. Aktual: judul proposal tidak muncul; query hanya memakai tabel `reviews` lama.
- Dampak: tugas hibah dapat dikerjakan dari detail proposal, tetapi antrean kerja pada menu Review tidak lengkap. Ini tidak berarti mesin penilaian hibah sama sekali tidak tersedia.
- Acuan: penugasan/penilaian proposal, SOP 11.2-11.3 dan 9.3.
- Tindak lanjut: satukan atau tampilkan antrean tugas proposal, monev dan seminar beserta tautan, status serta putaran aktif.

### H04 - Sedang: Penguncian proposal saat review hanya berlaku untuk pengusul

- Lokasi: `app/Services/GrantWorkflow.php:93` dan `:115`; endpoint `GrantController::upload`.
- Reproduksi: admin LPPM mengunggah `document_type=proposal` saat `grant_stage=review`.
- Diharapkan pada audit integritas versi: perubahan konten pengajuan diarahkan melalui revisi. Aktual: unggahan baru diterima tanpa error dan versi baru tampil dalam daftar dokumen.
- Dampak: ada versi yang diunggah ketika pemeriksaan berlangsung tanpa pengesahan/pengajuan ulang. Berkas lama tidak ditimpa dan upload tercatat, tetapi penilaian belum terikat secara eksplisit ke snapshot versi dokumen yang dinilai.
- Acuan: hubungan pengesahan dan unggahan pada SOP 5.2-5.3 serta integritas review.
- Tindak lanjut: tentukan kebijakan koreksi oleh admin secara eksplisit; kunci konten atau ikat penilaian pada versi pengajuan dan wajibkan putaran baru untuk perubahan substantif.

### H05 - Sedang: Form penilaian putaran lama masih ditampilkan

- Lokasi: `resources/views/grants/show.blade.php:72`.
- Reproduksi: proposal direvisi lalu dikirim ulang ke review; reviewer membuka detail proposal.
- Diharapkan: form input hanya untuk tugas pada putaran aktif. Aktual: input `assignment_id` tugas sebelum `resubmit` masih dirender.
- Dampak: reviewer dapat mengisi form yang kemudian ditolak server karena tugas lama. Validasi backend putaran sudah bekerja; masalahnya adalah form yang tidak dapat diselesaikan tetap ditawarkan.
- Tindak lanjut: gunakan batas siklus yang sama di query tampilan, status tugas, dan validasi backend.

### H06 - Sedang: Arsip non-PDF lolos melalui pilihan dokumen yang sudah ada

- Lokasi: `app/Services/GrantSop.php:206-220`; `app/Services/GrantWorkflow.php:108`; SOP 40.3.
- Reproduksi: unggah JPEG nyata sebagai `archive_scan` melalui unggahan dokumen hibah; pilih dokumen itu pada `existing_documents[archive_scan]` di langkah 40.3.
- Diharapkan: ditolak karena keluaran SOP adalah PDF terindeks. Aktual: langkah tersimpan tanpa error; cabang dokumen lama menjalankan `continue` sebelum validasi PDF.
- Dampak: langkah digitalisasi dinyatakan lengkap dengan format yang tidak memenuhi keluaran SOP 40.3.
- Tindak lanjut: samakan aturan MIME untuk upload langsung dan penggunaan ulang dokumen; batasi tipe `archive_scan` pada endpoint unggahan umum juga.

### H07 - Tinggi: Reviewer eksternal tetap dianggap siap ketika berkas SK hilang

- Lokasi: `app/Models/ReviewerAppointment.php:16-22`.
- Reproduksi: jalankan permohonan dan rekomendasi eksternal, tetapkan SK melalui SOP 7.4, kemudian hilangkan berkas SK dari penyimpanan.
- Diharapkan: `readyForAssignment()` menjadi false. Aktual: true; cabang external cukup memeriksa status dan masa berlaku.
- Dampak: penugasan dan pemeriksaan kesiapan penilaian dapat lolos meskipun bukti penetapan eksternal tidak lagi tersedia. Tidak diperlukan manipulasi status palsu untuk reproduksi terakhir.
- Acuan: SOP 7.4.
- Tindak lanjut: validasi kembali langkah penetapan, rantai rekomendasi, keberadaan berkas SK dan kelengkapan portofolio sesuai jalur internal/eksternal saat penugasan.

### H08 - Kekurangan navigasi: Halaman Hibah Internal belum menampilkan daftar hibah berjalan

- Lokasi: `app/Http/Controllers/GrantController.php:26`; `resources/views/grants/index.blade.php:5-12`.
- Reproduksi: pengguna memiliki hibah aktif lalu membuka `/grants`.
- Aktual: hanya periode/form pembukaan yang tersedia; judul hibah pengguna tidak muncul.
- Dampak: pengguna harus melalui menu Proposal untuk menemukan kegiatan. Daftar Proposal tersedia, jadi ini kekurangan pusat tindak lanjut, bukan kehilangan data atau ketidaktersediaan seluruh alur.
- Tindak lanjut: tampilkan daftar hibah sesuai lingkup, tahap, tugas berikutnya dan tautan detail; atau sediakan tautan yang jelas ke daftar Proposal dengan filter hibah.

### H09 - Keamanan dependency PHP: advisory belum dibereskan

- Lokasi: `composer.lock`; bukti `output/audit-hibah-2026-11-20/composer-audit.json` dan `composer-runtime-audit.json`.
- Seluruh dependency: 50 advisory pada 12 paket; kategori pemindai 1 critical, 14 high, 21 medium, 7 low, 7 tanpa severity.
- Runtime: 45 advisory pada 11 paket. Lima advisory tambahan berasal dari `symfony/yaml` pada dependency pengembangan.
- Contoh: `laravel/framework` 10.50.2 terkena advisory `PKSA-b41t-wbzk-9q51` / CVE-2026-114509 (TokenGuard, severity critical menurut pemindai). Aplikasi saat ini memakai guard web/session dan Sanctum; eksploitasi TokenGuard pada aplikasi ini belum dibuktikan. Advisory paket tidak otomatis berarti semua jalur rentan tersebut digunakan aplikasi.
- Paket runtime terdampak: guzzlehttp/guzzle, guzzlehttp/psr7, laravel/framework, league/commonmark, league/flysystem, symfony/http-foundation, symfony/http-kernel, symfony/mailer, symfony/mime, symfony/polyfill-intl-idn, symfony/routing.
- npm audit tetap 0. Pernyataan terdahulu tentang npm bersih tidak mencakup dependency PHP.
- Tindak lanjut: susun pembaruan PHP dependency yang kompatibel, tinjau advisory per penggunaan, lalu ulangi seluruh pengujian dan audit. Paket belum diubah dalam audit ini.

## Status fitur terhadap SOP hibah

| Kelompok SOP | Ada dan teruji | Yang masih menghalangi klaim selesai |
|---|---|---|
| 4 dan 5: periode, sosialisasi, pengesahan, unggah, registrasi | Form, bukti, pembatasan periode, urutan tahap tersedia | H01, H02, H04; konsistensi versi pengajuan harus diperbaiki |
| 6, 7, 35: rekrutmen dan penetapan reviewer | Jalur internal/eksternal, portofolio, rekomendasi, seleksi, SK, publikasi, bukti penyampaian | H02, H07; provisioning permission reviewer di SSO masih memerlukan verifikasi |
| 18: SK hibah, kontrak, 70%, pelaksanaan, kemajuan, monev, 30%, final | Alur utama sampai selesai lulus, nominal 70/30 dijumlahkan tepat 100% | H03-H05 dan H07 memengaruhi keandalan proses review; tidak ada eksekusi transfer bank otomatis |
| 9 dan 11: seminar dan monev | Jadwal, tugas, borang, rekomendasi dan tindak lanjut tersedia | H03, H05, H07 |
| 10: laporan | Laporan terstruktur, bukti unggah, pemeriksaan fisik, tanda terima tersedia | Integritas versi terkait H04; validitas dokumen sungguhan masih pemeriksaan petugas |
| 38 dan 39: koordinasi, rekap, tahunan, sinkronisasi | Rekap CSV sesuai lingkup, bukti sinkronisasi, pengesahan dan distribusi tersedia | H01 pada lingkup; konektor SINTA/SISTER/PDDIKTI belum ada dan belum diuji |
| 40: arsip dan retensi | Pemeriksaan, klasifikasi, scan, lokasi fisik, inventaris, catatan retensi tersedia | H06; aplikasi mencatat bukti tindakan fisik, tidak melaksanakan pemusnahan fisik |

## Integrasi yang belum dapat dinyatakan selesai

1. **SSO dan permission hibah**: pengguna dengan `sso_permissions` LPPM yang tidak kosong memakai daftar SSO sebagai otoritas; fallback role tidak digabung. Permission baru `lppm.grant.*` harus tersedia pada SSO. Tes lokal menggunakan role tanpa daftar permission SSO; hasil itu tidak membuktikan akun institusi sudah mendapat akses baru.
2. **Calon reviewer dosen**: penetapan reviewer tidak otomatis mengubah role/permission pada akun. Kandidat yang tetap hanya memiliki role dosen belum punya `lppm.review.score`. Perlu alur provisioning resmi dan verifikasi; tidak boleh sekadar membuka hak menilai bagi semua dosen.
3. **Sistem nasional**: SOP 38.6 dan 39.4 saat ini mempunyai pencatatan bukti proses, bukan konektor API otomatis. Jika targetnya sinkronisasi langsung dari aplikasi, fitur itu belum selesai.
4. **Scheduler pengingat**: command dan jadwal Laravel ada serta idempotensi dites; layanan scheduler sistem yang terus berjalan belum diverifikasi. Pengingat tidak dapat dianggap aktif hanya karena definisi jadwal tersedia.
5. **Pengesahan, transfer, dan arsip fisik**: aplikasi memeriksa kelengkapan bukti; keabsahan tanda tangan, transfer bank nyata, surat ke instansi dan tindakan arsip tetap proses operasional di luar aplikasi.

## Cara mengulang

Jalankan hanya dengan `.env.testing` dan akun `lppm_test` yang terpisah. Bootstrap tes menolak database/akun aplikasi. Tidak perlu membuat akun demo atau mengubah login produk.

```bash
php artisan test
php artisan test tmp/audit/GrantRoleSmokeTest.php
php artisan test tmp/audit/GrantDeepAuditTest.php
npm run build
php artisan view:cache
npm audit --json
composer audit --format=json
composer audit --no-dev --format=json
```

Tes audit tambahan sengaja berada di luar suite rutin agar laporan lama tidak berubah diam-diam: perintah audit eksplisit menghasilkan 8 kegagalan sampai temuannya ditangani. Kegagalan adalah perbedaan aktual versus kriteria audit, bukan error koneksi database. Kode reproduksi serta hasil mentah disimpan lokal.

## Urutan penanganan yang disarankan

1. H01, H02 dan H07: tutup masalah lingkup akses dan kesiapan reviewer.
2. H09: atasi dependency PHP dengan pengujian kompatibilitas, sambil mempertahankan mode lokal.
3. H04, H06 dan H05: perkuat integritas versi, validasi arsip serta putaran penilaian.
4. H03 dan H08: lengkapi antrean kerja dan navigasi.
5. Verifikasi provisioning SSO, batas integrasi nasional, scheduler, lalu ulangi UAT lokal penelitian dan PkM beserta jalur revisi/penolakan.

Belum ada temuan di atas yang dinyatakan diperbaiki pada pekerjaan audit ini. Tidak dipush dan tidak dinaikkan ke produksi.
