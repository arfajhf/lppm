<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Proposal;
use App\Support\LppmWorkspace;
use Illuminate\Database\Eloquent\Builder;

/**
 * Menampilkan dokumen proposal sesuai jangkauan akses masing-masing pengguna.
 */
class DocumentController extends Controller
{
    /**
     * Menyajikan daftar dokumen dan ringkasan jenis dokumen yang terlihat oleh user.
     */
    public function index()
    {
        $user = auth()->user();
        $module = LppmWorkspace::module($user, 'documents');
        $documents = Document::with(['proposal.user', 'uploader'])
            ->whereHas('proposal', fn (Builder $query) => $this->applyProposalVisibility($query))
            ->latest()
            ->paginate(10);

        $documentTypeSummary = Document::query()
            ->whereHas('proposal', fn (Builder $query) => $this->applyProposalVisibility($query))
            ->selectRaw('document_type, count(*) as total')
            ->groupBy('document_type')
            ->pluck('total', 'document_type');

        return view('documents.index', compact('documents', 'module', 'documentTypeSummary'));
    }

    /**
     * Membatasi proposal yang dapat terlihat berdasarkan role, keanggotaan, dan
     * permission LPPM yang aktif.
     */
    private function applyProposalVisibility(Builder $query): void
    {
        $user = auth()->user();

        if ($user->hasLppmPermission('lppm.proposal.view_all') || $user->hasLppmPermission('lppm.review.assign')) {
            return;
        }

        if ($user->hasAnyLppmRole(['reviewer'])) {
            $query->whereHas('reviews', function (Builder $reviewQuery) use ($user) {
                $reviewQuery->where('reviewer_id', $user->user_id);
            });

            return;
        }

        if ($user->hasAnyLppmRole(['kaprodi'])) {
            $studyProgram = trim((string) $user->study_program);

            $query->where(function (Builder $proposalQuery) use ($user, $studyProgram) {
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

            return;
        }

        if ($user->hasAnyLppmRole(['dekan'])) {
            $faculty = trim((string) $user->faculty);

            $query->where(function (Builder $proposalQuery) use ($user, $faculty) {
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

            return;
        }

        if ($user->hasAnyLppmRole(['mitra', 'rektorat', 'yayasan'])) {
            $query->whereIn('status', ['approved', 'completed']);

            return;
        }

        $query->where(function (Builder $proposalQuery) use ($user) {
            $proposalQuery->where('user_id', $user->user_id)
                ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                    $memberQuery->where('user_id', $user->user_id);
                });
        });
    }
}
