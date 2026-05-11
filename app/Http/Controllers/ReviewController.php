<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\LppmWorkspace;
use Illuminate\Database\Eloquent\Builder;

/**
 * Mengelola daftar hasil review proposal sesuai role dan penugasan reviewer.
 */
class ReviewController extends Controller
{
    /**
     * Menampilkan review yang relevan bagi user berikut ringkasan status review.
     */
    public function index()
    {
        $user = auth()->user();
        $module = LppmWorkspace::module($user, 'reviews');

        $reviewQuery = Review::with(['proposal', 'reviewer'])
            ->when(
                ! $user->hasLppmPermission('lppm.review.assign') && ! $user->hasLppmPermission('lppm.proposal.view_all'),
                function (Builder $query) use ($user) {
                    if ($user->hasAnyLppmRole(['reviewer'])) {
                        $query->where('reviewer_id', $user->user_id);

                        return;
                    }

                    $query->whereHas('proposal', function (Builder $proposalQuery) use ($user) {
                        if ($user->hasAnyLppmRole(['kaprodi'])) {
                            $studyProgram = trim((string) $user->study_program);
                            $proposalQuery->where(function (Builder $nestedQuery) use ($user, $studyProgram) {
                                $nestedQuery->where(function (Builder $approvalQuery) use ($studyProgram) {
                                    $approvalQuery->where('status', \App\Models\Proposal::STATUS_PENDING_KAPRODI)
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
                                    $approvalQuery->where('status', \App\Models\Proposal::STATUS_PENDING_DEKAN)
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

                        $proposalQuery->where('user_id', $user->user_id)
                            ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                                $memberQuery->where('user_id', $user->user_id);
                            });
                    });
                }
            );

        $statusSummary = (clone $reviewQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $reviews = $reviewQuery->latest()->paginate(10);

        return view('reviews.index', compact('reviews', 'module', 'statusSummary'));
    }
}
