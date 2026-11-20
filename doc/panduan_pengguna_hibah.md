# Panduan pengguna hibah internal LPPM

Diperbarui 20 November 2026 untuk pemeriksaan QA kampus. Panduan mengikuti tampilan dan perilaku aplikasi saat ini pada Laravel 10.50.2. Cakupan: Penelitian Hibah dan Pengabdian Hibah internal. KKN, hibah eksternal, dan skema mandiri tidak termasuk alur ini.

## 1. Mulai dari sini

1. Buka alamat aplikasi yang dijalankan operator. Alamat yang terakhir diperiksa adalah **http://localhost:8001/login**. Jika operator memakai port 8000, gunakan **http://127.0.0.1:8000/login**. Pilih satu alamat dan gunakan terus selama satu sesi agar cookie login tidak tertukar.
2. Untuk pemeriksaan sementara, gunakan form **Login lokal sementara** dengan email dan password akun yang diberikan operator. Tombol **Login dengan SSO** tetap tersedia, tetapi integrasi SSO institusi masih dalam penyesuaian.
3. Akun utama pemeriksaan adalah **admin@example.ex**, role Admin LPPM. Daftar 22 identitas dan pembagian fakultas/prodi ada pada [panduan akun lokal](login_lokal_sementara.md).
4. Password berbeda untuk setiap akun dan tersedia hanya pada berkas privat lokal `storage/app/local-auth/DAFTAR_AKUN_LOKAL.csv`. Password tidak disalin ke panduan atau GitHub. Minta akses berkas kepada operator jika Anda QA di komputer berbeda.
5. Pilih **Keluar**, lalu masuk dengan akun berikutnya saat berganti pelaksana. Role tidak dipilih bebas pada form login.

Login lokal hanya bekerja pada `APP_ENV=local`, flag aktif, dan akses loopback dari komputer yang menjalankan aplikasi. Membuka alamat IP komputer melalui LAN tidak otomatis mengizinkan login lokal. QA jarak jauh memerlukan lingkungan dan autentikasi yang disiapkan operator; panduan ini tidak mengaktifkan akses remote.

## 2. Navigasi dan pola pengisian

| Menu | Kegunaan |
|---|---|
| Dashboard | Ringkasan kegiatan sesuai kewenangan akun |
| Proposal | Daftar pengajuan yang dapat diakses dan tombol pembuatan proposal |
| Hibah Internal | Periode penerimaan, daftar hibah berjalan, serta akses penetapan reviewer |
| Review | Antrean penilaian hibah yang relevan dan review lama |
| Dokumen | Daftar dokumen dalam lingkup akses; unduh berkas melalui detail hibah atau bukti SOP |
| Laporan | Laporan kemajuan, akhir, keuangan, luaran dan revisi yang sudah dicatat |
| Notifikasi | Pemberitahuan penugasan, perubahan tahap dan pengingat |
| Matriks Akses | Pemeriksaan role/permission oleh akun yang berwenang |

Pada layar kecil, buka **Menu navigasi** untuk menampilkan menu. Kartu dan formulir menjadi satu kolom; halaman panjang tetap perlu discroll ke bawah. Jika tampilan lama masih terlihat setelah pembaruan, lakukan refresh paksa **Ctrl+Shift+R**.

### Dua tindakan yang berbeda

- **Simpan hasil langkah SOP ...** menyimpan hasil pemeriksaan, checklist dan bukti untuk satu langkah. Ini belum tentu memindahkan tahap proposal.
- **Selesaikan tahap dan lanjutkan** memindahkan tahap setelah seluruh langkah wajib, penugasan dan dokumen tahap tersebut lengkap. Tombol ini hanya muncul bagi pelaksana yang berwenang.

Gunakan **Lengkapi langkah dan bukti SOP tahap ini** dari detail hibah. Form pada langkah yang belum aktif atau sudah berlalu tidak dapat diisi. Jika satu tahap melibatkan beberapa role, masing-masing pelaksana mengisi bagiannya sebelum pelaksana akhir menekan tombol lanjut.

### Dokumen dan batas unggah

