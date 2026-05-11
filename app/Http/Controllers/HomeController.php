<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesLppmProposalAccess;
use App\Models\Proposal;
use App\Models\Review;
use App\Support\LppmWorkspace;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Mengelola halaman muka dan dashboard utama workspace LPPM.
 */
class HomeController extends Controller
{
    use AuthorizesLppmProposalAccess;

    /**
     * Menampilkan halaman welcome dengan gambaran alur kerja inti aplikasi.
     */
    public function welcome()
    {
        $workflow = collect([
            Proposal::STATUS_DRAFT,
            Proposal::STATUS_PENDING_KAPRODI,
            Proposal::STATUS_PENDING_DEKAN,
            Proposal::STATUS_SUBMITTED,
            Proposal::STATUS_REVIEW,
            Proposal::STATUS_REVISION,
            Proposal::STATUS_APPROVED,
            Proposal::STATUS_COMPLETED,
        ]);

        $featureGroups = [
            [
                'title' => 'Siklus Proposal Terpusat',
                'description' => 'Pengajuan penelitian dan pengabdian dikelola dalam satu alur yang rapi dari draf sampai laporan akhir.',
            ],
            [
                'title' => 'Review Berbasis Peran',
                'description' => 'Reviewer, admin, dan pengusul bekerja di panel yang sama dengan pembagian akses yang jelas.',
            ],
            [
                'title' => 'Siap Tumbuh ke API',
                'description' => 'Struktur Laravel dirapikan dengan pendekatan modular agar mudah diteruskan ke layanan dan integrasi berikutnya.',
            ],
        ];

        return view('pages.welcome', compact('workflow', 'featureGroups'));
    }

