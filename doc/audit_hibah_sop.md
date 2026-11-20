# Audit ulang alur hibah internal terhadap SOP LPPM 2026

> **Versi aktif dikembalikan ke Laravel 10.50.2 sesuai kesepakatan proyek.** Perbaikan fitur H01-H08 tetap ada; pembaruan dependency H09 ke Laravel 12 dibatalkan. Angka nol advisory di catatan sebelumnya tidak berlaku untuk lockfile asli.

> Audit lanjutan dan perbaikan 20 November 2026: H01-H09 ditangani, 52 tes lulus (1.045 assertion), Composer/npm audit 0 advisory. Lihat `doc/perbaikan_hibah_2026-11-20.md`. Pemetaan berikut tetap menjadi acuan SOP; angka tes lama adalah riwayat pemeriksaan.

Tanggal audit: 19 November 2026. Sumber: SOP 004-UMB-LPPM, 1 Februari 2026 (`SOP_LPPM_REVIEW 2026.pdf`). Halaman mengikuti urutan fisik PDF, bukan nomor SOP. Audit mencakup penelitian dan PkM hibah internal, bukan KKN atau keseluruhan layanan LPPM.

## Metode dan batas kesimpulan

Teks dan diagram pelaksana diperiksa untuk SOP 4, 5, 6, 7, 9, 10, 11, 18, 35, 38, 39, dan 40. Tiap langkah dibandingkan dengan route, permission, validasi server, penyimpanan dokumen, tenggat, serta tes HTTP. Status implementasi tidak membuktikan bahwa dokumen sungguhan telah ditandatangani, uang telah ditransfer, surat telah dikirim, atau data telah diperbarui di sistem nasional.

Terdapat 52 definisi langkah yang dapat dicatat secara terstruktur pada `config/grant_sop.php`; langkah sistem/berulang lainnya dipetakan terpisah di bawah. Formulir resmi diunggah oleh pelaksana dan nomor/referensi wajib dicatat. PDF tidak menyediakan isi rubrik, bobot, ambang kelulusan, atau jadwal retensi rinci; aplikasi tidak mengarang ketentuan tersebut.

## Temuan sebelum perbaikan dan tindakan

| Temuan | Perbaikan |
|---|---|
| Pengkajian rekomendasi internal dilakukan LPPM | SOP 6.3 dicatat oleh Dekan/Wr sesuai kolom diagram; seleksi SOP 35.3 tetap LPPM |
| Permohonan reviewer dan penyampaian nama belum tersedia | Nota dinas internal/eksternal, surat Rektor ke instansi, rekomendasi, instrumen seleksi, berita acara, dan bukti penyampaian ke Dit. Litabmas dicatat terpisah |
| Status appointed cukup untuk penugasan | Reviewer internal membutuhkan SK aktif, pengumuman, dan bukti penyampaian; reviewer eksternal mengikuti jalur SOP 7 tanpa memaksakan seleksi internal SOP 35 |
| Konfirmasi tunggal menutupi banyak langkah | Checklist, hasil, file, pelaksana, urutan prasyarat, dan bukti cetak per langkah |
| Pelaksanaan 90 hari digabung dengan laporan kemajuan | Tahap progress tersendiri untuk SOP 18.5 (1 hari) |
| Pemeriksaan laporan dan arsip terlalu umum | Penyerahan fisik, pemeriksaan, verifikasi kontrak/standar, tanda terima, klasifikasi, digitalisasi, lokasi fisik, inventaris, dan retensi dipisahkan |
| Laporan baru berupa angka progres | Jenis laporan progress70/final100/finance100/outputs/revised_final, identitas luaran, dokumen, pengirim, waktu, serta validasi/tanda terima disimpan |
| Durasi review proposal 7 hari berasal dari asumsi | Tenggat review proposal tidak diberi angka SOP fiktif; waktu eksplisit ditandai sumber dan durasi yang benar |
| Dokumen dapat diubah pengusul selama review | Upload konten pengajuan dikunci di tahap pemeriksaan; perubahan memerlukan revisi |
| Rekap dan tindak lanjut institusi belum ada | Ekspor CSV usulan/kontrak/luaran sesuai lingkup; pencatatan evaluasi bersama, laporan tahunan, pembaruan nasional, pengesahan, dan distribusi |

## Pemetaan langkah satu per satu

