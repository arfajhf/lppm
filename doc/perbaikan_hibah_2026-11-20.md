# Hasil perbaikan audit hibah - 20 November 2026

> **Koreksi versi:** atas instruksi pengguna, composer.json, composer.lock dan migrasi reviews dikembalikan persis ke versi awal proyek: Laravel 10.50.2 / Sanctum 3.3.3 / Carbon 2.73.0 / PHPUnit 10.5.63. Perbaikan H01-H08 dipertahankan. Catatan pembaruan Laravel 12 dan nol advisory pada bagian berikut adalah riwayat yang sudah dibatalkan; H09 tidak boleh dinyatakan selesai pada dependency asli. **Tes ulang Laravel 10.50.2: 52 tes lulus, 1.045 assertion**, termasuk dua alur hibah penuh. Bukti: `output/audit-hibah-2026-11-20/pemulihan-laravel10/tes-lengkap.txt`.

Semua temuan H01-H09 pada audit lokal sudah ditangani. Delapan skenario yang sebelumnya gagal kini masuk suite rutin dan lulus, disertai tambahan pengujian batas akses, putaran review, format arsip, permission SSO dan dua jenis hibah. Hasil ini berlaku untuk perilaku aplikasi yang diuji di lokal; tidak mengklaim seluruh integrasi institusi sudah berjalan.

## Perbaikan

| ID | Perubahan | Verifikasi |
|---|---|---|
| H01 | Lingkup Kaprodi memeriksa fakultas dan prodi sekaligus | Fakultas lain dengan nama prodi sama ditolak; lingkup sendiri diterima |
| H02 | Lingkup semua role digabung dalam kelompok query yang tetap dibatasi | Dekan + reviewer dan Dekan + Kaprodi memperoleh akses fakultas yang sah |
| H03 | Menu Review menampilkan tugas hibah proposal, monev dan seminar dari grant_records, status, jadwal dan tautan detail | Tugas sendiri muncul; tugas reviewer lain dan putaran lama tidak muncul |
| H04 | Admin tidak dapat mengunggah proposal baru saat pengesahan, validasi atau review aktif; koreksi harus melalui revisi | Unggahan admin saat review ditolak; alur revisi tetap lulus |
| H05 | Form penilaian hanya memakai assignment setelah resubmit terakhir untuk tahap aktif | Form assignment lama tidak dirender; backend tetap memeriksa putaran |
| H06 | Archive scan wajib PDF pada endpoint upload dan pada pemakaian dokumen lama; MIME berkas lama diperiksa dari penyimpanan | JPEG nyata ditolak di kedua jalur; PDF lama yang valid diterima |
| H07 | Kesiapan reviewer memeriksa langkah penetapan, rantai bukti, portofolio dan keberadaan SK, termasuk jalur eksternal | Jalur eksternal lengkap siap; setelah SK dihapus menjadi tidak siap |
| H08 | Halaman Hibah Internal menampilkan daftar hibah sesuai scope dengan pagination, pengusul, tahap dan tautan detail | Hibah pengguna terlihat di pusat tindak lanjut |
| H09 | Dependency PHP diperbarui dengan lockfile baru tanpa menonaktifkan audit keamanan | Composer audit 0 advisory, npm audit 0 advisory, composer validate --strict berhasil |

## Kompatibilitas framework

Laravel 10.50.2 diperbarui ke 12.73.0, Sanctum 4.5.0, Carbon 3.15.1, PHPUnit 11.5.58, serta dependency transitif yang telah ditambal. Composer menyatakan PHP minimal 8.2. Struktur bootstrap/kernel aplikasi dipertahankan.

Migrasi pembuatan tabel reviews disesuaikan dari unsignedDecimal yang sudah dihapus menjadi decimal()->unsigned(), mempertahankan definisi kolom. Perhitungan durasi penerimaan dicast ke integer agar cocok dengan hasil Carbon 3. Kedua jadwal normal (30 hari hibah dan 14 hari reviewer) diuji melalui HTTP.