Gunakan PDF untuk paket pemeriksaan agar konsisten. Unggahan umum menerima PDF/JPG/PNG maksimal **10 MB per berkas**, sesuai validasi form. **Portofolio reviewer, borang penilaian, dan arsip digital hasil scan wajib PDF**. Surat pengumuman pada form pembuatan periode menerima PDF.

Dokumen proposal tersimpan sebagai versi baru, bukan menimpa riwayat. Pada form SOP, pilih **Atau gunakan dokumen yang sudah diunggah** bila jenis berkas cocok, atau unggah langsung pada langkah terkait. Pengesahan Dekan dari SOP 5.2 digunakan kembali oleh SOP 5.3.

Untuk QA lokal, beri penanda jelas **DOKUMEN UJI QA LOKAL** pada berkas simulasi. Catatan uji tidak menjadi pengesahan institusi, bukti transfer bank nyata, atau pengiriman surat sungguhan. Untuk operasional resmi, isi dan lampiran harus sesuai proses institusi.

## 3. Role yang dipakai sepanjang alur

| Role | Tugas pada hibah | Contoh akun lokal |
|---|---|---|
| Admin LPPM | Periode, administrasi, registrasi, seleksi dan tugas reviewer, penerimaan hasil | admin@example.ex |
| Sekretaris LPPM | Sosialisasi jadwal/panduan; bukan penerbit periode | sekretaris.lppm@example.ex |
| Kepala LPPM | Pengumuman penerimaan reviewer, administrasi dan pengesahan inventaris arsip | kepala.lppm@example.ex |
| Dosen/pengusul | Proposal, tim, pengajuan, revisi, pelaksanaan dan laporan | dosen.informatika1@example.ex |
| Dekan | Pengesahan proposal dan rekomendasi sesuai fakultas | dekan.teknik@example.ex |
| Kaprodi | Rekomendasi roadmap sesuai fakultas dan prodi | kaprodi.informatika@example.ex |
| Reviewer | Penilaian proposal/monev/seminar yang ditugaskan | reviewer.teknik@example.ex |
| Rektor | Penetapan SK reviewer dan penerima hibah | rektor@example.ex |
| Wakil Rektor | Rekomendasi/kajian reviewer; pengesahan tahunan sesuai kewenangan | wakil.rektor@example.ex |
| Keuangan | Mencatat bukti pencairan 70% dan 30% | keuangan@example.ex |
| Mahasiswa | Melihat kegiatan setelah ditambahkan sebagai anggota sesuai akses | mahasiswa.informatika@example.ex |

Pengusul/anggota tim tidak boleh menjadi reviewer kegiatannya sendiri. Keputusan institusional atas proposal sendiri diblokir. Gunakan pejabat lain yang berwenang dan sesuai fakultas bila pengusul juga mempunyai jabatan persetujuan. Akun Admin LPPM tidak otomatis menggantikan Rektor, Keuangan, atau Kepala LPPM untuk tindakan khusus mereka.

## 4. Menyiapkan periode

### Periode lokal yang sudah tersedia

Saat pembaruan panduan, database lokal memiliki periode **Hibah Internal 2026 - Periode November**, jenis proposal hibah, dibuka **20 November-19 Desember 2026**. Periode ini dibuka atas permintaan untuk pemeriksaan pengajuan lokal. Dokumen persiapan resmi tidak otomatis dinyatakan selesai.

QA boleh memakai periode tersebut untuk memeriksa pengajuan. Untuk memeriksa proses persiapan/penerbitan dari awal, buat **draft periode baru**, jangan menganggap periode lokal yang sudah dibuka membuktikan SOP persiapan sudah dilalui. Data lokal juga sudah memiliki satu proposal yang dibuat pengguna; panduan tidak lagi menganggap database kosong.

### Periode hibah baru melalui alur normal