| SOP / hlm. | Langkah dan output | Pelaksana pencatatan | Waktu PDF | Implementasi / pengendalian |
|---|---|---|---|---|
| 4.1 / 5 | Menetapkan jadwal penerimaan hibah → Jadwal pelaksanaan | LPPM | 1 hari | Form call, tahap setup; wajib sebelum tahap selesai |
| 4.2 / 5 | Sosialisasi jadwal dan panduan → Surat pemberitahuan | Sekretaris/staf melalui permission sosialisasi | 1 hari | Form call, tahap setup; wajib sebelum tahap selesai |
| 5.1 / 6 | Mengumumkan penerimaan proposal hibah → Surat pengumuman | LPPM | 1 hari | Form call, tahap setup; wajib sebelum tahap selesai |
| 38.1 / 41 | Koordinasi kalender hibah dengan fakultas/prodi → Notulensi dan kesepahaman jadwal | LPPM | 1 hari | Form call, tahap setup; wajib sebelum tahap selesai |
| 35.1 / 38 | Pengumuman penerimaan calon reviewer → Surat pengumuman Kepala LPPM | Kepala LPPM | 3–7 hari | Form call, tahap setup; wajib sebelum tahap selesai |
| 5.2 / 6 | Pengesahan proposal oleh Dekan → Halaman pengesahan | Dekan | 3 hari | Form proposal, tahap endorsement; wajib sebelum tahap selesai |
| 38.3.faculty / 41 | Rekomendasi kesesuaian roadmap fakultas/prodi → Rekomendasi fakultas/prodi | Dekan/Kaprodi lingkup fakultas/prodi | 3 hari | Form proposal, tahap validation; wajib sebelum tahap selesai |
| 5.3 / 6 | Mengunggah proposal yang telah disahkan → Proposal terunggah | Peneliti/pengusul | 30 hari | Form proposal, tahap upload; wajib sebelum tahap selesai |
| 5.4 / 6 | Validasi kelengkapan dan registrasi proposal → Proposal terdaftar dan bukti registrasi | LPPM | 3 hari | Form proposal, tahap validation; wajib sebelum tahap selesai |
| 38.3 / 41 | Verifikasi usulan terhadap roadmap oleh LPPM → Rekomendasi usulan layak | LPPM | 3 hari | Form proposal, tahap validation; wajib sebelum tahap selesai |
| 18.1 / 20 | Penetapan penerima hibah → SK Rektor penerima hibah | Rektor | 1 hari | Form proposal, tahap award; wajib sebelum tahap selesai |
| 18.2 / 20 | Pencatatan kontrak penelitian/PkM → Kontrak bertanda tangan | LPPM | 1 hari | Form proposal, tahap contract; wajib sebelum tahap selesai |
| 18.3 / 20 | Pencatatan pencairan dana 70% → Bukti transfer dan kuitansi | Petugas pencatatan keuangan (delegasi administratif) | 1 hari | Form proposal, tahap payment70; wajib sebelum tahap selesai |
| 18.7 / 20 | Pencatatan pencairan dana 30% → Bukti transfer dan kuitansi | Petugas pencatatan keuangan (delegasi administratif) | 1 hari | Form proposal, tahap payment30; wajib sebelum tahap selesai |
| 18.4 / 20 | Pelaksanaan kegiatan dan catatan harian → Logbook kegiatan | Peneliti/pengusul | 90 hari | Form proposal, tahap execution; wajib sebelum tahap selesai |
| 18.5 / 20 | Laporan kemajuan 70% → Laporan kemajuan soft file | Peneliti/pengusul | 1 hari | Form proposal, tahap progress; wajib sebelum tahap selesai |
| 11.1 / 12 | Menyusun jadwal monev → Jadwal monev | LPPM | 1 hari | Form proposal, tahap monev; wajib sebelum tahap selesai |
| 11.2 / 12 | Menugaskan reviewer monev → Surat tugas reviewer | LPPM | 5 hari | Form proposal, tahap monev; wajib sebelum tahap selesai |
| 11.4 / 12 | Melaporkan hasil monev → Laporan hasil monev | LPPM | 1 hari | Form proposal, tahap monev; wajib sebelum tahap selesai |
| 11.5 / 12 | Menindaklanjuti hasil monev → Capaian luaran dan tindak lanjut | Peneliti/pengusul | 3 hari | Form proposal, tahap monev; wajib sebelum tahap selesai |
| 18.8 / 20 | Laporan kegiatan dan realisasi anggaran 100% → Laporan akhir, anggaran, dan luaran | Peneliti/pengusul | 12 hari | Form proposal, tahap final; wajib sebelum tahap selesai |
| 9.1 / 10 | Menetapkan jadwal seminar hasil → Jadwal seminar | LPPM | 1 hari | Form proposal, tahap seminar; wajib sebelum tahap selesai |
| 9.2 / 10 | Pemaparan hasil penelitian/PkM → Bahan paparan dan bukti pelaksanaan | Peneliti/pengusul | 7 hari | Form proposal, tahap seminar; wajib sebelum tahap selesai |
| 9.4 / 10 | Perbaikan laporan berdasarkan penilaian seminar → Laporan final | Peneliti/pengusul | 14 hari | Form proposal, tahap final_revision; wajib sebelum tahap selesai |
| 10.3 / 11 | Penyerahan berkas fisik laporan → Berkas fisik diserahkan | Peneliti/pengusul | 1 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 10.4 / 11 | Pemeriksaan laporan fisik dan digital → Berkas yang sudah diperiksa | LPPM | 7 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 39.2 / 42 | Verifikasi data terhadap kontrak dan standar pelaporan → Berita acara verifikasi | LPPM | 5 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 10.5 / 11 | Validasi capaian luaran dan tanda terima laporan → Tanda terima laporan | LPPM | 1 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 40.1 / 43 | Penyerahan dokumen akhir untuk arsip → Hardcopy dan softcopy siap arsip | Peneliti/pengusul | 7 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 40.2 / 43 | Pemeriksaan kelengkapan dan klasifikasi arsip → Arsip terklasifikasi tahun/skema | LPPM | 2 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 40.3 / 43 | Digitalisasi dan pengindeksan arsip → Arsip digital PDF terindeks | LPPM | 5 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 40.4 / 43 | Penataan arsip fisik → Arsip fisik tertata sesuai kode | LPPM | 2 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 40.5 / 43 | Pengesahan daftar inventaris arsip → Inventaris arsip disahkan | Kepala LPPM | 1 hari | Form proposal, tahap archive; wajib sebelum tahap selesai |
| 40.6 / 43 | Pemeliharaan dan tindakan retensi arsip → Catatan pemeliharaan / berita acara pemusnahan | Kepala LPPM | Berkala / kalender | Form proposal, tahap completed; tindak lanjut tersendiri, tidak menghambat penutupan kegiatan individual |
| 6.1 / 7 | Permohonan kebutuhan reviewer internal → Nota dinas ke Dekan/Wr | LPPM | 1 hari | Form reviewer, tahap registered; wajib sebelum tahap selesai |
| 6.2 / 7 | Rekomendasi reviewer internal → Rekomendasi sesuai bidang | Dekan/Wr | 3 hari | Form reviewer, tahap registered; wajib sebelum tahap selesai |
| 6.3 / 7 | Pengkajian rekomendasi reviewer internal → Rekomendasi yang dikaji | Dekan/Wr | 1 hari | Form reviewer, tahap recommended; wajib sebelum tahap selesai |
| 7.1 / 8 | Permohonan reviewer eksternal kepada Rektor → Nota dinas ke Rektor | LPPM | 1 hari | Form reviewer, tahap registered; wajib sebelum tahap selesai |
| 7.2 / 8 | Permohonan Rektor kepada instansi eksternal → Surat permohonan ke instansi eksternal | Rektor | 3 hari | Form reviewer, tahap registered; wajib sebelum tahap selesai |
| 7.3 / 8 | Pencatatan rekomendasi resmi instansi eksternal → Rekomendasi instansi eksternal | LPPM | 7 hari | Form reviewer, tahap registered; wajib sebelum tahap selesai |
| 35.3 / 38 | Seleksi kelayakan reviewer menurut kriteria resmi → Berita acara hasil seleksi | LPPM | 5–10 hari | Form reviewer, tahap recommended; wajib sebelum tahap selesai |
| 6.4 / 7 | Penetapan SK reviewer internal → SK reviewer | Rektor | 5 hari | Form reviewer, tahap selected; wajib sebelum tahap selesai |
| 7.4 / 8 | Penetapan SK reviewer external → SK reviewer | Rektor | 5 hari | Form reviewer, tahap selected; wajib sebelum tahap selesai |
| 35.4 / 38 | Pengumuman kelulusan reviewer → Surat pengumuman kelulusan dan SK | LPPM | 1–2 hari | Form reviewer, tahap appointed; wajib sebelum tahap selesai |
| 35.5 / 38 | Penyampaian daftar reviewer kepada Dit. Litabmas → Surat Rektor dan bukti pengiriman | Rektor | 3–5 hari | Bukti surat dan pengiriman nyata; diwajibkan sebelum penugasan reviewer internal |
| 38.5 / 41 | Evaluasi bersama fakultas/prodi → Dokumen / bukti tindak lanjut | LPPM | 3 hari | Form call, tahap annual; tindak lanjut tersendiri, tidak menghambat penutupan kegiatan individual |
| 38.6 / 41 | Sinkronisasi luaran untuk borang → Dokumen / bukti tindak lanjut | LPPM | 4 hari | Form call, tahap annual; tindak lanjut tersendiri, tidak menghambat penutupan kegiatan individual; pencatatan bukti proses eksternal, bukan konektor API otomatis |
| 38.7 / 41 | Pelaporan akhir tahun dan evaluasi IKU → Dokumen / bukti tindak lanjut | LPPM | 7 hari | Form call, tahap annual; tindak lanjut tersendiri, tidak menghambat penutupan kegiatan individual |
| 39.3 / 42 | Validasi rekap data tahunan → Dokumen / bukti tindak lanjut | LPPM | 3 hari | Form call, tahap annual; tindak lanjut tersendiri, tidak menghambat penutupan kegiatan individual |
| 39.4 / 42 | Pencatatan sinkronisasi data nasional → Dokumen / bukti tindak lanjut | LPPM | 3 hari | Form call, tahap annual; tindak lanjut tersendiri, tidak menghambat penutupan kegiatan individual; pencatatan bukti proses eksternal, bukan konektor API otomatis |
| 39.5 / 42 | Pengesahan laporan kinerja universitas → Dokumen / bukti tindak lanjut | Rektor/Wr bidang riset | 2 hari | Form call, tahap annual; tindak lanjut tersendiri, tidak menghambat penutupan kegiatan individual |
| 39.6 / 42 | Distribusi data kepada penjaminan mutu dan fakultas → Dokumen / bukti tindak lanjut | LPPM | 1 hari | Form call, tahap annual; tindak lanjut tersendiri, tidak menghambat penutupan kegiatan individual |

