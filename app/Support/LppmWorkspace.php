<?php

namespace App\Support;

use App\Models\Proposal;
use App\Models\User;

class LppmWorkspace
{
    public static function primaryRole(User $user): string
    {
        $roles = $user->getEffectiveLppmRoles();

        foreach (['super_admin', 'lppm_admin', 'rektorat', 'dekan', 'kaprodi', 'dosen', 'mahasiswa', 'mitra', 'reviewer', 'staff', 'yayasan', 'admin'] as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        return 'dosen';
    }

    public static function forUser(User $user): array
    {
        $role = self::primaryRole($user);
        $profiles = self::profiles();

        if ($role === 'super_admin') {
            $profile = $profiles['admin_lppm'];
            $profile['role_label'] = 'Super Admin';
            $profile['dashboard']['title'] = 'Pusat Kendali Platform LPPM';
            $profile['dashboard']['description'] = 'Super admin memantau kesehatan platform, akses lintas peran, dan kesiapan alur proposal dari sisi pengelolaan sistem.';
            $profile['navigation_labels']['dashboard'] = 'Dashboard Platform';
            $profile['navigation_labels']['notifications.index'] = 'Notifikasi Platform';
            $profile['navigation_labels']['access.index'] = 'Akses Platform';

            return $profile;
        }

        if ($role === 'lppm_admin') {
            $profile = $profiles['admin_lppm'];
            $profile['role_label'] = 'LPPM Admin';

            return $profile;
        }

        if ($role === 'rektorat') {
            $profile = $profiles['yayasan'];
            $profile['role_label'] = 'Rektorat';
            $profile['dashboard']['title'] = 'Panel Monitoring Rektorat';
            $profile['dashboard']['description'] = 'Rektorat memantau proposal yang sudah matang, progres pelaksanaan, dan laporan strategis tanpa masuk ke detail operasional harian.';
            $profile['navigation_labels']['dashboard'] = 'Monitoring';
            $profile['navigation_labels']['proposals.index'] = 'Proposal Monitoring';
            $profile['navigation_labels']['reports.index'] = 'Laporan Monitoring';
            $profile['navigation_labels']['notifications.index'] = 'Notifikasi Monitoring';

            return $profile;
        }

        if ($role === 'dekan') {
            $profile = $profiles['dosen'];
            $profile['role_label'] = 'Dekan';
            $profile['dashboard']['title'] = 'Ruang Eksekutor Fakultas';
            $profile['dashboard']['description'] = 'Dekan mengawal pengajuan pada level fakultas sekaligus tetap dapat bertindak sebagai pengusul sesuai kewenangannya.';
            $profile['navigation_labels']['dashboard'] = 'Dashboard Fakultas';
            $profile['navigation_labels']['proposals.index'] = 'Proposal Fakultas';
            $profile['navigation_labels']['notifications.index'] = 'Notifikasi Fakultas';

            return $profile;
        }

        if ($role === 'kaprodi') {
            $profile = $profiles['dosen'];
            $profile['role_label'] = 'Kaprodi';
            $profile['dashboard']['title'] = 'Ruang Eksekutor Program Studi';
            $profile['dashboard']['description'] = 'Kaprodi memantau dan menindaklanjuti pengajuan pada level program studi sekaligus tetap dapat menjadi pengusul.';
            $profile['navigation_labels']['dashboard'] = 'Dashboard Prodi';
            $profile['navigation_labels']['proposals.index'] = 'Proposal Prodi';
            $profile['navigation_labels']['notifications.index'] = 'Notifikasi Prodi';

            return $profile;
        }

        if ($role === 'admin') {
            return $profiles['admin_lppm'];
        }

        return $profiles[$role] ?? $profiles['dosen'];
    }

    public static function module(User $user, string $module): array
    {
        return self::forUser($user)['modules'][$module] ?? [
            'eyebrow' => ucfirst($module),
            'title' => ucfirst($module),
            'description' => 'Modul ini disiapkan untuk mendukung alur kerja LPPM sesuai role Anda.',
            'empty' => 'Belum ada data untuk ditampilkan.',
            'focus' => [],
        ];
    }

    public static function proposalGuidance(User $user, Proposal $proposal): array
    {
        $role = self::primaryRole($user);
        $status = strtolower((string) $proposal->status);

        $map = [
            'super_admin' => [],
            'admin_lppm' => [
                'draft' => ['Pastikan proposal siap dikirim sebelum masuk antrean review.', 'Tentukan reviewer bila dokumen dan kelengkapan sudah memadai.'],
                'pending_kaprodi' => ['Pantau proposal yang masih berada di tingkat prodi sebelum masuk jalur fakultas.', 'Belum perlu menarik proposal ini ke LPPM sebelum persetujuan internal selesai.'],
                'pending_dekan' => ['Pantau proposal yang sedang menunggu persetujuan dekan.', 'Siapkan langkah administratif berikutnya setelah proposal resmi masuk ke LPPM.'],
                'submitted' => ['Validasi kelengkapan administrasi dan skema kegiatan.', 'Dorong proposal ke fase review agar antrean tidak mengendap.'],
                'review' => ['Pantau reviewer aktif dan pastikan keputusan terdokumentasi.', 'Siapkan arah revisi atau approval berdasarkan hasil penilaian.'],
                'revision' => ['Tunggu revisi pengusul dan cek perubahan dokumen utama.', 'Pastikan catatan reviewer benar-benar ditindaklanjuti.'],
                'approved' => ['Monitor dokumen pelaksanaan dan jadwal laporan progres.', 'Jaga notifikasi agar mitra dan tim tidak kehilangan konteks.'],
                'completed' => ['Arsipkan hasil, laporan, dan mitra sebagai rekam jejak kegiatan.', 'Gunakan data ini untuk evaluasi roadmap berikutnya.'],
            ],
            'admin' => [],
            'reviewer' => [
                'submitted' => ['Telaah ringkasan, skema, dan kelengkapan dokumen sebelum memberi skor.', 'Catatan review sebaiknya tajam, sopan, dan tidak menulis puisi abstrak.'],
                'review' => ['Lengkapi skor, komentar, dan rekomendasi keputusan.', 'Pastikan penilaian konsisten dengan tujuan penelitian atau pengabdian.'],
                'revision' => ['Bandingkan revisi terbaru dengan catatan review sebelumnya.', 'Tentukan apakah proposal layak disetujui atau butuh koreksi lanjutan.'],
                'approved' => ['Pantau laporan progres bila Anda masih dilibatkan sebagai evaluator.', 'Gunakan hasil approval sebagai baseline monitoring berikutnya.'],
            ],
            'dosen' => [
                'draft' => ['Lengkapi ringkasan proposal, anggota, dan dokumen pendukung.', 'Pastikan skema dan jenis kegiatan sudah sesuai tujuan pengajuan.'],
                'pending_kaprodi' => ['Proposal sedang menunggu persetujuan kaprodi.', 'Pastikan dokumen dan ringkasan sudah cukup kuat sebelum ditinjau lebih lanjut.'],
                'pending_dekan' => ['Proposal sudah melewati tahap prodi dan kini menunggu persetujuan dekan.', 'Pantau notifikasi agar Anda siap merespons bila ada catatan lanjutan.'],
                'submitted' => ['Pantau notifikasi dan siapkan respons bila admin meminta perbaikan.', 'Jangan panik; fase submitted itu normal, bukan keputusan akhir.'],
                'review' => ['Baca catatan reviewer dengan kepala dingin dan kopi hangat secukupnya.', 'Siapkan poin revisi yang paling berdampak pada kelayakan proposal.'],
                'revision' => ['Perbarui dokumen, laporan awal, atau komposisi tim sesuai catatan evaluasi.', 'Pastikan revisi menjawab komentar, bukan hanya mempercantik margin.'],
                'approved' => ['Mulai eksekusi kegiatan dan unggah laporan progres tepat waktu.', 'Koordinasikan mahasiswa dan mitra agar ritme kegiatan tetap stabil.'],
                'completed' => ['Pastikan laporan akhir dan dokumen penutup sudah lengkap.', 'Gunakan hasil kegiatan sebagai pijakan pengajuan tahap berikutnya.'],
            ],
            'mahasiswa' => [
                'draft' => ['Bantu pengusul merapikan ringkasan, anggota, dan dokumen pendukung.', 'Pastikan peran Anda pada tim sudah tercatat dengan benar.'],
                'review' => ['Pantau catatan revisi yang perlu didukung dari sisi administrasi atau lampiran.', 'Jangan sampai file pendukung hilang seperti tugas kelompok klasik.'],
                'approved' => ['Bantu pelaksanaan kegiatan dan pembaruan dokumen lapangan.', 'Ikuti progres laporan agar kontribusi Anda tetap sinkron dengan tim.'],
                'completed' => ['Pastikan arsip kegiatan, luaran, dan dokumen akhir tertata.', 'Hasil kerja tim yang rapi jauh lebih menenangkan daripada folder bernama final_fix_baru2.'],
            ],
            'mitra' => [
                'approved' => ['Pantau kesiapan kolaborasi, jadwal kegiatan, dan dokumen yang sudah disetujui.', 'Pastikan pihak kampus dan mitra memiliki ekspektasi hasil yang sama.'],
                'completed' => ['Lihat laporan akhir dan hasil kolaborasi yang sudah dicapai.', 'Gunakan data ini untuk evaluasi kerja sama lanjutan.'],
            ],
            'staff' => [
                'submitted' => ['Bantu cek kelengkapan administrasi awal sebelum diteruskan.', 'Jaga alur dokumen agar tidak mandek di meja virtual.'],
                'review' => ['Dukung koordinasi antar pihak selama proses evaluasi berlangsung.', 'Pastikan notifikasi dan dokumen yang relevan mudah ditemukan.'],
            ],
            'yayasan' => [
                'approved' => ['Pantau proposal yang sudah mendapat persetujuan untuk konteks monitoring.', 'Fokus pada dampak, progres, dan keteraturan pelaporan.'],
                'completed' => ['Lihat hasil akhir kegiatan sebagai bahan evaluasi strategis.', 'Data yang rapi lebih berguna daripada cerita heroik tanpa lampiran.'],
            ],
            'rektorat' => [
                'pending_kaprodi' => ['Proposal masih berada di tahap persetujuan internal dan belum masuk ranah monitoring strategis.', 'Gunakan fase ini untuk menjaga ekspektasi bahwa proposal belum siap dipantau institusional.'],
                'pending_dekan' => ['Proposal masih menunggu validasi fakultas.', 'Monitoring strategis biasanya lebih relevan setelah proposal bergerak ke LPPM atau pelaksanaan.'],
                'submitted' => ['Proposal sudah resmi berada di jalur LPPM dan siap dipantau dari sisi institusional.', 'Perhatikan ritme masuknya proposal sebagai sinyal aktivitas akademik.'],
                'approved' => ['Pantau proposal yang sudah mendapat persetujuan untuk konteks monitoring strategis.', 'Fokus pada dampak, progres, dan keteraturan pelaporan lintas unit.'],
                'completed' => ['Lihat hasil akhir kegiatan sebagai bahan evaluasi institusional.', 'Data yang rapi lebih berguna daripada cerita heroik tanpa lampiran.'],
            ],
            'dekan' => [
                'draft' => ['Lengkapi pengajuan yang Anda buat atau pantau proposal fakultas yang membutuhkan tindak lanjut.', 'Pastikan skema kegiatan dan dukungan fakultas sudah selaras.'],
                'pending_kaprodi' => ['Proposal masih berada di meja kaprodi.', 'Belum perlu diteruskan sebelum persetujuan level prodi selesai.'],
                'pending_dekan' => ['Proposal menunggu persetujuan Anda sebagai dekan.', 'Pastikan proposal ini layak diteruskan ke LPPM beserta konteks fakultasnya.'],
                'submitted' => ['Pantau pengajuan yang menunggu langkah berikutnya dan siapkan keputusan sesuai kewenangan.', 'Pastikan koordinasi dengan prodi dan LPPM tidak tersendat.'],
                'review' => ['Baca catatan reviewer dan siapkan keputusan lanjutan dengan konteks fakultas.', 'Gunakan fase review sebagai bahan pembinaan proposal, bukan hanya formalitas.'],
                'revision' => ['Pastikan revisi menjawab catatan penting dan siap diteruskan.', 'Koordinasikan kebutuhan perbaikan dengan pengusul dan unit terkait.'],
                'approved' => ['Pantau pelaksanaan kegiatan yang sudah berjalan dari lingkup fakultas.', 'Jaga ritme laporan agar fakultas tidak kehilangan jejak progres.'],
                'completed' => ['Pastikan dokumen akhir dan luaran kegiatan tercatat baik.', 'Gunakan hasil kegiatan untuk evaluasi dan pengembangan pengajuan berikutnya.'],
            ],
            'kaprodi' => [
                'draft' => ['Lengkapi pengajuan yang Anda buat atau proposal prodi yang sedang dipersiapkan.', 'Pastikan tujuan kegiatan dan keterlibatan tim sudah jelas sejak awal.'],
                'pending_kaprodi' => ['Proposal menunggu persetujuan Anda sebagai kaprodi.', 'Periksa kelayakan awal sebelum proposal diteruskan ke dekan.'],
                'pending_dekan' => ['Proposal sudah diteruskan ke dekan.', 'Pantau tindak lanjut berikutnya agar pengusul tidak kehilangan konteks.'],
                'submitted' => ['Pantau pengajuan prodi yang masuk ke antrean persetujuan berikutnya.', 'Siapkan tindak lanjut agar proposal tidak berhenti di meja digital.'],
                'review' => ['Baca catatan reviewer dan bantu pengusul menyiapkan revisi yang paling berdampak.', 'Pastikan perbaikan proposal tetap sejalan dengan arah prodi.'],
                'revision' => ['Dampingi penyempurnaan dokumen dan tim sebelum proposal diteruskan lagi.', 'Pastikan revisi menjawab inti catatan evaluasi.'],
                'approved' => ['Pantau pelaksanaan kegiatan yang berasal dari lingkungan prodi.', 'Jaga agar laporan progres tidak tertinggal dari aktivitas lapangan.'],
                'completed' => ['Pastikan hasil akhir kegiatan terdokumentasi rapi.', 'Gunakan rekam jejak ini untuk penguatan pengajuan prodi berikutnya.'],
            ],
        ];

        $guidance = $map[$role][$status] ?? [];

        if (in_array($role, ['admin', 'super_admin'], true) && $guidance === []) {
            $guidance = $map['admin_lppm'][$status] ?? [];
        }

        return $guidance;
    }

    private static function profiles(): array
    {
        return [
            'admin_lppm' => [
                'role_label' => 'Admin LPPM',
                'dashboard' => [
                    'title' => 'Pusat Kendali Siklus LPPM',
                    'description' => 'Panel ini memantau proposal, review, laporan, dan kesiapan akses lintas peran dari draft sampai selesai.',
                    'focus' => [
                        ['title' => 'Kendalikan Workflow', 'description' => 'Dorong proposal dari submitted ke review, lalu ke approval dan pelaporan secara tertib.'],
                        ['title' => 'Atur Reviewer', 'description' => 'Pastikan penugasan dan hasil review terbaca jelas untuk setiap proposal yang aktif.'],
                        ['title' => 'Awasi Kualitas Data', 'description' => 'Jaga dokumen, laporan, dan notifikasi agar seluruh lifecycle tidak bolong di tengah jalan.'],
                    ],
                    'proposal_heading' => 'Seluruh proposal aktif',
                    'review_heading' => 'Antrian evaluasi terkini',
                ],
                'navigation_labels' => [
                    'dashboard' => 'Dashboard LPPM',
                    'proposals.index' => 'Semua Proposal',
                    'documents.index' => 'Dokumen LPPM',
                    'reviews.index' => 'Review & Approval',
                    'reports.index' => 'Monitoring Laporan',
                    'notifications.index' => 'Notifikasi Sistem',
                    'access.index' => 'Matriks Akses',
                ],
                'modules' => [
                    'proposals' => ['eyebrow' => 'Proposal LPPM', 'title' => 'Seluruh Proposal Penelitian dan Pengabdian', 'description' => 'Admin LPPM melihat semua proposal untuk memvalidasi status, kelengkapan, dan kesiapan review.', 'empty' => 'Belum ada proposal yang masuk ke pusat kendali LPPM.', 'focus' => ['Validasi kelengkapan proposal baru.', 'Pantau skema dan jenis kegiatan lintas tahun.', 'Pastikan proposal bergerak sesuai workflow blueprint.'], 'cta_label' => 'Tambah Proposal'],
                    'documents' => ['eyebrow' => 'Dokumen Administrasi', 'title' => 'Dokumen lintas proposal', 'description' => 'Panel dokumen membantu admin memastikan semua berkas utama tersedia sebelum review dan pelaporan.', 'empty' => 'Belum ada dokumen yang tercatat di tingkat LPPM.', 'focus' => ['Cek kelengkapan unggahan.', 'Lihat siapa pengunggah terakhir.', 'Bandingkan dokumen dengan status proposal.']],
                    'reviews' => ['eyebrow' => 'Review & Approval', 'title' => 'Antrian evaluasi dan keputusan', 'description' => 'Admin LPPM memantau skor, komentar, dan keputusan reviewer untuk menjaga ritme evaluasi.', 'empty' => 'Belum ada review yang masuk ke meja evaluasi.', 'focus' => ['Lihat proposal yang menunggu tindakan.', 'Pantau reviewer aktif.', 'Siapkan tindak lanjut approval atau revisi.']],
                    'reports' => ['eyebrow' => 'Monitoring Progres', 'title' => 'Laporan kegiatan lintas proposal', 'description' => 'Admin LPPM memonitor progres pelaksanaan dan ketertiban pelaporan setelah proposal disetujui.', 'empty' => 'Belum ada laporan progres yang masuk.', 'focus' => ['Pantau progress kegiatan.', 'Lihat proposal yang tertinggal laporannya.', 'Jaga kesinambungan dari approved ke completed.']],
                    'notifications' => ['eyebrow' => 'Notifikasi Operasional', 'title' => 'Sinyal sistem dan aktivitas pengguna', 'description' => 'Notifikasi digunakan untuk memantau pergerakan proposal, review, dan laporan secara real-time.', 'empty' => 'Belum ada notifikasi yang perlu ditinjau.', 'focus' => ['Lihat sinyal penting dari seluruh role.', 'Gunakan notifikasi sebagai alarm operasional.', 'Jaga respons tim tetap cepat.']],
                    'access' => ['eyebrow' => 'RBAC LPPM', 'title' => 'Matriks Role dan Permission', 'description' => 'Halaman ini membandingkan kebutuhan blueprint LPPM dengan role dan permission dari SSO.', 'empty' => 'Tidak ada gap akses saat ini.', 'focus' => ['Bandingkan akses blueprint dan SSO.', 'Evaluasi permission efektif user.', 'Pastikan client mengikuti standar umb-sso.']],
                ],
            ],
            'admin' => [],
            'reviewer' => [
                'role_label' => 'Reviewer',
                'dashboard' => [
                    'title' => 'Meja Review Proposal',
                    'description' => 'Fokus utama reviewer adalah menilai proposal yang ditugaskan, memberi catatan, dan mendorong keputusan yang bertanggung jawab.',
                    'focus' => [
                        ['title' => 'Telaah Proposal', 'description' => 'Lihat proposal yang masuk ke penugasan Anda dan cek kualitas ringkasan, skema, serta dokumen.'],
                        ['title' => 'Isi Skor dan Catatan', 'description' => 'Pastikan komentar review bisa ditindaklanjuti, bukan hanya terdengar galak.'],
                        ['title' => 'Pantau Revisi', 'description' => 'Bandingkan perbaikan terbaru dengan umpan balik yang sudah Anda berikan sebelumnya.'],
                    ],
                    'proposal_heading' => 'Proposal dalam penugasan Anda',
                    'review_heading' => 'Review yang perlu dituntaskan',
                ],
                'navigation_labels' => [
                    'dashboard' => 'Meja Review',
                    'proposals.index' => 'Proposal Tinjauan',
                    'documents.index' => 'Dokumen Review',
                    'reviews.index' => 'Catatan Review',
                    'reports.index' => 'Laporan Kegiatan',
                    'notifications.index' => 'Notifikasi Review',
                ],
                'modules' => [
                    'proposals' => ['eyebrow' => 'Proposal Tinjauan', 'title' => 'Proposal yang perlu Anda telaah', 'description' => 'Reviewer hanya melihat proposal yang ditugaskan agar fokus evaluasi tetap tajam.', 'empty' => 'Belum ada proposal yang ditugaskan ke Anda.', 'focus' => ['Baca ringkasan dan skema proposal.', 'Cek kesiapan dokumen sebelum memberi skor.', 'Gunakan status untuk mengukur prioritas review.']],
                    'documents' => ['eyebrow' => 'Dokumen Review', 'title' => 'Lampiran yang mendukung proses penilaian', 'description' => 'Panel dokumen reviewer berisi berkas dari proposal yang sedang atau pernah Anda review.', 'empty' => 'Belum ada dokumen untuk proposal penugasan Anda.', 'focus' => ['Buka dokumen pendukung proposal.', 'Bandingkan revisi dengan unggahan sebelumnya.', 'Lihat siapa pengunggah berkas terakhir.']],
                    'reviews' => ['eyebrow' => 'Penilaian', 'title' => 'Skor dan catatan review Anda', 'description' => 'Halaman ini memusatkan semua review yang sudah masuk atau masih butuh keputusan dari Anda.', 'empty' => 'Belum ada review yang tercatat untuk Anda.', 'focus' => ['Pastikan skor terisi konsisten.', 'Catat alasan revisi atau approval.', 'Gunakan status review untuk mengelola antrean.']],
                    'reports' => ['eyebrow' => 'Laporan Pelaksanaan', 'title' => 'Laporan dari proposal yang pernah Anda nilai', 'description' => 'Reviewer dapat memantau bagaimana proposal yang disetujui bergerak saat pelaksanaan.', 'empty' => 'Belum ada laporan untuk proposal dalam lingkup review Anda.', 'focus' => ['Pantau progres proposal hasil review.', 'Bandingkan hasil pelaksanaan dengan kualitas proposal awal.', 'Jadikan laporan sebagai umpan balik evaluasi.']],
                    'notifications' => ['eyebrow' => 'Sinyal Review', 'title' => 'Notifikasi terkait penugasan dan keputusan review', 'description' => 'Gunakan notifikasi untuk melihat proposal baru, revisi, atau pembaruan yang berkaitan dengan penilaian Anda.', 'empty' => 'Belum ada notifikasi review.', 'focus' => ['Pantau proposal baru.', 'Lihat revisi yang kembali ke meja Anda.', 'Jaga waktu respons tetap rapi.']],
                ],
            ],
            'dosen' => [
                'role_label' => 'Dosen',
                'dashboard' => [
                    'title' => 'Ruang Pengusul Proposal',
                    'description' => 'Dashboard ini membantu dosen mengelola proposal penelitian dan pengabdian dari penyusunan, review, sampai pelaporan.',
                    'focus' => [
                        ['title' => 'Susun Proposal', 'description' => 'Buat proposal baru sesuai jenis kegiatan dan skema yang tepat.'],
                        ['title' => 'Kelola Dokumen', 'description' => 'Unggah dokumen pendukung dan cek catatan review tanpa perlu berburu file di banyak tempat.'],
                        ['title' => 'Tindak Lanjut Review', 'description' => 'Respons revisi, pantau approval, lalu lanjutkan ke laporan progres saat proposal berjalan.'],
                    ],
                    'proposal_heading' => 'Proposal yang Anda pimpin',
                    'review_heading' => 'Umpan balik terhadap proposal Anda',
                ],
                'navigation_labels' => [
                    'dashboard' => 'Dashboard',
                    'proposals.index' => 'Proposal',
                    'documents.index' => 'Dokumen',
                    'reviews.index' => 'Review',
                    'reports.index' => 'Laporan',
                    'notifications.index' => 'Notifikasi',
                ],
                'modules' => [
                    'proposals' => ['eyebrow' => 'Proposal Saya', 'title' => 'Pengajuan penelitian dan pengabdian yang Anda kelola', 'description' => 'Dosen melihat proposal milik sendiri atau yang melibatkan dirinya sebagai anggota inti.', 'empty' => 'Anda belum memiliki proposal aktif. Saatnya menulis ide yang bisa diuji, bukan hanya disimpan di kepala.', 'focus' => ['Buat proposal baru.', 'Lihat status setiap pengajuan.', 'Kelola anggota dan tindak lanjut review.'], 'cta_label' => 'Buat Proposal'],
                    'documents' => ['eyebrow' => 'Dokumen Proposal', 'title' => 'Berkas pendukung untuk proposal Anda', 'description' => 'Semua dokumen yang relevan dengan pengajuan dan pelaksanaan proposal Anda ditampilkan di sini.', 'empty' => 'Belum ada dokumen yang terunggah untuk proposal Anda.', 'focus' => ['Unggah dokumen sesuai kebutuhan review.', 'Pastikan file terbaru sudah masuk.', 'Cek kecocokan dokumen dengan status proposal.']],
                    'reviews' => ['eyebrow' => 'Hasil Evaluasi', 'title' => 'Catatan reviewer atas proposal Anda', 'description' => 'Gunakan halaman ini untuk memahami skor, komentar, dan arah revisi dari reviewer.', 'empty' => 'Belum ada review untuk proposal Anda.', 'focus' => ['Baca skor dan komentar reviewer.', 'Siapkan tindak lanjut revisi.', 'Pantau perubahan status review ke approval.']],
                    'reports' => ['eyebrow' => 'Laporan Progres', 'title' => 'Pelaporan kemajuan kegiatan', 'description' => 'Setelah proposal disetujui, dosen bertanggung jawab menjaga ritme pelaporan agar kegiatan tetap terukur.', 'empty' => 'Belum ada laporan progres untuk proposal Anda.', 'focus' => ['Isi progres kegiatan.', 'Catat capaian dan hambatan.', 'Jaga transisi dari approved ke completed.']],
                    'notifications' => ['eyebrow' => 'Notifikasi Pengusul', 'title' => 'Pembaruan yang memengaruhi proposal Anda', 'description' => 'Notifikasi membantu Anda melihat perubahan status, review baru, dan kebutuhan tindak lanjut administrasi.', 'empty' => 'Belum ada notifikasi untuk proposal Anda.', 'focus' => ['Lihat status terbaru proposal.', 'Pantau catatan review baru.', 'Jangan biarkan revisi menunggu terlalu lama.']],
                ],
            ],
            'mahasiswa' => [
                'role_label' => 'Mahasiswa',
                'dashboard' => [
                    'title' => 'Ruang Kolaborasi Mahasiswa',
                    'description' => 'Mahasiswa berperan sebagai anggota kegiatan yang membantu dokumen, progres, dan koordinasi pelaksanaan.',
                    'focus' => [
                        ['title' => 'Dukung Tim Proposal', 'description' => 'Lihat proposal tempat Anda menjadi anggota dan pahami fase kerjanya.'],
                        ['title' => 'Bantu Administrasi', 'description' => 'Pastikan dokumen dan data dukung selalu tersedia saat dibutuhkan tim.'],
                        ['title' => 'Ikuti Progres', 'description' => 'Pantau laporan dan notifikasi agar kontribusi Anda tetap sinkron dengan kegiatan utama.'],
                    ],
                    'proposal_heading' => 'Proposal tim yang Anda ikuti',
                    'review_heading' => 'Update review untuk proposal tim',
                ],
                'navigation_labels' => [
                    'dashboard' => 'Kegiatan Saya',
                    'proposals.index' => 'Proposal Tim',
                    'documents.index' => 'Dokumen Tim',
                    'reviews.index' => 'Update Review',
                    'reports.index' => 'Progress Tim',
                    'notifications.index' => 'Notifikasi Tim',
                ],
                'modules' => [
                    'proposals' => ['eyebrow' => 'Proposal Tim', 'title' => 'Proposal yang Anda bantu sebagai anggota', 'description' => 'Mahasiswa hanya melihat proposal yang benar-benar melibatkan dirinya agar fokus kontribusi tetap jelas.', 'empty' => 'Anda belum terdaftar sebagai anggota proposal mana pun.', 'focus' => ['Pahami status proposal tim.', 'Baca ringkasan dan skema kegiatan.', 'Tahu kapan tim perlu dukungan dokumen atau revisi.']],
                    'documents' => ['eyebrow' => 'Dokumen Tim', 'title' => 'Lampiran yang mendukung kerja tim', 'description' => 'Halaman ini memudahkan mahasiswa membantu unggahan dan pengecekan dokumen proposal tim.', 'empty' => 'Belum ada dokumen tim yang bisa Anda lihat.', 'focus' => ['Bantu menyiapkan berkas.', 'Cek file yang sudah terunggah.', 'Pastikan dokumen terbaru mudah ditemukan.']],
                    'reviews' => ['eyebrow' => 'Perkembangan Review', 'title' => 'Catatan evaluasi yang perlu diketahui tim', 'description' => 'Mahasiswa dapat memantau catatan review untuk membantu tindak lanjut proposal tim.', 'empty' => 'Belum ada update review untuk proposal tim Anda.', 'focus' => ['Lihat catatan revisi.', 'Pahami area proposal yang perlu dibantu.', 'Ikuti perubahan status dari review ke approved.']],
                    'reports' => ['eyebrow' => 'Progres Tim', 'title' => 'Laporan kegiatan yang sedang Anda bantu', 'description' => 'Laporan membantu mahasiswa memahami kemajuan kegiatan dan kontribusi yang masih dibutuhkan.', 'empty' => 'Belum ada laporan progres tim.', 'focus' => ['Lihat progress kegiatan.', 'Bantu pengumpulan data lapangan.', 'Jaga arsip kegiatan tetap tertata.']],
                    'notifications' => ['eyebrow' => 'Notifikasi Tim', 'title' => 'Pengingat aktivitas proposal yang melibatkan Anda', 'description' => 'Notifikasi menjadi alarm ringan agar Anda tidak tertinggal dalam dukungan administrasi maupun pelaksanaan.', 'empty' => 'Belum ada notifikasi tim untuk Anda.', 'focus' => ['Pantau kebutuhan unggahan dokumen.', 'Lihat update status proposal.', 'Jaga koordinasi dengan dosen pengusul.']],
                ],
            ],
            'mitra' => [
                'role_label' => 'Mitra',
                'dashboard' => [
                    'title' => 'Panel Kolaborasi Mitra',
                    'description' => 'Mitra memantau proposal yang sudah matang, disetujui, atau selesai agar kolaborasi berjalan terang, tidak samar seperti janji rapat lanjutan.',
                    'focus' => [
                        ['title' => 'Pantau Proposal Relevan', 'description' => 'Lihat proposal approved atau completed yang berkaitan dengan kerja sama.'],
                        ['title' => 'Ikuti Dokumen dan Laporan', 'description' => 'Periksa jejak dokumen serta progres kegiatan sebagai dasar koordinasi lapangan.'],
                        ['title' => 'Jaga Keterhubungan', 'description' => 'Gunakan notifikasi untuk memastikan komunikasi antar pihak tidak terputus.'],
                    ],
                    'proposal_heading' => 'Proposal kolaborasi yang sudah matang',
                    'review_heading' => 'Catatan evaluasi yang dapat dijadikan konteks',
                ],
                'navigation_labels' => [
                    'dashboard' => 'Kolaborasi',
                    'proposals.index' => 'Proposal Kolaborasi',
                    'documents.index' => 'Dokumen Kolaborasi',
                    'reviews.index' => 'Riwayat Evaluasi',
                    'reports.index' => 'Hasil & Progres',
                    'notifications.index' => 'Notifikasi Kolaborasi',
                ],
                'modules' => [
                    'proposals' => ['eyebrow' => 'Proposal Kolaborasi', 'title' => 'Proposal approved dan completed yang relevan bagi mitra', 'description' => 'Mitra tidak perlu melihat draft atau fase internal kampus yang belum siap untuk kolaborasi.', 'empty' => 'Belum ada proposal kolaborasi yang terbuka untuk mitra.', 'focus' => ['Lihat proposal yang sudah siap dijalankan.', 'Pahami skema dan tujuan kegiatan.', 'Gunakan status sebagai konteks koordinasi lapangan.']],
                    'documents' => ['eyebrow' => 'Dokumen Kolaborasi', 'title' => 'Berkas kegiatan yang siap dipantau mitra', 'description' => 'Hanya dokumen dari proposal yang sudah approved atau completed yang ditampilkan untuk mitra.', 'empty' => 'Belum ada dokumen kolaborasi yang tersedia.', 'focus' => ['Lihat lampiran yang sudah final.', 'Gunakan dokumen sebagai bahan koordinasi.', 'Pastikan versi file yang dilihat sudah relevan.']],
                    'reviews' => ['eyebrow' => 'Riwayat Evaluasi', 'title' => 'Konteks penilaian proposal yang telah berjalan', 'description' => 'Mitra dapat melihat hasil evaluasi sebagai konteks kualitas proposal yang akhirnya dikerjakan bersama.', 'empty' => 'Belum ada riwayat evaluasi yang dapat ditampilkan.', 'focus' => ['Pahami alasan proposal disetujui.', 'Baca catatan evaluasi utama.', 'Gunakan sebagai konteks pelaksanaan.']],
                    'reports' => ['eyebrow' => 'Hasil & Progres', 'title' => 'Laporan kegiatan kolaborasi', 'description' => 'Mitra memantau progres dan hasil kegiatan tanpa harus masuk ke wilayah administratif internal kampus.', 'empty' => 'Belum ada laporan kegiatan kolaborasi.', 'focus' => ['Lihat capaian kegiatan.', 'Pantau progress pelaksanaan.', 'Gunakan laporan sebagai dasar evaluasi kerja sama.']],
                    'notifications' => ['eyebrow' => 'Notifikasi Kolaborasi', 'title' => 'Pembaruan yang relevan untuk mitra', 'description' => 'Notifikasi mitra berisi sinyal yang membantu menjaga koordinasi dengan pihak kampus.', 'empty' => 'Belum ada notifikasi kolaborasi.', 'focus' => ['Lihat update progress.', 'Pantau dokumen baru yang relevan.', 'Jaga sinkronisasi antar lembaga.']],
                ],
            ],
            'staff' => [
                'role_label' => 'Staff',
                'dashboard' => [
                    'title' => 'Panel Dukungan Operasional',
                    'description' => 'Staff mendukung ketertiban dokumen, notifikasi, dan alur administrasi proposal.',
                    'focus' => [
                        ['title' => 'Cek Dokumen', 'description' => 'Pastikan lampiran dan status proposal mudah dipantau.'],
                        ['title' => 'Bantu Administrasi', 'description' => 'Jaga notifikasi dan data tetap sinkron untuk pihak yang membutuhkan.'],
                    ],
                    'proposal_heading' => 'Proposal yang perlu dukungan administrasi',
                    'review_heading' => 'Perkembangan evaluasi untuk dipantau',
                ],
                'navigation_labels' => [
                    'dashboard' => 'Dashboard Operasional',
                    'proposals.index' => 'Proposal Operasional',
                    'documents.index' => 'Dokumen Operasional',
                    'notifications.index' => 'Notifikasi Operasional',
                    'reports.index' => 'Monitoring Laporan',
                ],
                'modules' => [
                    'proposals' => ['eyebrow' => 'Proposal Operasional', 'title' => 'Proposal yang dapat dipantau dari sisi administrasi', 'description' => 'Staff membantu memastikan proposal dan dokumen mudah diproses secara operasional.', 'empty' => 'Belum ada proposal yang membutuhkan dukungan operasional.', 'focus' => ['Pantau status proposal.', 'Cek kebutuhan berkas.', 'Dukung koordinasi antar peran.']],
                    'documents' => ['eyebrow' => 'Dokumen Operasional', 'title' => 'Berkas yang perlu dijaga kerapihannya', 'description' => 'Halaman ini membantu staff melihat dokumen yang terkait dengan proses administrasi proposal.', 'empty' => 'Belum ada dokumen operasional yang tersedia.', 'focus' => ['Lihat unggahan terbaru.', 'Cek siapa pengunggahnya.', 'Jaga jejak dokumen tetap utuh.']],
                    'notifications' => ['eyebrow' => 'Notifikasi Operasional', 'title' => 'Sinyal koordinasi dan administrasi', 'description' => 'Notifikasi staff membantu mempercepat respons administratif tanpa perlu menebak-nebak prioritas.', 'empty' => 'Belum ada notifikasi operasional.', 'focus' => ['Lihat pembaruan proposal.', 'Pantau kebutuhan dokumen.', 'Jaga ritme koordinasi.']],
                    'reports' => ['eyebrow' => 'Monitoring Laporan', 'title' => 'Laporan yang perlu dipantau dari sisi ketertiban', 'description' => 'Staff dapat membantu memonitor laporan yang sudah masuk agar alur dokumentasi tetap rapi.', 'empty' => 'Belum ada laporan yang perlu dipantau.', 'focus' => ['Cek laporan yang sudah dikirim.', 'Pastikan data tidak tercecer.', 'Dukung admin LPPM dalam monitoring.']],
                ],
            ],
            'yayasan' => [
                'role_label' => 'Yayasan',
                'dashboard' => [
                    'title' => 'Panel Monitoring Yayasan',
                    'description' => 'Yayasan melihat kegiatan yang sudah matang untuk kebutuhan monitoring dan evaluasi strategis.',
                    'focus' => [
                        ['title' => 'Pantau Kegiatan', 'description' => 'Lihat proposal yang telah disetujui dan progres pelaksanaannya.'],
                        ['title' => 'Baca Laporan', 'description' => 'Gunakan laporan dan notifikasi sebagai bahan evaluasi kebijakan.'],
                    ],
                    'proposal_heading' => 'Proposal yang siap dipantau',
                    'review_heading' => 'Konteks evaluasi proposal',
                ],
                'navigation_labels' => [
                    'dashboard' => 'Monitoring',
                    'proposals.index' => 'Proposal Monitoring',
                    'reports.index' => 'Laporan Kegiatan',
                    'notifications.index' => 'Notifikasi Monitoring',
                ],
                'modules' => [
                    'proposals' => ['eyebrow' => 'Proposal Monitoring', 'title' => 'Proposal yang terbuka untuk pengawasan strategis', 'description' => 'Yayasan melihat proposal dari sudut monitoring tingkat atas, bukan detail operasional harian.', 'empty' => 'Belum ada proposal untuk monitoring yayasan.', 'focus' => ['Lihat proposal yang sudah matang.', 'Pantau arah kegiatan.', 'Gunakan status untuk evaluasi kelembagaan.']],
                    'reports' => ['eyebrow' => 'Laporan Kegiatan', 'title' => 'Progres kegiatan yang relevan untuk evaluasi yayasan', 'description' => 'Laporan progres memberi gambaran apakah kegiatan bergerak sesuai tujuan yang telah disetujui.', 'empty' => 'Belum ada laporan kegiatan untuk monitoring yayasan.', 'focus' => ['Baca capaian kegiatan.', 'Pantau progres terhadap target.', 'Gunakan data sebagai bahan evaluasi.']],
                    'notifications' => ['eyebrow' => 'Notifikasi Monitoring', 'title' => 'Pembaruan yang membantu pengawasan strategis', 'description' => 'Notifikasi yayasan berfungsi sebagai penanda perubahan penting pada kegiatan yang relevan.', 'empty' => 'Belum ada notifikasi monitoring.', 'focus' => ['Lihat update proposal dan laporan.', 'Jaga pengawasan tetap berbasis data.', 'Fokus pada sinyal yang penting.']],
                ],
            ],
        ];
    }
}
