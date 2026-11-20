<?php

$config = [
    'required_roles' => [
        'super_admin' => 'Pengelola platform, pengaturan sistem, dan orkestrasi akses lintas peran.',
        'lppm_admin' => 'Eksekutor dan pengelola administratif LPPM, termasuk review dan tindak lanjut operasional.',
        'rektorat' => 'Pemantau strategis proposal dan pelaksanaan kegiatan pada level institusi.',
        'dekan' => 'Kepala fakultas yang mengeksekusi dan mengawal pengajuan pada lingkup fakultas.',
        'kaprodi' => 'Kepala program studi yang mengeksekusi dan mengawal pengajuan pada lingkup prodi.',
        'dosen' => 'Pengusul utama proposal penelitian atau pengabdian.',
        'mahasiswa' => 'Anggota kegiatan yang membantu dokumen, progres, dan kolaborasi proposal.',
        'mitra' => 'Pihak eksternal yang memantau kolaborasi dan hasil kegiatan yang relevan.',
    ],

    'required_permissions' => [
        'lppm.access' => 'Masuk ke aplikasi LPPM.',
        'lppm.dashboard.view' => 'Melihat dashboard LPPM.',
        'lppm.proposal.view' => 'Melihat daftar proposal yang relevan.',
        'lppm.proposal.view_all' => 'Melihat seluruh proposal lintas pengusul.',
        'lppm.proposal.create' => 'Membuat proposal baru.',
        'lppm.proposal.update' => 'Memperbarui proposal yang menjadi tanggung jawab pengguna.',
        'lppm.proposal.submit' => 'Mengirim proposal dari draft ke submitted.',
        'lppm.document.view' => 'Melihat dokumen proposal.',
        'lppm.document.upload' => 'Mengunggah dokumen pendukung.',
        'lppm.review.view' => 'Melihat daftar review yang relevan.',
        'lppm.review.assign' => 'Menetapkan reviewer atau distribusi review.',
        'lppm.review.score' => 'Memberi skor dan komentar review.',
        'lppm.review.approve' => 'Menyetujui hasil review atau proposal.',
        'lppm.report.view' => 'Melihat laporan progres.',
        'lppm.report.create' => 'Mengirim atau memperbarui laporan progres.',
        'lppm.notification.view' => 'Melihat notifikasi terkait LPPM.',
        'lppm.access.manage' => 'Melihat matriks akses dan evaluasi role/permission LPPM.',
    ],

    'role_permission_fallbacks' => [
        'super_admin' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.proposal.view_all',
            'lppm.proposal.create',
            'lppm.proposal.update',
            'lppm.proposal.submit',
            'lppm.document.view',
            'lppm.document.upload',
            'lppm.review.view',
            'lppm.review.assign',
            'lppm.review.score',
            'lppm.review.approve',
            'lppm.report.view',
            'lppm.report.create',
            'lppm.notification.view',
            'lppm.access.manage',
        ],
        'lppm_admin' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.proposal.view_all',
            'lppm.proposal.create',
            'lppm.proposal.update',
            'lppm.proposal.submit',
            'lppm.document.view',
            'lppm.document.upload',
            'lppm.review.view',
            'lppm.review.assign',
            'lppm.review.score',
            'lppm.review.approve',
            'lppm.report.view',
            'lppm.report.create',
            'lppm.notification.view',
            'lppm.access.manage',
        ],
        'rektorat' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.report.view',
            'lppm.notification.view',
        ],
        'dekan' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.proposal.create',
            'lppm.proposal.update',
            'lppm.proposal.submit',
            'lppm.document.view',
            'lppm.document.upload',
            'lppm.review.view',
            'lppm.report.view',
            'lppm.report.create',
            'lppm.notification.view',
        ],
        'kaprodi' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.proposal.create',
            'lppm.proposal.update',
            'lppm.proposal.submit',
            'lppm.document.view',
            'lppm.document.upload',
            'lppm.review.view',
            'lppm.report.view',
            'lppm.report.create',
            'lppm.notification.view',
        ],
        'admin' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.proposal.view_all',
            'lppm.proposal.create',
            'lppm.proposal.update',
            'lppm.proposal.submit',
            'lppm.document.view',
            'lppm.document.upload',
            'lppm.review.view',
            'lppm.review.assign',
            'lppm.review.score',
            'lppm.review.approve',
            'lppm.report.view',
            'lppm.report.create',
            'lppm.notification.view',
            'lppm.access.manage',
        ],
        'admin_lppm' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.proposal.view_all',
            'lppm.proposal.create',
            'lppm.proposal.update',
            'lppm.proposal.submit',
            'lppm.document.view',
            'lppm.document.upload',
            'lppm.review.view',
            'lppm.review.assign',
            'lppm.review.score',
            'lppm.review.approve',
            'lppm.report.view',
            'lppm.report.create',
            'lppm.notification.view',
            'lppm.access.manage',
        ],
        'dosen' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.proposal.create',
            'lppm.proposal.update',
            'lppm.proposal.submit',
            'lppm.document.view',
            'lppm.document.upload',
            'lppm.review.view',
            'lppm.report.view',
            'lppm.report.create',
            'lppm.notification.view',
        ],
        'mahasiswa' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.document.view',
            'lppm.document.upload',
            'lppm.report.view',
            'lppm.notification.view',
        ],
        'mitra' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.document.view',
            'lppm.report.view',
            'lppm.notification.view',
        ],
        'reviewer' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.document.view',
            'lppm.review.view',
            'lppm.review.score',
            'lppm.report.view',
            'lppm.notification.view',
        ],
        'staff' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.document.view',
            'lppm.notification.view',
        ],
        'yayasan' => [
            'lppm.access',
            'lppm.dashboard.view',
            'lppm.proposal.view',
            'lppm.report.view',
            'lppm.notification.view',
        ],
    ],

    'navigation' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'grid',
            'permission' => 'lppm.dashboard.view',
        ],
        [
            'label' => 'Proposal',
            'route' => 'proposals.index',
            'icon' => 'folder',
            'permission' => 'lppm.proposal.view',
        ],
        [
            'label' => 'Dokumen',
            'route' => 'documents.index',
            'icon' => 'document',
            'permission' => 'lppm.document.view',
        ],
        [
            'label' => 'Review',
            'route' => 'reviews.index',
            'icon' => 'search',
            'permission' => 'lppm.review.view',
        ],
        [
            'label' => 'Laporan',
            'route' => 'reports.index',
            'icon' => 'chart',
            'permission' => 'lppm.report.view',
        ],
        [
            'label' => 'Notifikasi',
            'route' => 'notifications.index',
            'icon' => 'bell',
            'permission' => 'lppm.notification.view',
        ],
        [
            'label' => 'Matriks Akses',
            'route' => 'access.index',
            'icon' => 'shield',
            'permission' => 'lppm.access.manage',
        ],
    ],
];