## Langkah sistem/berulang yang tidak menjadi satu formulir tunggal

| SOP / hlm. | Implementasi |
|---|---|
| 4.3 / 5 | Penerimaan proposal hanya pada periode terbuka. Hibah 30 hari; jadwal berbeda membutuhkan dasar perubahan resmi |
| 10.1 / 11 dan 18.4 / 20 | Logbook bertanggal sepanjang pelaksanaan hibah 90 hari. Catatan harian dapat ditinjau per periode 30 hari; angka 30 hari pada SOP umum tidak dipakai untuk memotong durasi khusus hibah |
| 10.2 / 11 | Upload laporan menyimpan ID, versi, file, pengirim, dan timestamp. Laporan terkait juga tampil di modul Laporan; bukti pencatatan dapat dicetak |
| 11.3 / 12 | Penilaian hanya oleh reviewer yang ditugaskan setelah surat tugas monev; borang resmi PDF, skor, referensi kriteria, kekuatan/capaian, perbaikan, dan rekomendasi wajib. Acuan kegiatan 7 hari, tidak disamakan dengan durasi tahap agregat |
| 18.6 / 20 | Tahap monev hibah beracuan 1 hari; rincian SOP 11 (jadwal 1, tugas 5, pelaksanaan 7, laporan 1, tindak lanjut 3) tetap ditampilkan terpisah. Kedua sumber tidak dijumlahkan atau diklaim identik |
| 9.3 / 10 | Reviewer memeriksa bahan paparan, laporan akhir dan luaran melalui borang resmi dan penugasan seminar. Acuan 7 hari dicatat pada panduan, bukan menjadi ambang skor otomatis |
| 35.2 / 38 | Form pendaftaran, portofolio, keahlian, asal, dan instansi; calon dapat mendaftar sendiri atau didaftarkan LPPM. Periode 7–14 hari, penyimpangan membutuhkan dasar resmi |
| 38.2 / 41 | Rekap kolektif usulan dosen menurut fakultas/prodi tersedia melalui ekspor dengan pembatasan lingkup. Acuan pendataan 5 hari; jadwal ditetapkan dalam kalender/periode, tidak otomatis setelah setiap pengajuan |
| 38.4 / 41 | Kontrak, SK dan surat tugas tercatat per kegiatan; ekspor periode menggabungkan data kontrak serta nilai hibah. Acuan rekap 2 hari |
| 39.1 / 42 | Pengisian laporan, identitas/jenis luaran dan bukti digital tersimpan per pengusul mengikuti kalender kegiatan |

