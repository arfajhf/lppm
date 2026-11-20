# Audit ulang Laravel 10 - 20 November 2026

Framework dan dependency tidak diubah. composer.json dan composer.lock identik dengan versi awal: Laravel 10.50.2, Sanctum 3.3.3, Carbon 2.73.0, PHPUnit 10.5.63. Audit ini tidak mengubah login SSO dan tidak membuat akun pada database operasional. Tidak ada push.

## Hasil

- Suite rutin: **52 tes lulus, 1.045 assertion**. Perbaikan H01-H08 sebelumnya tetap lolos regresi, termasuk dua alur hibah lengkap dan render halaman 11 role.
- Pengujian kasus tambahan: **3 gagal** pada kriteria yang diharapkan. Ini temuan baru di luar cakupan suite rutin sebelumnya.
- Composer validate --strict berhasil. npm audit: 0 advisory. Composer audit: 50 advisory pada 12 paket pada dependency asli. H09 tetap belum selesai setelah pembaruan framework dibatalkan; tidak ada update paket pada audit ini.
- Status migrasi diperiksa tanpa reset database aplikasi. Halaman login lokal HTTP 200.

## Temuan baru

| ID / prioritas | Lokasi | Reproduksi dan dampak |
|---|---|---|
| A01 / sedang | database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php:16 | User menggunakan UUID, tetapi morphs(tokenable) membuat tokenable_id bertipe integer. Panggilan createToken() gagal SQL 1366 Incorrect integer value. Autentikasi API berbasis token tidak dapat disiapkan untuk akun UUID. Login web SSO memakai sesi dan tetap lulus. |
| A02 / tinggi | app/Http/Controllers/Concerns/AuthorizesLppmProposalAccess.php:29 | Dosen dengan permission eksplisit SSO lppm.proposal.view, lppm.review.view, lppm.review.score sudah ditugaskan sebagai reviewer tetapi tanpa nama role reviewer. Detail hibah menghasilkan 403 karena query visibilitas memeriksa nama role, sedangkan endpoint penugasan memeriksa permission score. Pemberian permission yang dijelaskan pada panduan belum cukup untuk skenario ini. |
| A03 / sedang | app/Console/Commands/RemindGrantDeadlines.php:60 | Tugas penilaian lama memiliki tenggat, kemudian proposal dikirim ulang ke review. Command grants:remind tetap mengirim satu pengingat untuk tugas putaran lama. Form dan antrean sudah menyaring putaran, tetapi pengingat belum mengikuti batas siklus yang sama. |

## Saran perbaikan

1. A02: konsistenkan visibilitas reviewer dengan permission penilaian dan penugasan yang sah, tanpa memberikan akses ke proposal yang tidak ditugaskan.
2. A01: sesuaikan tokenable_id dengan UUID melalui migrasi kompatibel yang mempertimbangkan data token existing. Jangan mengubah tipe ID seluruh aplikasi atau login SSO.
3. A03: batasi pengingat pada jenis penilaian, tahap, dan siklus penugasan aktif yang sama dengan form dan antrean.
4. Dependency PHP: bahas opsi yang tetap sesuai batas Laravel 10 sebelum mengubah versi apa pun. Audit ini tidak melakukan pembaruan.

## Bukti dan cara mengulang

```bash
php artisan test
php artisan test tests/Audit/GrantEdgeAuditTest.php
composer audit --format=json
npm audit --json
```

Bukti mentah: output/audit-ulang-laravel10/. Ketiga skenario tambahan memakai database MySQL pengujian lppm_test dengan rollback transaksi, bukan data operasional. Akun fixture hanya ada selama tes, bukan akun demo aplikasi.

Temuan baru belum diperbaiki pada audit ini. Hasil ini tidak menyatakan seluruh fitur bebas bug. SSO institusi sungguhan, integrasi nasional dan uji visual browser menyeluruh belum diverifikasi. Permintaan menampilkan aplikasi dikirim ke panel browser Codex pada http://127.0.0.1:8000/grants; tool UI mengembalikan status queued, sehingga tampilan yang sudah terbuka belum bisa dikonfirmasi oleh audit ini.

Tes reproduksi A01-A03 kini disertakan pada `tests/Audit/GrantEdgeAuditTest.php` di luar suite rutin; jalankan eksplisit untuk memeriksa temuan yang belum diperbaiki. Hasil mentah pada `output/` merupakan artefak lokal yang tidak disertakan ke GitHub.