1. **Admin LPPM** membuka **Hibah Internal > Buka periode hibah**. Pilih jenis **Proposal hibah**, isi nama, panduan, tanggal mulai/akhir, lalu **Simpan periode**.
2. Penerimaan beracuan **30 hari inklusif**. Jadwal berbeda harus disertai dasar perubahan jadwal resmi. Menyimpan form menghasilkan draft, bukan langsung diterbitkan.
3. Buka **Persiapan, tindak lanjut, dan rekap SOP** pada periode tersebut.
4. Admin melengkapi **4.1** (nota dinas/jadwal). **Sekretaris** mengisi **4.2** (surat sosialisasi) setelah 4.1. Admin melengkapi **5.1** (surat pengumuman) setelah 4.2 dan **38.1** (notulensi koordinasi) setelah 4.1.
5. Admin menekan **Terbitkan setelah langkah persiapan lengkap**. Periode yang terbit dan tanggalnya terbuka muncul pada dropdown pengajuan dosen.

### Periode penerimaan reviewer

Buat periode terpisah dengan jenis **Calon reviewer**. Rentang pendaftaran beracuan **7-14 hari inklusif**; penyimpangan memerlukan dasar jadwal. **Kepala LPPM** mengisi **35.1** (pengumuman dan kriteria), kemudian Admin menerbitkan periode. Periode proposal tidak bisa dipakai sebagai periode pendaftaran reviewer.

## 5. Menyiapkan reviewer sebelum penugasan

Buka **Hibah Internal > Penetapan reviewer**. Kandidat memilih periode reviewer yang terbuka, asal internal/eksternal, keahlian dan instansi, lalu unggah **portofolio PDF**. Kandidat dapat mendaftar sendiri; Admin dapat mendaftarkan akun yang dipilih.

Akun dengan role reviewer belum otomatis mempunyai SK. Selesaikan jalur penetapan berikut.

### Reviewer internal

| Urutan | Role dan tindakan | Bukti |
|---|---|---|
| 1 | Admin membuka tautan **Permohonan, rekomendasi, seleksi, dan surat SOP**; isi **6.1** | Nota dinas kebutuhan reviewer |
| 2 | Dekan sesuai fakultas atau Wakil Rektor mengisi **6.2**, lalu menekan **Catat rekomendasi** | Surat rekomendasi kesesuaian bidang |
| 3 | Dekan/Wr mengisi kajian **6.3**; Admin mengisi seleksi **35.3**, lalu **Selesaikan seleksi LPPM** | Hasil kajian, instrumen dan berita acara seleksi |
| 4 | Rektor mengisi **6.4** dan melanjutkan penetapan | SK, nomor SK dan masa berlaku |
| 5 | Admin mengisi **35.4**, lalu **Umumkan hasil penetapan** | Surat pengumuman kelulusan |
| 6 | Rektor mengisi **35.5** | Surat penyampaian nama reviewer dan bukti pengiriman |

Langkah 35.5 bukan tombol perpindahan status tambahan, tetapi kelengkapannya diperiksa sebelum reviewer internal bisa ditugaskan.

### Reviewer eksternal

Admin mengisi **7.1** (nota dinas ke Rektor), Rektor **7.2** (surat ke instansi serta referensi pengiriman), lalu Admin **7.3** berdasarkan rekomendasi resmi instansi. Admin melanjutkan rekomendasi ke status terpilih. Rektor mengisi **7.4** dan melanjutkan penetapan SK. Jalur ini tidak mewajibkan seleksi internal 35.3 atau pengumuman/penyampaian internal 35.4-35.5.

### Sebelum menugaskan