## Ambiguitas sumber dan keputusan implementasi

- SOP 5.4 menyebut “Memvalidasi proposal oleh Tim Peneliti”, sedangkan diagram menggunakan kolom Reviewer/Sistem dengan akhir pada Pengusul. Implementasi menyimpan konfirmasi tim oleh pengusul pada 5.3, kemudian validasi administrasi dan registrasi oleh LPPM pada 5.4. Ini interpretasi implementasi yang dicatat, bukan kutipan pelaksana tunggal yang tegas dari PDF.
- Pada SOP 9.2, aktivitas pemaparan tergambar di kolom Review, sementara kegiatan pemaparan dilakukan peneliti. Implementasi menampung bahan/pelaksanaan dari peneliti dan mewajibkan reviewer mengonfirmasi pemaparan pada borang. Tidak ada klaim diagram sepenuhnya konsisten.
- SOP 18 tidak menambahkan role keuangan tersendiri. Role keuangan di aplikasi adalah pemisahan akses administratif untuk mencatat bukti pembayaran atas kewenangan LPPM; bukan perubahan penanggung jawab kelembagaan SOP.
- SOP 35 memakai nama Dit. Litabmas Ditjen Dikti. Form tujuan surat menerima nomenklatur resmi yang berlaku dan tetap mencatat referensi sumber; aplikasi tidak mengasumsikan alamat tujuan terkini.
- SOP 38–39 memuat kewajiban tahunan tingkat institusi. Kewajiban itu dilacak per periode, bukan memaksa satu proposal menunggu seluruh laporan tahunan universitas sebelum diarsipkan. Tenggat tahunan yang belum ditetapkan kalender tidak dibuat otomatis dari tanggal pembukaan hibah.
- Tanda tangan, penetapan, pembayaran, pengiriman luar, dan pemusnahan fisik dicatat berdasarkan bukti resmi. Aplikasi tidak menjalankan tindakan eksternal tersebut. Retensi tidak menghapus bukti elektronik atau audit trail.

