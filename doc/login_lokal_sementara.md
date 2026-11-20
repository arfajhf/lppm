# Login lokal sementara untuk audit hibah

Buka http://localhost:8001/login (atau http://127.0.0.1:8000/login jika operator memakai port 8000; gunakan satu alamat secara konsisten) dan gunakan form **Login lokal sementara**. Akun utama: **admin@example.ex**, nama admin, role Admin LPPM. Password semua akun tersedia pada berkas privat lokal `storage/app/local-auth/DAFTAR_AKUN_LOKAL.csv` (tidak masuk Git). Setiap akun memakai password berbeda. Tidak ada pilihan role bebas pada halaman login.

Akun utama sebelumnya diganti identitasnya menjadi **admin** / **admin@example.ex**. UUID, role dan password tetap; login menggunakan email lama tidak lagi berlaku. Berkas kredensial privat sudah diperbarui.

Instalasi pada komputer baru: [panduan dari GitHub](instalasi_dari_github.md).

## Akun yang diminta

| Nama | Email | Role | Fakultas | Prodi |
|---|---|---|---|---|
| admin | admin@example.ex | lppm_admin | LPPM | Administrasi LPPM |
| Kepala LPPM | kepala.lppm@example.ex | kepala_lppm | LPPM | Administrasi LPPM |
| Sekretaris LPPM | sekretaris.lppm@example.ex | sekretaris_lppm | LPPM | Administrasi LPPM |
| Keuangan LPPM | keuangan@example.ex | keuangan | LPPM | Keuangan |
| Rektor | rektor@example.ex | rektor | Rektorat | - |
| Wakil Rektor | wakil.rektor@example.ex | wakil_rektor | Rektorat | - |
| Reviewer Teknik | reviewer.teknik@example.ex | reviewer | Fakultas Teknik | Teknik Informatika |
| Reviewer Ekonomi | reviewer.ekonomi@example.ex | reviewer | Fakultas Ekonomi | Manajemen |
| Dekan Fakultas Teknik | dekan.teknik@example.ex | dekan | Fakultas Teknik | - |
| Dekan Fakultas Ekonomi | dekan.ekonomi@example.ex | dekan | Fakultas Ekonomi | - |
| Kaprodi Teknik Informatika | kaprodi.informatika@example.ex | kaprodi | Fakultas Teknik | Teknik Informatika |
| Kaprodi Teknik Sipil | kaprodi.sipil@example.ex | kaprodi | Fakultas Teknik | Teknik Sipil |
| Kaprodi Manajemen | kaprodi.manajemen@example.ex | kaprodi | Fakultas Ekonomi | Manajemen |
| Kaprodi Akuntansi | kaprodi.akuntansi@example.ex | kaprodi | Fakultas Ekonomi | Akuntansi |
| Dosen Informatika 1 | dosen.informatika1@example.ex | dosen | Fakultas Teknik | Teknik Informatika |
| Dosen Informatika 2 | dosen.informatika2@example.ex | dosen | Fakultas Teknik | Teknik Informatika |
| Dosen Teknik Sipil | dosen.sipil@example.ex | dosen | Fakultas Teknik | Teknik Sipil |
| Dosen Manajemen 1 | dosen.manajemen1@example.ex | dosen | Fakultas Ekonomi | Manajemen |
| Dosen Manajemen 2 | dosen.manajemen2@example.ex | dosen | Fakultas Ekonomi | Manajemen |
| Dosen Akuntansi | dosen.akuntansi@example.ex | dosen | Fakultas Ekonomi | Akuntansi |
| Mahasiswa Informatika | mahasiswa.informatika@example.ex | mahasiswa | Fakultas Teknik | Teknik Informatika |
| Mahasiswa Manajemen | mahasiswa.manajemen@example.ex | mahasiswa | Fakultas Ekonomi | Manajemen |

Panduan alur dan dokumen lengkap: [panduan pengguna hibah](panduan_pengguna_hibah.md). Hasil pemeriksaan dicatat pada [checklist QA kampus](checklist_qa_hibah.md). Identitas dan password akun lokal tidak terbentuk otomatis dari clone GitHub.

## Urutan audit manual

