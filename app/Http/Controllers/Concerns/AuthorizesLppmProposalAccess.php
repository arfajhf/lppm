<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Proposal;
use Illuminate\Database\Eloquent\Builder;

/**
 * Menyatukan aturan visibilitas proposal agar konsisten dipakai lintas controller.
 */
trait AuthorizesLppmProposalAccess
{
    /**
     * Membatasi query proposal berdasarkan permission, role reviewer, mitra,
     * atau keanggotaan proposal dari user yang sedang login.
     */
    protected function scopeProposalVisibility(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user->hasLppmPermission('lppm.proposal.view_all') || $user->hasLppmPermission('lppm.review.assign')) {
            return $query;
        }

        if ($user->hasAnyLppmRole(['reviewer'])) {
            return $query->whereHas('reviews', function (Builder $reviewQuery) use ($user) {
                $reviewQuery->where('reviewer_id', $user->user_id);
            });
        }

        if ($user->hasAnyLppmRole(['kaprodi'])) {
            $studyProgram = trim((string) $user->study_program);

            return $query->where(function (Builder $proposalQuery) use ($user, $studyProgram) {
                $proposalQuery->where(function (Builder $approvalQuery) use ($studyProgram) {
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
        }

        if ($user->hasAnyLppmRole(['dekan'])) {
            $faculty = trim((string) $user->faculty);

            return $query->where(function (Builder $proposalQuery) use ($user, $faculty) {
                $proposalQuery->where(function (Builder $approvalQuery) use ($faculty) {
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
        }

        if ($user->hasAnyLppmRole(['mitra', 'rektorat', 'yayasan'])) {
            return $query->whereIn('status', ['approved', 'completed']);
        }

        return $query->where(function (Builder $proposalQuery) use ($user) {
            $proposalQuery->where('user_id', $user->user_id)
                ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                    $memberQuery->where('user_id', $user->user_id);
                });
        });
    }

    /**
     * Menghentikan akses saat proposal berada di luar cakupan visibilitas pengguna.
     */
    protected function ensureUserCanAccessProposal(Proposal $proposal): void
    {
        if ($this->scopeProposalVisibility(Proposal::query()->whereKey($proposal->proposal_id))->exists()) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke proposal ini.');
    }
}
