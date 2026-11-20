# Checklist QA kampus: hibah internal LPPM

Diperbarui 20 November 2026. Gunakan bersama [panduan pengguna](panduan_pengguna_hibah.md), [akun lokal](login_lokal_sementara.md) dan [daftar dokumen](daftar_dokumen_hibah.md). Ini lembar pemeriksaan, bukan hasil UAT yang sudah disetujui.

## Data sesi QA

| Data | Diisi QA |
|---|---|
| Nama pemeriksa dan tanggal | |
| Browser, versi, OS, ukuran layar | |
| Alamat aplikasi/port dan versi/build/commit bila tersedia | |
| Jenis: Penelitian Hibah / Pengabdian Hibah | |
| ID/judul proposal dan periode | |
| Akun, role, fakultas/prodi tiap pelaksana | |
| Hasil akhir: lulus / gagal / terblokir / belum diuji | |

Gunakan satu alamat/port selama sesi. Password tidak ditulis pada lembar QA, screenshot, laporan bug atau issue GitHub. Periode November yang sudah terbuka boleh dipakai untuk menguji proposal; gunakan draft periode baru untuk memeriksa langkah persiapan dan publikasi.

## A. Akses, akun dan tampilan

- [ ] Login lokal dengan kredensial benar; dashboard dan nama/role sesuai akun.
- [ ] Password salah ditolak; tidak ada pilihan role bebas pada login.
- [ ] Keluar kembali ke login lokal; akun berikutnya memperoleh role/lingkupnya sendiri.
- [ ] Akun tanpa lingkup tidak dapat melihat proposal/dokumen pihak lain melalui daftar maupun URL langsung.
- [ ] Kaprodi hanya pada fakultas+prodi yang sesuai; Dekan hanya pada fakultas yang sesuai.
- [ ] Mahasiswa yang bukan anggota tidak dapat melihat kegiatan; setelah ditambahkan, akses sesuai keanggotaan dan tidak dapat mengambil keputusan pejabat.
- [ ] Reviewer tidak ditugaskan tidak dapat membuka pengajuan pihak lain.
- [ ] Pada layar 320/390px, 768px dan desktop: tidak ada scroll horizontal halaman, tombol/label tidak bertumpuk, teks panjang membungkus.
- [ ] Menu navigasi HP dapat dibuka/tutup; keyboard dapat mengoperasikan menu dan tombol. Desktop menampilkan sidebar.
- [ ] Form file, pilihan anggota, nama panjang dan pagination tetap dapat dipakai pada layar kecil.
- [ ] Pengujian SSO institusi dicatat terpisah sebagai belum diuji bila layanan/kredensial belum siap; jangan menganggap login lokal membuktikan SSO lulus.

Uji perangkat fisik dicatat tersendiri. Mengubah viewport Chrome bukan bukti bahwa Safari/iOS atau seluruh perangkat fisik sudah lulus.

## B. Periode dan reviewer

- [ ] Admin membuat draft periode hibah; tanggal normal 30 hari inklusif diterima.
- [ ] Jadwal berbeda tanpa dasar perubahan ditolak; alasan resmi dapat dicatat bila diperlukan.
- [ ] Penerbitan draft ditolak bila nota dinas/jadwal, sosialisasi, pengumuman dan koordinasi belum lengkap.
- [ ] Sekretaris dapat menyimpan sosialisasi, tetapi tidak dapat menerbitkan periode.
- [ ] Setelah kelengkapan persiapan, penerbitan berhasil dan periode terbuka muncul pada form dosen.
- [ ] Periode belum terbuka/sudah ditutup tidak menerima pengajuan; periode reviewer tidak bisa dipakai untuk proposal hibah.
- [ ] Periode reviewer terpisah beracuan 7-14 hari; Kepala LPPM melengkapi pengumuman/kriteria.
- [ ] Kandidat mendaftar dengan portofolio PDF; kandidat biasa tidak dapat memilih akun lain sebagai dirinya.
- [ ] Jalur internal berjalan: 6.1 -> 6.2/rekomendasi -> 6.3/kajian -> 35.3/seleksi -> 6.4/SK -> 35.4/publikasi -> 35.5/penyampaian.
- [ ] Jalur eksternal berjalan: 7.1 -> 7.2 -> 7.3/rekomendasi -> 7.4/SK; tidak dipaksa menjalankan seleksi internal.
- [ ] Reviewer tanpa SK aktif/berkas/kelengkapan yang diwajibkan tidak dapat ditugaskan.
- [ ] Pengumuman yang diterbitkan dapat dibaca publik; portofolio dan rekomendasi tetap privat.