1. Masuk sebagai admin/Admin LPPM untuk menyiapkan periode penerimaan hibah dan reviewer. Kepala LPPM serta Sekretaris melengkapi langkah SOP sesuai kewenangannya. Untuk audit persiapan dari nol, buat draft periode baru. Periode **Hibah Internal 2026 - Periode November** sudah dibuka khusus lokal (20 November-19 Desember 2026) atas permintaan pengguna; itu tidak menyatakan bukti persiapan resmi sudah lengkap. Pengguna juga sudah membuat proposal sendiri.
2. Daftarkan reviewer, jalankan permohonan/rekomendasi melalui Dekan yang fakultasnya cocok (atau jalur eksternal), penetapan melalui Rektor, lalu lengkapi publikasi dan bukti penyampaian sesuai SOP. Akun reviewer belum otomatis memiliki SK; hak login tidak menggantikan bukti penetapan.
3. Masuk sebagai Dosen Informatika 1, buat proposal dan tim, lalu keluar. Dosen Informatika 2 dapat digunakan untuk memeriksa privasi antar-pengusul yang satu prodi.
4. Masuk sebagai Dekan Fakultas Teknik untuk pengesahan. Kaprodi Teknik Informatika dan Dekan Teknik dapat memeriksa roadmap; Kaprodi Sipil dan Dekan Ekonomi dapat dipakai untuk memeriksa pembatasan lingkup.
5. Lanjutkan sebagai Admin LPPM untuk validasi/penugasan, Reviewer Teknik untuk penilaian, Rektor untuk SK hibah, Keuangan untuk pencatatan 70/30, dan Kepala LPPM untuk inventaris/arsip. Kembali ke akun dosen pada tahap unggah, logbook, laporan dan tindak lanjut.
6. Ulangi alur dengan dosen Ekonomi untuk memastikan fakultas/prodi berbeda tetap terpisah. Mahasiswa hanya melihat kegiatan jika sudah menjadi anggota tim.

Selalu **Keluar** sebelum mengganti akun. Logout akun lokal kembali ke halaman login lokal; logout sesi SSO tetap menggunakan jalur SSO semula.

## Pembatasan dan pengelolaan

Untuk instalasi baru, operator menyiapkan `.env` dengan `APP_ENV=local` dan `LOCAL_AUTH_ENABLED=true`, menjalankan `php artisan config:clear`, lalu membuat akun pribadi pertama memakai `auth:local-user ... --role=lppm_admin`. Langkah lengkap ada di [README](../README.md#login-lokal-sementara). Tidak perlu dan tidak tersedia seeder akun massal bawaan.

- Framework tetap Laravel 10.50.2; composer.json/composer.lock tidak diperbarui.
- Login lokal membutuhkan APP_ENV=local, LOCAL_AUTH_ENABLED=true dan asal loopback (127.0.0.1 atau ::1). Di production atau akses remote, form disembunyikan dan endpoint ditolak.
- Password diperiksa menggunakan autentikasi Laravel; CSRF, regenerasi sesi dan pembatasan percobaan aktif. Role dan permission dibaca dari akun tersimpan, bukan dari form.
- Untuk menonaktifkan: set LOCAL_AUTH_ENABLED=false lalu jalankan php artisan config:clear. Sesi lokal juga ditutup pada permintaan berikutnya jika flag/lingkungan/asal akses tidak memenuhi batasan.
- Untuk membuat akun bernama lain dengan password yang ditentukan sendiri: jalankan perintah di bawah. Jangan mengirim password lewat chat atau argumen shell.

```bash
php artisan auth:local-user email@example.ex --name="Nama pengguna" --role=dosen --faculty="Fakultas Teknik" --program="Teknik Informatika"
```

Password diminta secara interaktif dua kali (minimal 12 karakter). Opsi --generate-password membuat password acak dan menulis berkas privat di storage/app/local-auth/. Akun existing ditolak agar tidak tertimpa atau berubah role tanpa disengaja.

Tiga temuan kasus tambahan pada doc/audit_ulang_laravel10.md dan advisory dependency PHP tetap merupakan backlog audit; penambahan login lokal ini tidak mengubah framework maupun menandai temuan tersebut selesai.