    /**
     * Menyusun statistik, aktivitas terbaru, dan fokus kerja sesuai peran pengguna.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $workspace = LppmWorkspace::forUser($user);
        $proposalQuery = $this->scopeProposalVisibility(Proposal::query());

        $stats = [
            'total' => (clone $proposalQuery)->count(),
            'submitted' => (clone $proposalQuery)->whereIn('status', [
                Proposal::STATUS_PENDING_KAPRODI,
                Proposal::STATUS_PENDING_DEKAN,
                Proposal::STATUS_SUBMITTED,
            ])->count(),
            'in_review' => (clone $proposalQuery)->where('status', Proposal::STATUS_REVIEW)->count(),
            'completed' => (clone $proposalQuery)->where('status', Proposal::STATUS_COMPLETED)->count(),
        ];

        $statusSummary = (clone $proposalQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $latestProposals = $this->scopeProposalVisibility(Proposal::with('user'))
            ->latest()
            ->take(5)
            ->get();

        $latestReviews = Review::with(['proposal', 'reviewer'])
            ->when(
                ! $user->hasLppmPermission('lppm.review.assign') && ! $user->hasLppmPermission('lppm.proposal.view_all'),
                function ($query) use ($user) {
                    if ($user->hasAnyLppmRole(['reviewer'])) {
                        $query->where('reviewer_id', $user->user_id);

                        return;
                    }

                    $query->whereHas('proposal', function ($proposalQuery) use ($user) {
                        if ($user->hasAnyLppmRole(['kaprodi'])) {
                            $studyProgram = trim((string) $user->study_program);
                            $proposalQuery->where(function ($nestedQuery) use ($user, $studyProgram) {
                                $nestedQuery->where(function ($approvalQuery) use ($studyProgram) {
                                    $approvalQuery->where('status', Proposal::STATUS_PENDING_KAPRODI)
                                        ->whereHas('user', function ($ownerQuery) use ($studyProgram) {
                                            $ownerQuery->where('study_program', $studyProgram);
                                        });
                                })
                                    ->orWhere('user_id', $user->user_id)
                                    ->orWhereHas('members', function ($memberQuery) use ($user) {
                                        $memberQuery->where('user_id', $user->user_id);
                                    });
                            });

                            return;
                        }

                        if ($user->hasAnyLppmRole(['dekan'])) {
                            $faculty = trim((string) $user->faculty);
                            $proposalQuery->where(function ($nestedQuery) use ($user, $faculty) {
                                $nestedQuery->where(function ($approvalQuery) use ($faculty) {
                                    $approvalQuery->where('status', Proposal::STATUS_PENDING_DEKAN)
                                        ->whereHas('user', function ($ownerQuery) use ($faculty) {
                                            $ownerQuery->where('faculty', $faculty);
                                        });
                                })
                                    ->orWhere('user_id', $user->user_id)
                                    ->orWhereHas('members', function ($memberQuery) use ($user) {
                                        $memberQuery->where('user_id', $user->user_id);
                                    });
                            });

                            return;
                        }

                        $proposalQuery->where('user_id', $user->user_id)
                            ->orWhereHas('members', function ($memberQuery) use ($user) {
                                $memberQuery->where('user_id', $user->user_id);
                            });
                    });
                }
            )
            ->latest()
            ->take(4)
            ->get();

        $roleActions = $this->roleActions($user->getSsoRoleNames(), (string) $user->role);
        $focusCards = collect($workspace['dashboard']['focus'] ?? []);
        $dashboardTitle = $workspace['dashboard']['title'] ?? 'Ringkasan Operasional LPPM';
        $dashboardDescription = $workspace['dashboard']['description'] ?? 'Panel ini menampilkan ringkasan kerja Anda di ekosistem LPPM.';
        $proposalHeading = $workspace['dashboard']['proposal_heading'] ?? 'Aktivitas pengusulan';
        $reviewHeading = $workspace['dashboard']['review_heading'] ?? 'Catatan evaluasi';
        $primaryRoleLabel = $workspace['role_label'] ?? ucfirst(LppmWorkspace::primaryRole($user));

        return view('dashboard.index', compact(
            'user',
            'stats',
            'statusSummary',
            'latestProposals',
            'latestReviews',
            'roleActions',
            'focusCards',
            'dashboardTitle',
            'dashboardDescription',
            'proposalHeading',
            'reviewHeading',
            'primaryRoleLabel'
        ));
    }

    /**
     * Menghasilkan daftar arahan tindakan yang relevan untuk role utama user.
     */
    protected function roleActions(array $roleNames, string $primaryRole): Collection
    {
        $roles = \App\Support\LppmAccess::expandRoleNames(array_unique(array_merge($roleNames, [strtolower($primaryRole)])));

        if (in_array('reviewer', $roles, true)) {
            return collect([
                'Tinjau proposal yang masuk sesuai penugasan review.',
                'Berikan skor, catatan, dan rekomendasi revisi secara terstruktur.',
                'Pastikan siklus review bergerak tepat waktu dan tidak jadi legenda kampus.',
            ]);
        }

        if (in_array('lppm_admin', $roles, true) || in_array('admin_lppm', $roles, true) || in_array('super_admin', $roles, true) || in_array('admin', $roles, true)) {
            return collect([
                'Verifikasi proposal baru dan kendalikan alur approval.',
                'Pantau progres penelitian dan pengabdian lintas skema.',
                'Evaluasi gap role/permission agar orkestrasi RBAC tidak cuma indah di diagram.',
            ]);
        }

        return collect(match (true) {
            in_array('mahasiswa', $roles, true) => [
                'Gabung sebagai anggota kegiatan dan lengkapi dokumen dukung.',
                'Ikuti progres review serta revisi proposal yang Anda bantu.',
                'Pantau notifikasi agar tidak ketinggalan tugas administrasi.',
            ],
            in_array('mitra', $roles, true), in_array('rektorat', $roles, true), in_array('yayasan', $roles, true) => [
                'Pantau kolaborasi dan hasil kegiatan yang sudah disetujui.',
                'Lihat laporan progres yang relevan dengan ruang kerja Anda.',
                'Gunakan notifikasi sebagai pengingat alur kerja, bukan sebagai dekorasi digital.',
            ],
            default => [
                'Buat proposal penelitian atau pengabdian baru.',
                'Unggah dokumen pendukung dan pantau status review.',
                'Siapkan laporan progres agar lifecycle kegiatan tetap utuh dari awal sampai akhir.',
            ],
        });
    }
}