## Bukti pemeriksaan dan batas lingkungan

- `GrantWorkflowTest`: alur hibah lengkap melalui HTTP, dari penetapan reviewer sampai arsip; termasuk tugas/penilaian monev dan seminar, laporan terstruktur, jumlah pembayaran tepat 100%, revisi, dokumen privat, dan keamanan API.
- `ReviewerAppointmentTest`: pendaftaran, rekomendasi, seleksi, SK, publikasi, dan larangan penugasan tanpa kelengkapan.
- `GrantSopAuditTest`: urutan dan dokumen wajib, aktor SOP 6.3, permohonan eksternal, nominal pencairan, retensi, tindak lanjut tahunan, bukti surat, privasi, variasi jadwal, dan tenggat berdasarkan sumber.
- Pengujian terbaru menggunakan Oracle MySQL 8.4.13 pada database terpisah `lppm_test` dengan akun khusus pengujian. Aplikasi lokal memakai `lppm_local`. SSO, MySQL institusi, keabsahan dokumen nyata, dan sistem nasional belum diuji langsung.
- Tidak ada push GitHub atau pengiriman ke instansi luar pada pekerjaan ini.

## Hasil verifikasi akhir lokal

- Setelah penghapusan fitur latihan: 36 tes lulus dengan 639 assertion pada MySQL 8.4.13, termasuk alur hibah lengkap, login SSO, dan penolakan endpoint login lokal yang telah dihapus.
- Build Vite produksi dan kompilasi Blade berhasil.
- Seluruh migrasi berhasil dijalankan pada MySQL 8.4.13. Pemeriksaan rollback sebelumnya dilakukan pada SQLite sementara; rollback MySQL belum diperiksa.
- Akun dan data latihan sudah dibersihkan dari database aktif setelah pencadangan privat: 11 akun, dua periode, dan satu proposal. Seeder data contoh dan login pemilihan role dihapus. Login dan logout kembali ke kode awal melalui SSO; konfigurasi SSO dikembalikan dari konfigurasi asli. Integrasi SSO langsung belum diverifikasi.
- Audit npm seluruh dependency: 0 kerentanan terdeteksi pada pemeriksaan 19 November 2026.
- Perubahan tetap lokal pada branch `codex/hibah-sop`; tidak dipush.