Pastikan administrasi aktif, berkas SK/portofolio tersedia, dan masa berlaku belum lewat. Pada build ini, pakai akun yang mempunyai **role reviewer dan permission `lppm.review.score`**. Pemberian permission SSO saja tanpa nama role reviewer masih mempunyai temuan akses A02; lihat [batasan QA](checklist_qa_hibah.md#temuan-terbuka-yang-harus-dicatat).

## 6. Pengajuan sampai arsip: urutan ganti akun

Tenggat dan acuan waktu terlihat pada tahap/form SOP. Di luar jendela penerimaan, QA urutan tidak perlu menunggu seluruh durasi kegiatan (misalnya 90 hari) sebelum melanjutkan; kelengkapan langkah, role dan bukti tetap diperiksa. Ini bukan perubahan jadwal operasional institusi.

Contoh berikut memakai dosen Teknik Informatika. Untuk Fakultas Ekonomi, ganti dengan dosen, Dekan, Kaprodi dan reviewer dari lingkup yang cocok. Kedua jenis kegiatan, **Penelitian Hibah** dan **Pengabdian Hibah**, menggunakan urutan yang sama.

| Tahap yang tampil | Akun/pelaksana | Tindakan dan dokumen utama sebelum lanjut |
|---|---|---|
| Draft | Dosen pengusul | Buat proposal, lengkapi judul/jenis/skema/tahun/ringkasan, pilih periode terbuka, simpan. Buka **Edit proposal dan tim** untuk anggota. Unggah proposal awal bila tersedia agar Dekan dapat memeriksa isinya; lanjutkan untuk meminta pengesahan. |
| Pengesahan Dekan | Dekan sesuai fakultas | Isi **5.2** dengan referensi, tanggal, checklist dan halaman pengesahan. Simpan langkah, lalu lanjutkan tahap. |
| Unggah proposal disahkan | Pengusul | Isi **5.3**, pilih/unggah proposal yang sesuai versi disahkan, konfirmasi tim. Pengesahan 5.2 dipakai kembali. Lanjutkan selagi periode masih terbuka. |
| Validasi administrasi LPPM | Dekan/Kaprodi, lalu Admin | Fakultas/prodi mengisi **38.3.faculty** dengan rekomendasi dan matriks roadmap. Admin mengisi **5.4** (termasuk nomor registrasi unik) dan **38.3**, konfirmasi checklist pada detail, lalu lanjutkan. |
| Review proposal | Admin -> reviewer -> Admin | Admin menugaskan reviewer aktif dari form **Penugasan reviewer dan jadwal**. Reviewer menyelesaikan semua borang dan rekomendasi. Admin memutuskan lanjut/revisi/tolak. |
| Penetapan SK penerima | Rektor | Isi **18.1**, unggah SK Rektor; pada detail hibah isi nilai hibah rupiah bulat positif, lalu lanjutkan. |
| Kontrak | Admin | Isi **18.2**, nomor/tanggal/para pihak, kontrak bertanda tangan dan checklist kesesuaian. Lanjutkan. |
| Pencairan 70% | Keuangan | Isi **18.3** dengan nominal yang sesuai, referensi/tanggal transaksi, bukti transfer dan kuitansi. Lanjutkan. |
| Pelaksanaan dan laporan kemajuan | Pengusul | Isi catatan harian bertanggal melalui **Simpan logbook**. Lengkapi **18.4** dan lanjutkan ke tahap kemajuan. |
| Laporan kemajuan 70% | Pengusul | Isi **18.5**, capaian dan laporan kemajuan, lalu lanjutkan. |
| Monev internal | Admin -> reviewer -> Admin -> pengusul -> Admin | Ikuti urutan rinci monev di bawah; unggah jadwal, surat tugas, borang, laporan hasil dan bukti tindak lanjut. |
| Pencairan 30% | Keuangan | Isi **18.7**, bukti transfer dan kuitansi sisa pembayaran, lalu lanjutkan. |
| Laporan akhir dan anggaran 100% | Pengusul | Isi **18.8**: laporan akhir, realisasi anggaran 100%, bukti/identitas luaran. Lanjutkan. |
| Seminar hasil | Admin -> pengusul -> reviewer -> Admin | Ikuti urutan rinci seminar di bawah; jadwal, pemaparan, borang, berita acara, lalu lanjutkan. |
| Perbaikan laporan final | Pengusul | Isi **9.4**, tanggapan perbaikan dan laporan final diperbaiki, lalu lanjutkan. |
| Validasi dan arsip | Pengusul -> Admin -> pengusul -> Admin -> Kepala LPPM | Ikuti urutan arsip di bawah. Kepala LPPM mengonfirmasi pemeriksaan akhir dan menyelesaikan tahap. |
| Selesai | Kepala LPPM untuk tindak lanjut | Kegiatan sudah ditutup. Pemeliharaan/retensi **40.6** dapat dicatat berkala; tidak menghapus berkas elektronik asli. |

Dekan/Kaprodi memilih **Proposal** atau **Hibah Internal** untuk menemukan pengajuan dalam lingkupnya. Reviewer menemukan tugas aktif melalui **Review** atau detail proposal yang ditugaskan. Gunakan tautan detail hibah yang sama saat berpindah akun; sistem akan memeriksa kewenangan akun berikutnya.

### Cara mengisi penilaian

Reviewer membuka tugas aktif, mengisi skor 0-100, hasil penilaian, referensi kriteria/borang, kekuatan/capaian, kekurangan/perbaikan, rekomendasi, dan mengunggah borang resmi PDF. Untuk seminar, reviewer juga mengonfirmasi pemaparan telah berlangsung.

Seluruh reviewer yang ditugaskan pada putaran aktif harus menyelesaikan penilaian. Sistem tidak menetapkan kelulusan hanya dari ambang skor buatan. Bila ada rekomendasi revisi/tolak tetapi Admin tetap melanjutkan, alasan penyelesaian rekomendasi harus dicatat pada keputusan.

### Urutan monev yang tidak boleh ditukar

1. Admin mengisi **11.1** dan mengunggah jadwal monev.
2. Admin membuat penugasan reviewer monev pada detail hibah.
3. Admin mengisi **11.2** dan mengunggah surat tugas; ini juga mencatat acuan batas penilaian.
4. Reviewer menilai dan mengunggah borang PDF.
5. Admin mengisi **11.4** beserta laporan hasil monev.
6. Pengusul mengisi **11.5** beserta bukti tindak lanjut.
7. Admin menekan **Selesaikan tahap dan lanjutkan** untuk menuju pencairan 30%.

### Urutan seminar

1. Admin mengisi **9.1** dan jadwal seminar, lalu membuat tugas reviewer seminar pada detail hibah.
2. Pengusul mengisi **9.2**, bahan paparan dan berita acara pelaksanaan.
3. Reviewer menilai, mengonfirmasi pemaparan, serta mengunggah borang PDF.
4. Admin memeriksa semua penilaian dan berita acara, lalu melanjutkan.
5. Pengusul mengisi perbaikan **9.4** pada tahap berikutnya.

### Urutan arsip dalam satu tahap

| Urutan | Pelaksana | Langkah/bukti |
|---|---|---|
| 1 | Pengusul | **10.3**: bukti penyerahan laporan fisik |
| 2 | Admin | **10.4**: pemeriksaan fisik/digital; **39.2**: berita acara verifikasi data terhadap kontrak/standar; **10.5**: tanda terima laporan |
| 3 | Pengusul | **40.1**: mencatat penyerahan dokumen akhir untuk arsip setelah tanda terima |
| 4 | Admin | **40.2**: lembar kendali/klasifikasi; **40.3**: arsip scan **PDF** dan indeks; **40.4**: lokasi/kode penataan fisik |
| 5 | Kepala LPPM | **40.5**: inventaris disahkan; pada detail hibah konfirmasi checklist akhir, lalu selesaikan kegiatan |

Tanggal, referensi, lokasi dan checklist yang diminta tetap harus diisi meski langkah tertentu tidak meminta file tambahan. Jangan hanya mengunggah dokumen pada panel versi tanpa menyimpan langkah SOP yang memerlukannya.

## 7. Revisi, penolakan dan nominal pencairan

Tombol **Minta revisi**/**Tolak** tersedia pada tahap pengesahan, validasi, review, monev dan arsip bagi pelaksana yang berwenang. Keputusan ini memerlukan catatan. Pada status **Revisi**, pengusul memperbaiki proposal/dokumen atau mencatat tindak lanjut yang diminta, kemudian **Kirim ulang ke tahap pemeriksaan**. Kembali ke putaran review/monev memerlukan penugasan dan penilaian baru; borang putaran lama tidak memenuhi putaran aktif.

Pengajuan yang ditolak berhenti pada status **Ditolak**; tidak ada tombol untuk melompati atau menghidupkan ulang tahap itu dalam alur ini. Catat keputusan QA sebelum membuat pengajuan lain.

Pencairan memakai nilai yang ditetapkan saat SK penerima. Untuk nominal uji Rp10.000.000, pencatatan 70% adalah Rp7.000.000 dan 30% Rp3.000.000. Untuk nilai yang tidak habis dibagi, rupiah 70% dibulatkan ke bawah dan sisanya masuk 30%, sehingga total tetap 100%. Nominal yang tidak sesuai tahap ditolak. Sistem hanya mencatat bukti, tidak memindahkan dana bank.

## 8. Rekap, pelaporan tahunan dan arsip lanjutan

Pada periode, buka **Persiapan, tindak lanjut, dan rekap SOP**. Tombol **Unduh rekap usulan, kontrak, dan luaran** tersedia bagi role yang berwenang dan hasilnya mengikuti lingkup akses pengguna.

Tindak lanjut tahunan **38.5-38.7** dan **39.3-39.6** dicatat pada periode sesuai kalender, bukan dengan menahan seluruh proposal agar tidak dapat ditutup. Admin mencatat evaluasi/sinkronisasi/laporan/validasi/distribusi; Rektor/Wr mencatat pengesahan tahunan. Langkah ini meminta bukti proses. Tidak ada konektor otomatis ke SINTA/SISTER/PDDIKTI pada build ini.

Pada langkah yang sudah tercatat, gunakan **Bukti pencatatan / cetak** untuk melihat aktor, waktu, data dan lampiran. Cetak bukan pengganti tanda tangan resmi atau bukti pengiriman sebenarnya.

## 9. Masalah yang sering ditemui

| Gejala | Yang perlu diperiksa |
|---|---|
| Dropdown periode kosong | Jenis periode harus proposal hibah, sudah diterbitkan, tanggal terbuka. Periode reviewer berbeda. Setelah 19 Desember 2026, periode November tidak lagi terbuka. |
| File sudah diunggah tetapi tahap tidak maju | Buka checklist SOP, pilih file yang sesuai, isi semua kolom/checklist dan simpan langkah. Lalu tekan tombol lanjut dengan akun pelaksana tahap. |
| Tombol lanjut tidak muncul / 403 | Periksa akun, pemilik proposal, fakultas/prodi, role/permission dan tahap. Penolakan di luar lingkup merupakan kontrol akses, bukan otomatis bug. |
| Reviewer tidak bisa ditugaskan | Periksa konflik ketua/anggota, role/permission, SK aktif, berkas, serta publikasi dan bukti penyampaian untuk reviewer internal. |
| Selesai menilai tetapi belum bisa maju | Seluruh tugas putaran aktif harus dinilai. Periksa jadwal, surat tugas, borang, tindak lanjut, bukti dan keputusan Admin. |
| Nomor registrasi ditolak | Nomor harus diisi dan belum dipakai proposal lain. |
| Berkas ditolak | Periksa jenis file, ukuran maksimal 10 MB, dan kewajiban PDF khusus portofolio/borang/scan arsip. |
| 419 / sesi kedaluwarsa | Refresh halaman, login ulang bila perlu, lalu ulangi pengisian. Jangan berpindah localhost/127.0.0.1 atau port di tengah sesi. |
| 429 / terlalu banyak percobaan | Tunggu batas satu menit berlalu; cek ulang password di berkas privat. Batas lokal: 6 percobaan per akun+IP dan 60 per IP per menit. |
| Menu tidak terlihat pada HP | Buka **Menu navigasi**. Untuk aset lama, refresh paksa. |
| Pengingat menunjukkan tugas putaran lama | Catat sebagai temuan terbuka A03; verifikasi pekerjaan aktif dari menu Review/detail hibah. Jangan menilai ulang tugas lama hanya karena notifikasi. |

## 10. Handover QA

Gunakan [checklist QA hibah](checklist_qa_hibah.md) untuk hasil lulus/gagal, role, ID proposal, bukti layar dan langkah reproduksi. Temuan A01-A03 serta advisory dependency PHP masih terbuka; kelulusan suite rutin tidak menghapus temuan tersebut. Pengujian fitur bukan penerimaan produksi atau bukti integrasi institusi sudah berjalan.

Lampiran lengkap jenis dokumen tiap langkah ada pada [daftar dokumen SOP](daftar_dokumen_hibah.md). Untuk penyiapan aplikasi, akun, scheduler dan konfigurasi SSO, operator menggunakan [README](../README.md), bukan mengubah database atau memilih role lewat browser.