## C. Jalur utama proposal

Jalankan bagian ini **dua kali**, untuk Penelitian Hibah dan Pengabdian Hibah. Nama berkas boleh berbeda; jangan menandai kasus kedua lulus hanya karena jenis pertama lulus.

- [ ] Dosen membuat draft, memilih periode terbuka, mengisi judul/jenis/skema/tahun/ringkasan, lalu melengkapi tim.
- [ ] Dekan sesuai fakultas mengisi pengesahan 5.2 dan melanjutkan.
- [ ] Pengusul mengisi 5.3, proposal versi disahkan dan konfirmasi tim; pengesahan sebelumnya tersedia.
- [ ] Fakultas/prodi mengisi rekomendasi roadmap 38.3.faculty; Admin mengisi registrasi 5.4 dan verifikasi 38.3.
- [ ] Registrasi kosong/duplikat dan checklist wajib yang kosong ditolak.
- [ ] Admin menugaskan reviewer yang sah; ketua/anggota tim tidak dapat ditugaskan menilai dirinya.
- [ ] Tugas muncul pada menu Review; reviewer mengisi seluruh kolom, rekomendasi dan borang PDF.
- [ ] Tanpa penilaian semua reviewer putaran aktif, Admin tidak dapat melanjutkan review.
- [ ] Rektor mencatat SK dan nilai hibah; Admin tidak otomatis menggantikan hak Rektor.
- [ ] Admin mencatat kontrak bertanda tangan dan melanjutkan ke pencairan 70%.
- [ ] Keuangan mencatat 70% dengan nominal tepat, bukti transfer dan kuitansi; nominal salah ditolak.
- [ ] Pengusul mengisi logbook bertanggal, menyimpan 18.4, lalu kemajuan 18.5 beserta laporannya.
- [ ] Monev: jadwal -> penugasan -> surat tugas -> penilaian -> laporan hasil -> tindak lanjut pengusul -> Admin lanjut.
- [ ] Keuangan mencatat 30% dan total dua pencairan sama dengan nilai hibah, termasuk nilai rupiah yang tidak habis dibagi.
- [ ] Pengusul mengisi laporan akhir, realisasi anggaran, bukti/identitas luaran pada 18.8.
- [ ] Seminar: jadwal -> tugas reviewer -> bahan paparan/berita acara -> penilaian/konfirmasi pemaparan -> Admin lanjut.
- [ ] Pengusul mengisi perbaikan final 9.4; jenis laporan terlihat pada menu Laporan.
- [ ] Arsip: 10.3 -> 10.4 -> 39.2 -> 10.5 -> 40.1 -> 40.2 -> 40.3 -> 40.4 -> 40.5, dengan pergantian role sesuai panduan.
- [ ] Scan arsip non-PDF ditolak pada unggah langsung maupun pemakaian dokumen lama.
- [ ] Kepala LPPM menyelesaikan kegiatan setelah inventaris dan checklist lengkap; riwayat dan berkas tetap tersedia.

## D. Revisi, penolakan dan bukti

- [ ] Minta revisi tanpa alasan ditolak; alasan yang disimpan muncul dalam riwayat.
- [ ] Pengusul melakukan perbaikan/tindak lanjut sebelum kirim ulang.
- [ ] Setelah kirim ulang review/monev, tugas baru diperlukan dan penilaian lama tidak meloloskan putaran aktif.
- [ ] Form serta antrean penilaian tidak menawarkan tugas putaran lama sebagai pekerjaan aktif; pengingat lama diperiksa pada A03 di bawah.
- [ ] Pengusul maupun Admin tidak menambah versi proposal selama pengesahan/validasi/review aktif tanpa jalur revisi.
- [ ] Penolakan dicatat dan menghentikan alur; tidak ada tombol melompati tahap.
- [ ] Versi dokumen dan aktor/waktu unggah tetap tersimpan setelah revisi.
- [ ] Bukti pencatatan/cetak menampilkan data langkah dan lampiran yang sesuai; akun luar lingkup ditolak saat mengunduh.

