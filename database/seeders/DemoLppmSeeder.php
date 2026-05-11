<?php

namespace Database\Seeders;

use App\Models\ActivityNotification;
use App\Models\Partner;
use App\Models\Proposal;
use App\Models\ProposalMember;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoLppmSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect([
            [
                'name' => 'Admin LPPM',
                'email' => 'admin.lppm@umb.ac.id',
                'role' => 'admin_lppm',
                'identity_number' => 'ADM001',
                'faculty' => 'LPPM',
                'study_program' => 'Administrasi Penelitian',
            ],
            [
                'name' => 'Reviewer Internal',
                'email' => 'reviewer@umb.ac.id',
                'role' => 'reviewer',
                'identity_number' => 'REV001',
                'faculty' => 'Fakultas Teknik',
                'study_program' => 'Teknik Informatika',
            ],
            [
                'name' => 'Dosen Pengusul',
                'email' => 'dosen@umb.ac.id',
                'role' => 'dosen',
                'identity_number' => 'DSN001',
                'faculty' => 'Fakultas Ekonomi',
                'study_program' => 'Manajemen',
            ],
            [
                'name' => 'Mahasiswa Kolaborator',
                'email' => 'mahasiswa@umb.ac.id',
                'role' => 'mahasiswa',
                'identity_number' => 'MHS001',
                'faculty' => 'Fakultas Keguruan',
                'study_program' => 'Pendidikan Bahasa',
            ],
        ])->mapWithKeys(function (array $attributes) {
            $user = User::updateOrCreate(
                ['email' => $attributes['email']],
                $attributes + ['password' => Hash::make('password')]
            );

            return [$attributes['email'] => $user];
        });

        $proposal = Proposal::updateOrCreate(
            ['title' => 'Model Pemberdayaan UMKM Berbasis Data Kampus'],
            [
                'type' => 'Pengabdian Hibah',
                'scheme' => 'Kolaborasi Regional',
                'user_id' => $users['dosen@umb.ac.id']->user_id,
                'status' => 'review',
                'year' => now()->year,
                'summary' => 'Program pengabdian terstruktur untuk membantu UMKM sekitar kampus melalui pelatihan, digitalisasi, dan pendampingan pelaporan.',
            ]
        );

        ProposalMember::updateOrCreate(
            [
                'proposal_id' => $proposal->proposal_id,
                'user_id' => $users['mahasiswa@umb.ac.id']->user_id,
            ],
            ['role' => 'anggota']
        );

        $partner = Partner::updateOrCreate(
            ['name' => 'Forum UMKM Kota Tasikmalaya'],
            [
                'institution_type' => 'Komunitas',
                'email' => 'umkm@example.org',
                'phone' => '081234567890',
            ]
        );

        $proposal->partners()->syncWithoutDetaching([$partner->partner_id]);

        Review::updateOrCreate(
            [
                'proposal_id' => $proposal->proposal_id,
                'reviewer_id' => $users['reviewer@umb.ac.id']->user_id,
            ],
            [
                'score' => 87.50,
                'comment' => 'Rancangan kegiatan sudah kuat, tinggal perjelas indikator keberlanjutan mitra.',
                'status' => 'revision',
            ]
        );

        Report::updateOrCreate(
            ['proposal_id' => $proposal->proposal_id],
            [
                'progress' => 60,
                'description' => 'Pendampingan batch pertama berjalan dan mitra mulai mengadopsi dashboard penjualan sederhana.',
                'submitted_at' => now(),
            ]
        );

        ActivityNotification::updateOrCreate(
            [
                'user_id' => $users['dosen@umb.ac.id']->user_id,
                'message' => 'Proposal Anda telah masuk tahap review dan menunggu revisi.',
            ],
            [
                'channel' => 'in_app',
                'is_read' => false,
            ]
        );
    }
}
