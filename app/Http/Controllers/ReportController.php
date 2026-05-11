<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Proposal;
use App\Support\LppmWorkspace;
use Illuminate\Database\Eloquent\Builder;

/**
 * Menampilkan laporan kemajuan proposal sesuai hak akses pengguna LPPM.
 */
class ReportController extends Controller
{
    /**
     * Menyajikan daftar laporan dan ringkasan progres pelaksanaan kegiatan.
     */
    public function index()
    {
        $user = auth()->user();
        $module = LppmWorkspace::module($user, 'reports');

        $reportQuery = Report::with('proposal')
            ->whereHas('proposal', function (Builder $proposalQuery) use ($user) {
                if ($user->hasLppmPermission('lppm.proposal.view_all') || $user->hasLppmPermission('lppm.review.assign')) {
                    return;
                }

                if ($user->hasAnyLppmRole(['reviewer'])) {
                    $proposalQuery->whereHas('reviews', function (Builder $reviewQuery) use ($user) {
                        $reviewQuery->where('reviewer_id', $user->user_id);
                    });

                    return;
                }

                if ($user->hasAnyLppmRole(['kaprodi'])) {
                    $studyProgram = trim((string) $user->study_program);

                    $proposalQuery->where(function (Builder $nestedQuery) use ($user, $studyProgram) {
                        $nestedQuery->where(function (Builder $approvalQuery) use ($studyProgram) {
                            $approvalQuery->where('status', Proposal::STATUS_PENDING_KAPRODI)
                                ->whereHas('user', function (Builder $ownerQuery) use ($studyProgram) {
                                    $ownerQuery->where('study_program', $studyProgram);
                                });
                        })
                            ->orWhere('user_id', $user->user_id)
                            ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                                $memberQuery->where('user_id', $user->user_id);
                            });
                    });

                    return;
                }

                if ($user->hasAnyLppmRole(['dekan'])) {
                    $faculty = trim((string) $user->faculty);

                    $proposalQuery->where(function (Builder $nestedQuery) use ($user, $faculty) {
                        $nestedQuery->where(function (Builder $approvalQuery) use ($faculty) {
                            $approvalQuery->where('status', Proposal::STATUS_PENDING_DEKAN)
                                ->whereHas('user', function (Builder $ownerQuery) use ($faculty) {
                                    $ownerQuery->where('faculty', $faculty);
                                });
                        })
                            ->orWhere('user_id', $user->user_id)
                            ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                                $memberQuery->where('user_id', $user->user_id);
                            });
                    });

                    return;
                }

                if ($user->hasAnyLppmRole(['mitra', 'rektorat', 'yayasan'])) {
                    $proposalQuery->whereIn('status', ['approved', 'completed']);

                    return;
                }

                $proposalQuery->where('user_id', $user->user_id)
                    ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                        $memberQuery->where('user_id', $user->user_id);
                    });
            });

        $progressBuckets = [
            '0-25' => (clone $reportQuery)->whereBetween('progress', [0, 25])->count(),
            '26-75' => (clone $reportQuery)->whereBetween('progress', [26, 75])->count(),
            '76-100' => (clone $reportQuery)->whereBetween('progress', [76, 100])->count(),
        ];

        $reports = $reportQuery->latest()->paginate(10);

        return view('reports.index', compact('reports', 'module', 'progressBuckets'));
    }
}