## E. Rekap dan tindak lanjut

- [ ] CSV periode dapat diunduh oleh role yang berwenang; lingkup fakultas/prodi tidak bocor.
- [ ] Data judul yang menyerupai formula tidak dieksekusi sebagai formula spreadsheet dari ekspor.
- [ ] Evaluasi, sinkronisasi luaran, laporan tahunan, validasi, pengesahan dan distribusi dicatat sebagai langkah terpisah pada periode.
- [ ] QA membedakan bukti sinkronisasi manual dengan integrasi otomatis; tidak menandai sistem nasional sudah terkoneksi tanpa uji langsung.
- [ ] Catatan pemeliharaan/retensi dapat dibuat sesudah selesai dan tidak menghapus file elektronik asli.
- [ ] Operator memeriksa scheduler bila pengingat diuji; definisi jadwal saja tidak membuktikan worker terus berjalan.

## Temuan terbuka yang harus dicatat

| ID | Kondisi yang masih diketahui | Cara mencatat saat QA |
|---|---|---|
| A01 | Token API personal untuk user UUID gagal karena kolom tokenable_id bertipe integer | Jalur API token dicatat terblokir/gagal. Login web lokal/SSO berbasis sesi merupakan jalur berbeda. Jangan menganggap tes web menyelesaikan API token. |
| A02 | Akun mendapat permission score dari SSO tetapi tanpa nama role reviewer dapat terkena 403 pada tugasnya | Gunakan akun role reviewer yang sudah disiapkan untuk alur utama. Kasus permission-only tetap dicatat sebagai temuan, bukan dinyatakan lulus karena memakai akun lain. |
| A03 | Pengingat dapat dikirim untuk tugas putaran sebelum revisi | Bedakan tugas aktif pada Review/detail dari notifikasi lama; lampirkan ID tugas/putaran bila terjadi. |
| H09 | Audit dependency PHP pada versi asli mencatat 50 advisory di 12 paket | Hasil audit 20 November 2026 adalah snapshot. Framework tetap Laravel 10.50.2 sesuai kesepakatan; tidak ada klaim dependency PHP bersih. |

Rincian reproduksi: [audit ulang Laravel 10](audit_ulang_laravel10.md). Perbaikan sebelumnya: [laporan H01-H08](perbaikan_hibah_2026-11-20.md). Responsivitas: [laporan browser](responsivitas_2026-11-20.md).

## Format laporan bug

```text
ID kasus/checklist:
Judul singkat:
Tanggal dan alamat/port aplikasi:
Browser/OS/ukuran layar:
Role dan fakultas/prodi:
ID proposal/periode/tugas:
Prasyarat dan tahap aktif:
Langkah reproduksi:
Hasil yang diharapkan:
Hasil aktual dan pesan error:
Frekuensi: selalu/kadang-kadang:
Dampak/prioritas menurut QA:
Bukti screenshot/log yang sudah disamarkan:
```

Jangan menyertakan password, cookie/sesi, token, isi .env, atau data sensitif yang tidak perlu. Jangan mengubah langsung tabel/status atau menonaktifkan validasi untuk membuat checklist terlihat lulus. Kasus yang tidak dapat diteruskan karena prasyarat dicatat **terblokir**, bukan lulus.

## Ringkasan handover

| Ringkasan | Hasil QA |
|---|---|
| Jumlah kasus lulus | |
| Jumlah kasus gagal | |
| Jumlah kasus terblokir/belum diuji | |
| ID temuan baru dan temuan terbuka yang muncul | |
| Alur Penelitian Hibah sampai selesai | |
| Alur Pengabdian Hibah sampai selesai | |
| Catatan SSO/perangkat fisik/sistem nasional | |
| Nama dan keputusan penerimaan QA | |

Pemeriksaan otomatis terakhir yang tersimpan: **61 tes / 1.098 assertion**, pemeriksaan browser 290 kombinasi lalu 108 pemeriksaan akhir. Angka tersebut adalah bukti pengujian pengembang, bukan pengganti hasil dan persetujuan QA kampus. Keputusan push GitHub dan deployment belum diberikan oleh dokumen ini.