$config['required_roles'] += ['reviewer' => 'Penilai sesuai penugasan dan SK.', 'kepala_lppm' => 'Pengesahan arsip LPPM.', 'keuangan' => 'Pencatatan pencairan hibah.', 'rektor' => 'Pengesahan SK hibah.', 'wakil_rektor' => 'Rekomendasi reviewer.'];
$config['required_permissions'] += ['lppm.grant.manage' => 'Kelola administrasi hibah.', 'lppm.grant.award' => 'Catat pengesahan SK Rektor.', 'lppm.grant.disburse' => 'Catat pencairan hibah.', 'lppm.grant.archive' => 'Sahkan arsip akhir hibah.'];
foreach (['super_admin', 'lppm_admin', 'admin', 'admin_lppm'] as $role) {
    $config['role_permission_fallbacks'][$role][] = 'lppm.grant.manage';
}
foreach (['rektor', 'kepala_lppm', 'keuangan', 'wakil_rektor'] as $role) {
    $config['role_permission_fallbacks'][$role] = ['lppm.access', 'lppm.dashboard.view', 'lppm.proposal.view', 'lppm.proposal.view_all', 'lppm.document.view', 'lppm.report.view', 'lppm.notification.view'];
}
$config['role_permission_fallbacks']['rektor'][] = 'lppm.grant.award';
$config['role_permission_fallbacks']['kepala_lppm'][] = 'lppm.grant.archive';
$config['role_permission_fallbacks']['kepala_lppm'][] = 'lppm.grant.manage';
$config['role_permission_fallbacks']['keuangan'][] = 'lppm.grant.disburse';
$config['navigation'][] = ['label' => 'Hibah Internal', 'route' => 'grants.index', 'icon' => 'folder', 'permission' => 'lppm.proposal.view'];

$config['required_roles']['sekretaris_lppm'] = 'Sekretaris/staf pelaksana sosialisasi jadwal LPPM.';
$config['required_permissions']['lppm.grant.socialise'] = 'Mencatat sosialisasi jadwal dan panduan oleh sekretariat LPPM.';
foreach (['staff', 'lppm_admin', 'kepala_lppm', 'super_admin', 'admin', 'admin_lppm'] as $role) {
    $config['role_permission_fallbacks'][$role][] = 'lppm.grant.socialise';
}
$config['role_permission_fallbacks']['sekretaris_lppm'] = $config['role_permission_fallbacks']['staff'];

return $config;