Tidak ada perubahan login, logout, endpoint, state-validation yang sudah ada, konfigurasi SSO, maupun kredensial SSO pada pekerjaan perbaikan ini. Tes autentikasi SSO dan penolakan state yang salah lulus. Tidak ada privilege SSO yang ditambahkan secara diam-diam.

Panduan kompatibilitas yang diperiksa: [Laravel 12 upgrade guide](https://laravel.com/docs/12.x/upgrade). Versi paket dan persyaratan aktual tersimpan di composer.json/composer.lock.

## Hasil tes akhir

- **52 tes lulus, 1.045 assertion**, seluruhnya pada database MySQL pengujian khusus lppm_test. Suite hasil perbaikan sebelumnya juga lulus 45/779 dan 51/1042 sebelum cakupan tambahan dimasukkan.
- Alur lengkap **Penelitian Hibah** dan **Pengabdian Hibah**: pengajuan/pengesahan, registrasi/roadmap, reviewer/borang, SK penerima, kontrak, 70%, pelaksanaan/logbook, kemajuan, monev, 30%, laporan akhir/keuangan/luaran, seminar, perbaikan, tanda terima dan arsip.
- Pengujian tambahan meliputi revisi, larangan persetujuan sendiri, berkas privat, jadwal, retensi, tindak lanjut tahunan, reviewer internal/eksternal, keamanan API, dan render halaman sesuai permission pada 11 role.
- Build Vite dan kompilasi Blade berhasil. Login lokal HTTP 200 dan tombol SSO tersedia, tanpa form login alternatif.
- Semua migrasi aplikasi berstatus Ran. Migrasi bersih diverifikasi melalui suite MySQL; database operasional tidak di-reset.
- Konfigurasi scheduler terdaftar untuk grants:remind pukul 08.00 Asia/Jakarta. Command dan idempotensi diuji; worker terus-menerus tidak dinyalakan oleh pekerjaan ini.
- Kondisi aplikasi setelah tes: APP_ENV=local, APP_DEBUG=false, driver mysql; **0 akun, 0 proposal, 0 periode** pada database operasional. Semua fixture hanya dibuat dalam transaksi pengujian.
- Tidak ada akun demo, seeder data contoh, endpoint bypass login, commit, push atau deployment.

Hasil mentah ada di output/audit-hibah-2026-11-20/perbaikan/. Hasil sebelum perbaikan tetap disimpan pada folder induk sebagai bukti audit historis.

## Batas proses luar aplikasi

1. Permission dari SSO institusi tetap authoritative. Hak lppm.grant.* dan lppm.review.score perlu diprovisikan oleh pengelola SSO sesuai kewenangan. Halaman reviewer sekarang menampilkan pesan jelas jika penetapan tersedia tetapi hak penilaian belum aktif; aplikasi tidak mengubah role server SSO. Skenario permission parsial juga diuji agar fallback tidak menaikkan privilege.
2. Pencatatan sinkronisasi SINTA/SISTER/PDDIKTI mengikuti langkah SOP yang sudah tersedia. Tidak ada konektor API baru tanpa spesifikasi/kredensial, dan tidak ada komunikasi ke sistem nasional pada pekerjaan lokal ini.
3. Keabsahan tanda tangan, bukti transfer bank, pengiriman surat luar dan arsip fisik tetap diverifikasi petugas. Aplikasi mencatat dokumen dan audit trail, bukan melaksanakan tindakan tersebut.
4. Autentikasi institusi langsung, uji beban dan pengujian visual browser menyeluruh belum dilakukan. Kelulusan tes bukan jaminan bebas seluruh bug pada setiap kondisi.

## Mengulang pemeriksaan

```bash
composer install
php artisan test
npm run build
php artisan view:cache
composer audit
npm audit
php artisan schedule:list
```

Kredensial pengujian tetap berada di .env.testing yang diabaikan Git; bootstrap tes menolak database/akun selain lppm_test. Jangan menjalankan migrate:fresh pada database operasional. Untuk pengingat lokal berkelanjutan, jalankan php artisan schedule:work pada terminal tersendiri.
