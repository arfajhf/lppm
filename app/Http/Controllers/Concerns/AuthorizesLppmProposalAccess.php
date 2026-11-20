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
        abort_unless($user && $user->hasLppmPermission('lppm.proposal.view'), 403);

        if ($user->hasLppmPermission('lppm.proposal.view_all') || $user->hasLppmPermission('lppm.review.assign')) {
            return $query;
        }

        return $query->where(function (Builder $visible) use ($user) {
            $visible->where('user_id', $user->user_id)
                ->orWhereHas('members', fn (Builder $q) => $q->where('user_id', $user->user_id));
            if ($user->hasAnyLppmRole(['reviewer'])) {
                $visible->orWhereHas('reviews', fn (Builder $q) => $q->where('reviewer_id', $user->user_id))
                    ->orWhereHas('grantRecords', fn (Builder $q) => $q->where('kind', 'assignment')->where('data->reviewer_id', $user->user_id));
            }
            $faculty = trim((string) $user->faculty);
            $program = trim((string) $user->study_program);
            if ($faculty !== '' && $user->hasAnyLppmRole(['dekan', 'kaprodi'])) {
                $visible->orWhere(function (Builder $scope) use ($user, $faculty, $program) {
                    $scope->where(function (Builder $stage) use ($user) {
                        $stage->whereNotNull('grant_stage');
                        if ($user->hasAnyLppmRole(['dekan'])) {
                            $stage->orWhere('status', Proposal::STATUS_PENDING_DEKAN);
                        }
                        if ($user->hasAnyLppmRole(['kaprodi'])) {
                            $stage->orWhere('status', Proposal::STATUS_PENDING_KAPRODI);
                        }
                    })->whereHas('user', function (Builder $owner) use ($user, $faculty, $program) {
                        $owner->where('faculty', $faculty);
                        if (! $user->hasAnyLppmRole(['dekan'])) {
                            $owner->where('study_program', $program)->whereRaw($program !== '' ? '1=1' : '1=0');
                        }
                    });
                });
            }
            if ($user->hasAnyLppmRole(['rektorat', 'yayasan'])) {
                $visible->orWhereIn('status', ['approved', 'completed']);
            }
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
