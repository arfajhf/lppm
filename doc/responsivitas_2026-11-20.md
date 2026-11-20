# Perbaikan responsivitas - 20 November 2026

## Perubahan

- Grid utama dan seluruh anak kartu/form diberi batas intrinsic yang benar (min-width: 0); ringkasan, judul, nama panjang dan label permission membungkus tanpa memaksa halaman melebar.
- Tombol tautan sekarang inline-flex dengan tinggi minimum 44px, sehingga padding mengikuti aliran layout dan tidak menimpa label/form berikutnya. Tombol form menyesuaikan isi di desktop dan memenuhi lebar pada HP.
- Sidebar desktop tetap sticky dan dapat discroll. Pada lebar <=1080px, navigasi menjadi details/summary yang dapat dilipat dan dioperasikan keyboard. Tanpa JavaScript, navigasi tetap tersedia sebagai elemen native.
- Header, profil, logout, grid statistik, daftar proposal, tabel berbasis baris, detail, input file, checkbox, form dan halaman login menyesuaikan breakpoint tablet/HP.
- Logo login mempertahankan rasio aslinya pada HP. Jarak antar-form, tombol lokal/SSO, serta kartu dirapikan.
- Pagination memakai markup aplikasi dengan tombol minimal 44px dan dapat membungkus baris, tanpa membutuhkan kelas Tailwind yang tidak dibundel proyek.
- Fokus keyboard terlihat; preferensi reduced motion dihormati.

## Verifikasi

Sebelum perbaikan, halaman hibah pada screenshot menghasilkan lebar scroll 1.485px pada viewport 390px/320px, dan 1.811px pada viewport 1.440px. Sesudah perbaikan, lebar scroll sama dengan viewport (320, 390 dan 1.440px), tanpa menyembunyikan overflow global.

Browser Chrome terpisah menguji 290 kombinasi halaman dan ukuran: 320, 375, 390, 480, 720, 768, 1024, 1080, 1280, 1440 dan 1920px. Tidak ditemukan overflow konten, tumpukan tombol atau error JavaScript pada pemeriksaan tersebut. Dekorasi glow login yang sengaja dipotong oleh kontainernya tidak dianggap overflow konten.

Setelah perapian jarak form terakhir, pemeriksaan ulang 108 kombinasi pada 320/390/768/1440px juga lulus. Menu mobile diuji dengan keyboard, kondisi buka/tutup dan perubahan kembali ke desktop. Judul/nama sangat panjang tanpa spasi juga diperiksa. Pagination dengan banyak halaman diuji tersendiri pada empat ukuran layar.

Cakupan halaman: landing, login, pengumuman, dashboard, daftar/buat/edit proposal, pusat/detail hibah, checklist SOP proposal/periode, reviewer, dokumen, review, laporan, notifikasi dan matriks akses. Pemeriksaan memakai halaman publik serta akun lokal dosen/admin; tidak membuat atau mengubah data hibah.

- Suite fitur: **61 tes lulus, 1.098 assertion**.
- Build Vite dan kompilasi Blade berhasil.
- composer.json/composer.lock tetap identik dengan versi awal Laravel 10.50.2.
- Tidak ada perubahan alur persetujuan, login, permission, data pengguna/proposal maupun deployment. Belum dipush.

Bukti JSON dan screenshot ada pada output/responsive/. Pengujian dilakukan pada Chrome desktop dengan viewport yang diubah; perangkat fisik/iOS/Safari belum diuji langsung.

## Memeriksa manual

Refresh paksa (Ctrl+Shift+R) pada aplikasi lokal, termasuk localhost:8001 yang digunakan pada screenshot pengguna. Perkecil jendela atau buka device toolbar browser. Navigasi HP tersedia melalui tombol Menu navigasi. Periksa detail proposal ber-ringkasan panjang, lalu scroll ke Dokumen dan versi: semua kontrol tetap berada di dalam kartu tanpa scroll horizontal.
